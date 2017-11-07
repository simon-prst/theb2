<?php

/*
Ajax destination for any actions related to articles.
What to do is determine by the variables received.
- display, store or delete a comment associated with an article
- rate an article (the little stars !)
- bookmark an article

*/

require 'data/config.php';
require 'inc/authentication.php';
require 'inc/misc_functions.php';
require 'inc/linkdb.class.php';
require 'data/database.php';

define('INACTIVITY_TIMEOUT',3600);
session_start();

if(isLoggedIn()){	// we need the user to be logged in


// display all comments for an article
// ------------------------------------------------------------------------------
if(isset($_GET['hash'])){

	$result = mysql_query("	SELECT * 
							FROM  `b2_comments` 
							WHERE hash = '".mysql_real_escape_string($_GET['hash'])."'
							ORDER BY date ASC
	");
	$nb = mysql_num_rows($result);
	if($nb == 0){
		echo 'No comment for now ...<info>';
	}else{
		for ($i = 0; $i < $nb; $i++){
			$comment = mysql_fetch_array($result);
			$com = $comment['comment'];

			// XSS (naive) protection
			$com = str_ireplace("<script", "", $com);
			$com = str_ireplace("<iframe", "", $com);
			$com = str_ireplace("<div", "", $com);

			echo $comment['author'] . '<info>' . text2clickable(stripslashes($com)) . '<info>' . $comment['id'];
			if($i!=$nb-1) echo '<com>';
		}
	}
	echo '<who>'.$_SESSION['username']; // we send the name of current user so that client-side script knows where to put the "delete comment" icon


// store a new comment
// ------------------------------------------------------------------------------
}elseif(isset($_POST['comment'])){

	$result = mysql_query("	INSERT INTO `b2_comments` (`id`, `hash`, `date`, `author`, `comment`)
							VALUES (	NULL, 
										'".mysql_real_escape_string($_POST['hash'])."',	
										CURRENT_TIMESTAMP, 
										'".mysql_real_escape_string($_SESSION['username'])."', 
										'".mysql_real_escape_string($_POST['comment'])."'
									)
	");
		

// delete a comment
// ------------------------------------------------------------------------------
}elseif(isset($_POST['id'])){

	$result = mysql_query("	DELETE FROM `b2_comments` WHERE id=" . mysql_real_escape_string($_POST['id']) . " AND author='" . mysql_real_escape_string($_SESSION['username']) ."'");


// rate an article (the little stars !)
// ------------------------------------------------------------------------------
}elseif(isset($_POST['awesome'])){

	$linkdate = $_POST['awesome'];
	$LINKSDB = new linkdb(true);  // Read links from database.
	$article = $LINKSDB[$linkdate];

	if ($article['author'] == $_SESSION['username']) {
		echo 'autolike';
	}else{
		if(isset($article['awesome'])){
			$awesome_table = unserialize($article['awesome']);
		}
		// We make sure the grade is between 0 and 65 (thank you pierre_l !)
		$sanitized_howmuch = min($_POST['howmuch'], 65);
		$sanitized_howmuch = max($sanitized_howmuch, 0);
		$awesome_table[$_SESSION['username']] = $sanitized_howmuch;
		
		$article['awesome'] = serialize($awesome_table);

		echo round(array_sum($awesome_table)/count($awesome_table)) . "##" . count($awesome_table);
		$LINKSDB[$linkdate] = $article;
		$LINKSDB->savedb(); // save to disk
	}


// bookmark an article
// ------------------------------------------------------------------------------
}elseif(!empty($_POST['bookmark'])){

	$bookmarks = mysql_query(" SELECT bookmarks FROM `b2_members` WHERE login = '".mysql_real_escape_string($_SESSION['username'])."'");
    $bookmarks = mysql_fetch_array($bookmarks);
    $bookmarks = unserialize(gzinflate(base64_decode($bookmarks[0])));

    if (!is_int(array_search($_POST['bookmark'], $bookmarks))) {
    	$bookmarks[] = $_POST['bookmark'];
    }

    $bookmarks = base64_encode(gzdeflate(serialize($bookmarks)));
	$result = mysql_query("	UPDATE  `b2_members` SET  `bookmarks` = '".$bookmarks."'  WHERE  `b2_members`.`login` = '".mysql_real_escape_string($_SESSION['username'])."'");


// un-bookmark an article
// ------------------------------------------------------------------------------
}elseif(!empty($_POST['unbookmark'])){

	$bookmarks = mysql_query(" SELECT bookmarks FROM `b2_members` WHERE login = '".mysql_real_escape_string($_SESSION['username'])."'");
    $bookmarks = mysql_fetch_array($bookmarks);
    $bookmarks = unserialize(gzinflate(base64_decode($bookmarks[0])));

    $search = array_search($_POST['unbookmark'], $bookmarks);
	if (is_int($search)) {
		unset($bookmarks[$search]);
		$bookmarks = array_values($bookmarks);
	}

    $bookmarks = base64_encode(gzdeflate(serialize($bookmarks)));
	$result = mysql_query("	UPDATE  `b2_members` SET  `bookmarks` = '".$bookmarks."'  WHERE  `b2_members`.`login` = '".mysql_real_escape_string($_SESSION['username'])."'");

}
}

?>