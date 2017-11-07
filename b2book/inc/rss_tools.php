<?php
// Ouput the last 50 links in RSS 2.0 format.
function showRSS()
{
    global $LINKSDB;

    // Optionnaly filter the results:
    $linksToDisplay=array();
    if (!empty($_GET['searchterm'])) $linksToDisplay = $LINKSDB->filterFulltext($_GET['searchterm']);
    elseif (!empty($_GET['searchtags']))   $linksToDisplay = $LINKSDB->filterTags(trim($_GET['searchtags']));
    else $linksToDisplay = $LINKSDB;

    header('Content-Type: application/rss+xml; charset=utf-8');
    $pageaddr=htmlspecialchars(indexUrl());
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">';
    echo '<channel><title>'.htmlspecialchars($GLOBALS['title']).'</title><link>'.$pageaddr.'</link>';
    echo '<description>Shared links</description><language>en-en</language><copyright>'.$pageaddr.'</copyright>'."\n\n";
    if (!empty($GLOBALS['config']['PUBSUBHUB_URL']))
    {
        echo '<!-- PubSubHubbub Discovery -->';
        echo '<link rel="hub" href="'.htmlspecialchars($GLOBALS['config']['PUBSUBHUB_URL']).'" xmlns="http://www.w3.org/2005/Atom" />';
        echo '<link rel="self" href="'.htmlspecialchars($pageaddr).'?do=rss" xmlns="http://www.w3.org/2005/Atom" />';
        echo '<!-- End Of PubSubHubbub Discovery -->';
    }
    $i=0;
    $keys=array(); foreach($linksToDisplay as $key=>$value) { $keys[]=$key; }  // No, I can't use array_keys().
    while ($i<50 && $i<count($keys))
    {
        $link = $linksToDisplay[$keys[$i]];
        $guid = $pageaddr.'?'.smallHash($link['linkdate']);
        $rfc822date = linkdate2rfc822($link['linkdate']);
        $absurl = htmlspecialchars($link['url']);
        if (startsWith($absurl,'?')) $absurl=$pageaddr.$absurl;  // make permalink URL absolute
        echo '<item><title>'.htmlspecialchars($link['title']).'</title><guid>'.$guid.'</guid><link>'.$absurl.'</link>';
        if (!$GLOBALS['config']['HIDE_TIMESTAMPS'] || isLoggedIn()) echo '<pubDate>'.htmlspecialchars($rfc822date)."</pubDate>\n";
        if ($link['tags']!='') // Adding tags to each RSS entry (as mentioned in RSS specification)
        {
            foreach(explode(' ',$link['tags']) as $tag) { echo '<category domain="'.htmlspecialchars($pageaddr).'">'.htmlspecialchars($tag).'</category>'."\n"; }
        }
        echo '<description><![CDATA['.nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description'])))).']]></description>'."\n</item>\n";
        $i++;
    }
    echo '</channel></rss>';
    exit;
}

// ------------------------------------------------------------------------------------------
// Ouput the last 50 links in ATOM format.
function showATOM()
{
    global $LINKSDB;

    // Optionnaly filter the results:
    $linksToDisplay=array();
    if (!empty($_GET['searchterm'])) $linksToDisplay = $LINKSDB->filterFulltext($_GET['searchterm']);
    elseif (!empty($_GET['searchtags']))   $linksToDisplay = $LINKSDB->filterTags(trim($_GET['searchtags']));
    else $linksToDisplay = $LINKSDB;

    header('Content-Type: application/atom+xml; charset=utf-8');
    $pageaddr=htmlspecialchars(indexUrl());
    $latestDate = '';
    $entries='';
    $i=0;
    $keys=array(); foreach($linksToDisplay as $key=>$value) { $keys[]=$key; }  // No, I can't use array_keys().
    while ($i<50 && $i<count($keys))
    {
        $link = $linksToDisplay[$keys[$i]];
        $guid = $pageaddr.'?'.smallHash($link['linkdate']);
        $iso8601date = linkdate2iso8601($link['linkdate']);
        $latestDate = max($latestDate,$iso8601date);
        $absurl = htmlspecialchars($link['url']);
        if (startsWith($absurl,'?')) $absurl=$pageaddr.$absurl;  // make permalink URL absolute
        $entries.='<entry><title>'.htmlspecialchars($link['title']).'</title><link href="'.$absurl.'" /><id>'.$guid.'</id>';
        if (!$GLOBALS['config']['HIDE_TIMESTAMPS'] || isLoggedIn()) $entries.='<updated>'.htmlspecialchars($iso8601date).'</updated>';
        $entries.='<content type="html">'.htmlspecialchars(nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($link['description'])))))."</content>\n";
        if ($link['tags']!='') // Adding tags to each ATOM entry (as mentioned in ATOM specification)
        {
            foreach(explode(' ',$link['tags']) as $tag)
                { $entries.='<category scheme="'.htmlspecialchars($pageaddr,ENT_QUOTES).'" term="'.htmlspecialchars($tag,ENT_QUOTES).'" />'."\n"; }
        }
        $entries.="</entry>\n";
        $i++;
    }
    $feed='<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom">';
    $feed.='<title>'.htmlspecialchars($GLOBALS['title']).'</title>';
    if (!$GLOBALS['config']['HIDE_TIMESTAMPS'] || isLoggedIn()) $feed.='<updated>'.htmlspecialchars($latestDate).'</updated>';
    $feed.='<link rel="self" href="'.htmlspecialchars(serverUrl().$_SERVER["REQUEST_URI"]).'" />';
    if (!empty($GLOBALS['config']['PUBSUBHUB_URL']))
    {
        $feed.='<!-- PubSubHubbub Discovery -->';
        $feed.='<link rel="hub" href="'.htmlspecialchars($GLOBALS['config']['PUBSUBHUB_URL']).'" />';
        $feed.='<!-- End Of PubSubHubbub Discovery -->';
    }
    $feed.='<author><name>'.htmlspecialchars($pageaddr).'</name><uri>'.htmlspecialchars($pageaddr).'</uri></author>';
    $feed.='<id>'.htmlspecialchars($pageaddr).'</id>'."\n\n"; // Yes, I know I should use a real IRI (RFC3987), but the site URL will do.
    $feed.=$entries;
    $feed.='</feed>';
    echo $feed;
    exit;
}

// ------------------------------------------------------------------------------------------
// Daily RSS feed: 1 RSS entry per day giving all the links on that day.
// Gives the last 7 days (which have links).
// This RSS feed cannot be filtered.
function showDailyRSS()
{
    global $LINKSDB;
    
    /* Some Shaarlies may have very few links, so we need to look
       back in time (rsort()) until we have enough days ($nb_of_days).
    */
    $linkdates=array(); foreach($LINKSDB as $linkdate=>$value) { $linkdates[]=$linkdate; } 
    rsort($linkdates);
    $nb_of_days=7; // We take 7 days.
    $today=Date('Ymd');
    $days=array();
    foreach($linkdates as $linkdate)
    {
        $day=substr($linkdate,0,8); // Extract day (without time)
        if (strcmp($day,$today)<0)
        {
            if (empty($days[$day])) $days[$day]=array();
            $days[$day][]=$linkdate;
        }
        if (count($days)>$nb_of_days) break; // Have we collected enough days ?
    }
    
    // Build the RSS feed.
    header('Content-Type: application/rss+xml; charset=utf-8');
    $pageaddr=htmlspecialchars(indexUrl());
    echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0">';
    echo '<channel><title>Daily - '.htmlspecialchars($GLOBALS['title']).'</title><link>'.$pageaddr.'</link>';
    echo '<description>Daily shared links</description><language>en-en</language><copyright>'.$pageaddr.'</copyright>'."\n";
    
    foreach($days as $day=>$linkdates) // For each day.
    {
        $daydate = utf8_encode(strftime('%A %d, %B %Y',linkdate2timestamp($day.'_000000'))); // Full text date
        $rfc822date = linkdate2rfc822($day.'_000000');
        $absurl=htmlspecialchars(indexUrl().'?do=daily&day='.$day);  // Absolute URL of the corresponding "Daily" page.
        echo '<item><title>'.htmlspecialchars($GLOBALS['title'].' - '.$daydate).'</title><guid>'.$absurl.'</guid><link>'.$absurl.'</link>';
        echo '<pubDate>'.htmlspecialchars($rfc822date)."</pubDate>";
        
        // Build the HTML body of this RSS entry.
        $html='';
        $href='';
        $links=array();
        // We pre-format some fields for proper output.
        foreach($linkdates as $linkdate)
        {
            $l = $LINKSDB[$linkdate];
            $l['formatedDescription']=nl2br(keepMultipleSpaces(text2clickable(htmlspecialchars($l['description']))));
            $l['thumbnail'] = thumbnail($l['url']);  
            $l['localdate']=linkdate2locale($l['linkdate']);            
            if (startsWith($l['url'],'?')) $l['url']=indexUrl().$l['url'];  // make permalink URL absolute
            $links[$linkdate]=$l;    
        }
        // Then build the HTML for this day:
        $tpl = new RainTPL;    
        $tpl->assign('links',$links);
        $html = $tpl->draw('dailyrss',$return_string=true);
        echo "\n";
        echo '<description><![CDATA['.$html.']]></description>'."\n</item>\n\n";

    }    
    echo '</channel></rss>';
    exit;
}
?>