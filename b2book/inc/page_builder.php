<?php
// Render HTML page (according to URL parameters and user rights)
function renderPage()
{
    global $LINKSDB;
    $PAGE = new pageBuilder;

    // -------- User wants to logout.
    if (isset($_SERVER["QUERY_STRING"]) && startswith($_SERVER["QUERY_STRING"],'do=logout'))
    {
        invalidateCaches();
        logout();
        header('Location: ../');
        exit;
    }

    // -------- User wants to change the number of links per page (linksperpage=...)
    if (isset($_GET['linksperpage']))
    {
        if (is_numeric($_GET['linksperpage'])) { $_SESSION['LINKS_PER_PAGE']=abs(intval($_GET['linksperpage'])); }
        header('Location: '.(empty($_SERVER['HTTP_REFERER'])?'?':$_SERVER['HTTP_REFERER']));
        exit;
    }
    
    // -------- User wants to see only private links (toggle)
    if (isset($_GET['privateonly']))
    {
        if (empty($_SESSION['privateonly']))
        {
            $_SESSION['privateonly']=1; // See only private links
        }
        else
        {
            unset($_SESSION['privateonly']); // See all links
        }
        header('Location: '.(empty($_SERVER['HTTP_REFERER'])?'?':$_SERVER['HTTP_REFERER']));
        exit;
    }
    
    // --------- Daily (all links form a specific day) ----------------------
    if (isset($_SERVER["QUERY_STRING"]) && startswith($_SERVER["QUERY_STRING"],'do=daily'))
    { 
        $day=Date('Ymd'); // Today, in format YYYYMMDD.
        if (isset($_GET['day'])) $day=$_GET['day'];
        
        $days = $LINKSDB->days();
        $i = array_search($day,$days);
        if ($i==false) {    // no articles this day ? so we check the date of the last comment
            
            $lastCom = mysql_query("SELECT DATE_FORMAT((SELECT MAX(`date`) FROM `b2_comments`), '%Y%m%d') AS date");
            $lastCom = mysql_fetch_array($lastCom);
            $lastCom = $lastCom['date'];
            $i=count($days);
            
            if($lastCom != $day){   // not even a comment this day ? so we change the day ...
                $i=count($days)-1; $day=$days[$i];  // ... to the date of the last article
                // ... or to the date of the last comment if more recent
                if(mktime(0,0,0,substr($lastCom,4,2),substr($lastCom,6),substr($lastCom,0,4)) > mktime(0,0,0,substr($day,4,2),substr($day,6),substr($day,0,4))) $day = $lastCom;
            }   
        }
        $previousday=''; 
        $nextday=''; 
        if ($i!==false)
        {
            if ($i>1) $previousday=$days[$i-1];
            if ($i<count($days)-1) $nextday=$days[$i+1];
        }
        
        $colors = array('blue', 'orange', 'yellow', 'green', 'darkblue', 'red');
        $colorCounter = 0;
        $linksToDisplay=$LINKSDB->filterDay($day);
        // We pre-format some fields for proper output.
        foreach($linksToDisplay as $key=>$link)
        {
            $linksToDisplay[$key]['taglist']=explode(' ',$link['tags']);
            $linksToDisplay[$key]['author']=$link['author'];
            $linksToDisplay[$key]['formatedDescription']=i_nl2br($link['description']);
            $linksToDisplay[$key]['url'] = $link['url'];
            //$linksToDisplay[$key]['thumbnail'] = thumbnail($link['url']);
            $linksToDisplay[$key]['comment'] = false;            
            $linksToDisplay[$key]['header'] = 'Nouvel article posté par <strong>'.ucwords($link['author']).'</strong> :';            
            $linksToDisplay[$key]['color'] = $colors[$colorCounter];
            $colorCounter = ($colorCounter+1)%count($colors);           
        }
        
        // Look for daily comments to display
        $dailyCom = mysql_query("   SELECT  `hash`,
                                            MAX(UNIX_TIMESTAMP(`date`)) AS maxDate,
                                            GROUP_CONCAT(CONCAT_WS(' </span>',author,comment) ORDER BY date DESC SEPARATOR '%%%') AS comment
                                    FROM `b2_comments` 
                                    WHERE DATEDIFF(DATE(`date`), '".substr($day,0,4).'-'.substr($day,4,2).'-'.substr($day,6)."') < 4
                                    GROUP BY `hash`");

        $nbCom = mysql_num_rows($dailyCom);
        $nbLink = count($linksToDisplay);

        for ($y = 0; $y < $nbCom; $y++)
        {
            $com = mysql_fetch_array($dailyCom);
            if(Date('Ymd', $com['maxDate']) == $day){
                $article = $LINKSDB->filterSmallHash($com['hash']);  // we look for the title of the article commented (from its smallhash)
                foreach($article as $key=>$li){
                
                    // then we cut and split the concat version of the comments that we have received from the database
                    $comShorten = substr($com['comment'], 0, 1000);
                    $comShorten = str_replace('@@plus@@', '+', $comShorten);
                    $comShorten = str_replace('@@etcom@@', '&', $comShorten);
                    $comToDisplay = array_slice(explode('%%%', $comShorten), 0, 5);
                    foreach($comToDisplay as $i => $item){
                        $comArray[$i]['text'] = $item;
                        $comArray[$i]['opacity'] = 1-(0.2*$i);
                    }
                    $comArray = array_reverse($comArray);

                    // we check if the related article was posted the same day (in this case we display the comments just below it)
                    if(isset($linksToDisplay[$key])){
                            
                        $linksToDisplay[$key]['formatedDescription'] .= '<br/><br/><div style="display:table;">';
                        foreach($comArray as $com){
                            $linksToDisplay[$key]['formatedDescription'] .= '<div class="comment" style="opacity:'.$com['opacity'].';"><span>'.text2clickable(stripslashes($com['text'])).'</div>';
                        }
                        $linksToDisplay[$key]['formatedDescription'] .= '</div>';

                    }else{

                        $linksToDisplay[$key]['formatedDescription'] = array_map('stripslashes_deep', $comArray);
                        $linksToDisplay[$key]['linkdate'] = $key;
                        $linksToDisplay[$key]['author'] = $li['author'];
                        $linksToDisplay[$key]['thumbnail'] = false;
                        $linksToDisplay[$key]['description'] = $comShorten;
                        $linksToDisplay[$key]['title'] = $li['title'];
                        $linksToDisplay[$key]['url'] = $li['url'];
                        $linksToDisplay[$key]['comment'] = true;
                        $linksToDisplay[$key]['header'] = "Nouveau commentaire sur l'article :";
                        $linksToDisplay[$key]['color'] = $colors[$colorCounter];
                        $colorCounter = ($colorCounter+1)%count($colors); 

                    }
                    unset($comArray);
                }
            }
        }
        
        /* We need to spread the articles on 3 columns.
           I did not want to use a javascript lib like http://masonry.desandro.com/
           so I manually spread entries with a simple method: I roughly evaluate the 
           height of a div according to title and description length.
        */
        $columns=array(array(),array(),array()); // Entries to display, for each column.
        $fill=array(0,0,0);  // Rough estimate of columns fill.
        foreach($linksToDisplay as $key=>$link)
        {
            // Roughly estimate length of entry (by counting characters)
            // Title: 30 chars = 1 line. 1 line is 30 pixels height.
            // Description: 836 characters gives roughly 342 pixel height.
            // This is not perfect, but it's usually ok.
            $length=strlen($link['title'])+(342*strlen($link['description']))/836;
            //if ($link['thumbnail']) $length +=100; // 1 thumbnails roughly takes 100 pixels height.
            // Then put in column which is the less filled:
            $smallest=min($fill); // find smallest value in array.
            $index=array_search($smallest,$fill); // find index of this smallest value.
            array_push($columns[$index],$link); // Put entry in this column.
            $fill[$index]+=$length;
        }
        $PAGE = new pageBuilder;
        $PAGE->assign('linksToDisplay',$linksToDisplay);
        $PAGE->assign('col1',$columns[0]);
        $PAGE->assign('col2',$columns[1]);
        $PAGE->assign('col3',$columns[2]);
        $PAGE->assign('day',linkdate2daily($day.'_000000'));
        $PAGE->assign('previousday',$previousday);
        $PAGE->assign('nextday',$nextday);    
        $PAGE->renderPage('daily');
        exit;
    }



    // -------- User wants to change his/her password.
    if (isset($_SERVER["QUERY_STRING"]) && startswith($_SERVER["QUERY_STRING"],'do=changepasswd'))
    {
        if (!empty($_POST['setpassword']) && !empty($_POST['oldpassword']))
        {

            // Make sure old password is correct.
            //connexion BDD
            require 'data/database.php';
            
            $hash = sha1($_POST['oldpassword'].who().$GLOBALS['salt']);
            
            // recherche de l'utilisateur
            $result = mysql_query(" SELECT * 
                                    FROM  `b2_members` 
                                    WHERE login = '".who()."' AND password = '".$hash."'
            ");
            
            if ($result && mysql_num_rows($result)!=0)
            {
                $update = mysql_query(" UPDATE  `b2_members` 
                                        SET  `password` =  '".sha1($_POST['setpassword'].who().$GLOBALS['salt'])."'
                                        WHERE  `b2_members`.`login` = '".who()."'
                ");
                echo '<script language="JavaScript">alert("Your password has been changed.");document.location=\'?\';</script>';
                exit;
            }
            else
            {
                echo '<script language="JavaScript">alert("The old password is not correct.");document.location=\'?\';</script>';
                exit;
            }
        }
    }

    // -------- User clicked the "Save" button when editing a link: Save link to database.
    if (isset($_POST['save_edit']))
    {
        $tags = trim(preg_replace('/\s\s+/',' ', $_POST['lf_tags'])); // Remove multiple spaces.
        $linkdate=$_POST['lf_linkdate'];
        $link = array(  'title' => trim($_POST['lf_title']),
                        'url' => trim($_POST['lf_url']),
                        'description' => trim($_POST['lf_description']),
                        'private' => (isset($_POST['lf_private']) ? 1 : 0),
                        'linkdate' => $linkdate,
                        'tags' => str_replace(',',' ',$tags),
                        'author' => $_SESSION['username'],
                        'awesome' => trim($_POST['lf_awesome'])
                    );
        if(who() == 'admin'){ $link['author'] = trim($_POST['lf_author']); } // allow the admin to do invisible edit !
        if ($link['title']=='') $link['title']=$link['url']; // If title is empty, use the URL as title.
        $LINKSDB[$linkdate] = $link;
        $LINKSDB->savedb(); // save to disk
        pubsubhub();
        invalidateCaches();

        // If we are called from the bookmarklet, we must close the popup:
        if (isset($_GET['source']) && $_GET['source']=='bookmarklet') { echo '<script language="JavaScript">self.close();</script>'; exit; }
        $returnurl = ( isset($_POST['returnurl']) ? $_POST['returnurl'] : '?' );
        header('Location: '.$returnurl); // After saving the link, redirect to the page the user was on.
        exit;
    }

    // -------- User clicked the "Cancel" button when editing a link.
    if (isset($_POST['cancel_edit']))
    {
        // If we are called from the bookmarklet, we must close the popup;
        if (isset($_GET['source']) && $_GET['source']=='bookmarklet') { echo '<script language="JavaScript">self.close();</script>'; exit; }
        $returnurl = ( isset($_POST['returnurl']) ? $_POST['returnurl'] : '?' );
        header('Location: '.$returnurl); // After canceling, redirect to the page the user was on.
        exit;
    }

    // -------- User clicked the "Delete" button when editing a link : Delete link from database.
    if (isset($_POST['delete_link']))
    {
        // We do not need to ask for confirmation:
        // - confirmation is handled by javascript
        $linkdate=$_POST['lf_linkdate'];
        unset($LINKSDB[$linkdate]);
        $LINKSDB->savedb(); // save to disk
        invalidateCaches();
        // If we are called from the bookmarklet, we must close the popup:
        if (isset($_GET['source']) && $_GET['source']=='bookmarklet') { echo '<script language="JavaScript">self.close();</script>'; exit; }
        $returnurl = ( isset($_POST['returnurl']) ? $_POST['returnurl'] : '?' );
        header('Location: ?'); // After deleting the link, redirect to main page.
        exit;
    }

    // -------- User clicked the "EDIT" button on a link: Display link edit form.
    if (isset($_GET['edit_link']))
    {
        $link = $LINKSDB[$_GET['edit_link']];  // Read database
        if (!$link) { header('Location: ?'); exit; } // Link not found in database.
        $PAGE->assign('link',$link);
        $PAGE->assign('link_is_new',false);
        $PAGE->assign('http_referer',(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));
    }

    // -------- User want to post a new link: Display link edit form.
    if (isset($_GET['post']))
    {
        $url=$_GET['post'];

        // We remove the annoying parameters added by FeedBurner and GoogleFeedProxy (?utm_source=...)
        $i=strpos($url,'&utm_source='); if ($i!==false) $url=substr($url,0,$i);
        $i=strpos($url,'?utm_source='); if ($i!==false) $url=substr($url,0,$i);
        $i=strpos($url,'#xtor=RSS-'); if ($i!==false) $url=substr($url,0,$i);

        $link = $LINKSDB->getLinkFromUrl($url); // Check if URL is not already in database

        if($link && $link['author']!=who())     // the url is already in DB, and posted by a different user. We display only a message and the existing article.
        {
            $message = 'Sorry, this link has already been posted by '.ucwords($link['author']).' :';
            header('Location: ?'.smallHash($link['linkdate']).'&sms='.$message);
            exit;
        }
        elseif (!$link)     // the url doesn't exist already in DB
        {
            $link_is_new = true;  // This is a new link
            $linkdate = strval(date('Ymd_His'));    // give it a date
            $title = (empty($_GET['title']) ? '' : $_GET['title'] );    // Get title if it was provided in URL (by the bookmarklet).
            $description=''; $tags=''; $private=0;
            if (($url!='') && parse_url($url,PHP_URL_SCHEME)=='') $url = 'http://'.$url;
            // If this is an HTTP link, we try go get the page to extact the title (otherwise we will to straight to the edit form.)
            if (empty($title) && parse_url($url,PHP_URL_SCHEME)=='http')
            {
                list($status,$headers,$data) = getHTTP($url,4); // Short timeout to keep the application responsive.
                // FIXME: Decode charset according to specified in either 1) HTTP response headers or 2) <head> in html
                if (strpos($status,'200 OK')!==false) $title=html_entity_decode(html_extract_title($data),ENT_QUOTES,'UTF-8');
            }
            if ($url=='') $url='?'.smallHash($linkdate); // In case of empty URL, this is just a text (with a link that point to itself)
            $link = array('linkdate'=>$linkdate,'title'=>$title,'url'=>$url,'description'=>$description,'author'=>who(),'private'=>0, 'awesome'=>'');
            buildLinkList($PAGE); // Compute list of links to display
            $PAGE->assign('link',$link);
            $PAGE->assign('link_is_new',true);
            $PAGE->assign('http_referer',(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''));
        }
        else    // the url already exist in DB but have been posted by user. We allow him to edit.
        {
            $message = 'You already posted that link... to edit it, click on the "edit button" below :';
            header('Location: ?'.smallHash($link['linkdate']).'&sms='.$message);
            exit;
        }

        $PAGE->renderPage('linklist');
        exit;
    }

    // --------- Office (office presentation of b²book) ----------------------
    if (isset($_SERVER["QUERY_STRING"]) && startswith($_SERVER["QUERY_STRING"],'do=office'))
    { 
        buildLinkList($PAGE); // Compute list of links to display
        $PAGE->renderPage('office');
        exit;
    }

    // --------- Statistic page ----------------------
    if (isset($_SERVER["QUERY_STRING"]) && startswith($_SERVER["QUERY_STRING"],'do=stats'))
    { 
        $PAGE->renderPage('stats');
        exit;
    }

    // -------- If there is a message to display.
    if (isset($_GET['sms'])){ $PAGE->assign('message',$_GET['sms']); }
    buildLinkList($PAGE); // Compute list of links to display
    $PAGE->renderPage('linklist');
    exit;
}


/* This class is in charge of building the final page.
   (This is basically a wrapper around RainTPL which pre-fills some fields.)
   p = new pageBuilder;
   p.assign('myfield','myvalue');
   p.renderPage('mytemplate');
   
*/
class pageBuilder
{
    private $tpl; // RainTPL template
    function __construct()
    {
        $this->tpl=false;
    } 

    private function initialize()
    {    
        global $LINKSDB;
        $this->tpl = new RainTPL;    
        $this->tpl->assign('newversion',checkUpdate());
        $this->tpl->assign('linkcount',count($LINKSDB));
        $this->tpl->assign('persolinkcount',count($LINKSDB->filterAuthor(trim(who()))));
        $this->tpl->assign('feedurl',htmlspecialchars(indexUrl()));
        $searchcrits=''; // Search criteria
        if (!empty($_GET['searchtags'])) $searchcrits.='&searchtags='.urlencode($_GET['searchtags']);
        elseif (!empty($_GET['searchterm'])) $searchcrits.='&searchterm='.urlencode($_GET['searchterm']);
        $this->tpl->assign('searchcrits',$searchcrits);
        $this->tpl->assign('source',indexUrl());
        $this->tpl->assign('version',shaarli_version);
        $this->tpl->assign('scripturl',indexUrl());
        $this->tpl->assign('pagetitle','Shaarli');
        $this->tpl->assign('privateonly',!empty($_SESSION['privateonly'])); // Show only private links ?
        if (!empty($GLOBALS['title'])) $this->tpl->assign('pagetitle',$GLOBALS['title']);
        if (!empty($GLOBALS['pagetitle'])) $this->tpl->assign('pagetitle',$GLOBALS['pagetitle']);
        $this->tpl->assign('shaarlititle',empty($GLOBALS['title']) ? 'Shaarli': $GLOBALS['title'] );
        return;    
    }
    
    // The following assign() method is basically the same as RainTPL (except that it's lazy)
    public function assign($what,$where)
    {
        if ($this->tpl===false) $this->initialize(); // Lazy initialization
        $this->tpl->assign($what,$where);
    }
    
    // Render a specific page (using a template).
    // eg. pb.renderPage('picwall')
    public function renderPage($page)
    {
        if ($this->tpl===false) $this->initialize(); // Lazy initialization
        $this->tpl->draw($page);
    }
}
?>