<?php
// Template for the list of links (<div id="linklist">)
// This function fills all the necessary fields in the $PAGE for the template 'linklist.html'
function buildLinkList($PAGE, $hash)
{
    global $LINKSDB;  // Get the links database.

    // ---- Filter link database according to parameters
    $linksToDisplay=array();
    $search_type='';
    $search_crits='';
    if (!empty($_GET['searchterm'])) // Fulltext search
    {
        $linksToDisplay = $LINKSDB->filterFulltext(trim($_GET['searchterm']));
        $search_crits=htmlspecialchars(trim($_GET['searchterm']));
        $search_type='fulltext';
    }
    elseif (!empty($_GET['searchtags'])) // Search by author
    {
        $linksToDisplay = $LINKSDB->filterAuthor(trim($_GET['searchtags']));
        $search_crits=explode(' ',trim($_GET['searchtags']));
        $search_type='tags';
    }
    elseif (isset($_GET['bookmarks'])) // Display bookmarked links
    {
        $bookmarks = mysql_query(" SELECT bookmarks FROM `b2_members` WHERE login = '".who()."'");
        $bookmarks = mysql_fetch_array($bookmarks);
        $bookmarks = unserialize(gzinflate(base64_decode($bookmarks[0])));
        $linksToDisplay = $LINKSDB->filterSmallHash($bookmarks);
        $search_type='permalink';
    }
    elseif (isset($_GET['source']) && $_GET['source']=='bookmarklet')     // Detect post from bookmarklet
    {
        $linksToDisplay = array();
        $search_type='bookmarklet';
    } 
    elseif (isset($_SERVER['QUERY_STRING']) && preg_match('/[a-zA-Z0-9-_@]{6}(&.+?)?/',$_SERVER['QUERY_STRING']) && !isset($_GET['post']) && !isset($_GET['do'])) // Detect smallHashes in URL
    {
        $linksToDisplay = $LINKSDB->filterSmallHash(substr(trim($_SERVER["QUERY_STRING"], '/'),0,6));
        $search_type='permalink';
    }
    else
        $linksToDisplay = $LINKSDB;  // otherwise, display without filtering.
    
    // Option: Show only private links
    if (!empty($_SESSION['privateonly']))
    {
        $tmp = array();
        foreach($linksToDisplay as $linkdate=>$link)
        {
            if ($link['private']!=0) $tmp[$linkdate]=$link;
        }
        $linksToDisplay=$tmp;
    }

    // ---- Handle paging.
    /* Can someone explain to me why you get the following error when using array_keys() on an object which implements the interface ArrayAccess ???
       "Warning: array_keys() expects parameter 1 to be array, object given in ... "
       If my class implements ArrayAccess, why won't array_keys() accept it ?  ( $keys=array_keys($linksToDisplay); )
    */
    $keys=array(); foreach($linksToDisplay as $key=>$value) { $keys[]=$key; } // Stupid and ugly. Thanks php.

    // If there is only a single link, we change on-the-fly the title of the page.
    if (count($linksToDisplay)==1) $GLOBALS['pagetitle'] = $linksToDisplay[$keys[0]]['title'].' - '.$GLOBALS['title'];

    // retrieve bookmarked links
    $bookmarks = mysql_query(" SELECT bookmarks FROM `b2_members` WHERE login = '".who()."'");
    $bookmarks = mysql_fetch_array($bookmarks);
    $bookmarks = unserialize(gzinflate(base64_decode($bookmarks[0])));

    // Select articles according to paging.
    $pagecount = ceil(count($keys)/$_SESSION['LINKS_PER_PAGE']);
    $pagecount = ($pagecount==0 ? 1 : $pagecount);
    $page=( empty($_GET['page']) ? 1 : intval($_GET['page']));
    $page = ( $page<1 ? 1 : $page );
    $page = ( $page>$pagecount ? $pagecount : $page );
    $i = ($page-1)*$_SESSION['LINKS_PER_PAGE']; // Start index.
    $end = $i+$_SESSION['LINKS_PER_PAGE'];
    $linkDisp=array(); // Links to display
    while ($i<$end && $i<count($keys))
    {
        $link = $linksToDisplay[$keys[$i]];
        //$link['description']=nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description']))));
        $title=$link['title'];
        $classLi =  $i%2!=1 ? '' : 'publicLinkHightLight';
        $link['class'] = ($link['private']==0 ? $classLi : 'private');
        $link['localdate']=linkdate2locale($link['linkdate']);
        $count = mysql_query("  SELECT COUNT(*) AS 'count' 
                                FROM `b2_comments` 
                                WHERE hash = '".smallHash($link['linkdate'])."'");
        $count = mysql_fetch_array($count);
        $link['commentCount'] = $count['count'];

        // détection des posts favoris
        $link['bookmarked'] = false;
        if (is_int(array_search(smallHash($link['linkdate']), $bookmarks)) ) {
            $link['bookmarked'] = true;
        }

        //formatage des informations de l'awesometer
        $link['awesome_blank'] = true;
        $link['awesome_count'] = 0;
        $link['note_globale'] = 0;
        $link['note_perso'] = -1;

        //si il y a déjà eu un vote
        if(isset($link['awesome']) && $link['awesome']!=''){
            $link['awesome_blank'] = false;
            $awesome_table = unserialize($link['awesome']);
            //on vérifie qu'il n'y ait pas d'autolike ...
            if(isset($awesome_table[$link['author']])){
                unset($awesome_table[$link['author']]);
            }
            //la note globale est la moyenne des vote
            $link['note_globale'] = round(array_sum($awesome_table)/count($awesome_table));
            //on enregistre aussi le nombre de votant
            $link['awesome_count'] = count($awesome_table);
            //et la note perso si l'utilisateur à déjà voté
            if(isset($awesome_table[who()])) { $link['note_perso'] = $awesome_table[who()]; }
        }

        $linkDisp[$keys[$i]] = $link;
        $i++;
    }
    
    // Compute paging navigation
    $searchterm= ( empty($_GET['searchterm']) ? '' : '&searchterm='.$_GET['searchterm'] );
    $searchtags= ( empty($_GET['searchtags']) ? '' : '&searchtags='.$_GET['searchtags'] );
    $paging='';
    $previous_page_url=''; if ($i!=count($keys)) $previous_page_url='?page='.($page+1).$searchterm.$searchtags;
    $next_page_url='';if ($page>1) $next_page_url='?page='.($page-1).$searchterm.$searchtags;
 
    // Fill all template fields.
    $PAGE->assign('previous_page_url',$previous_page_url);
    $PAGE->assign('next_page_url',$next_page_url);
    $PAGE->assign('page_current',$page);
    $PAGE->assign('page_max',$pagecount);
    $PAGE->assign('result_count',count($linksToDisplay));
    $PAGE->assign('search_type',$search_type);
    $PAGE->assign('search_crits',$search_crits);   
    $PAGE->assign('redirector',empty($GLOBALS['redirector']) ? '' : $GLOBALS['redirector']); // optional redirector URL
    $PAGE->assign('links',$linkDisp);
    return;
}
?>