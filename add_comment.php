<?php

require_once('login_check.php');

if (!isset($_POST['c']) || trim($_POST['c']) == '') {
	die('You need to actually write a comment.');
}

require_once('includes/dbconn.php');

$comment_db = "'".$mysqli->escape_string(htmlentities(trim($_POST['c'])))."'";
$uid_db = (int) $user_cookie['user_id'] * 1;

$insert_comment = $mysqli->query("INSERT INTO commenting (uid, thecomment, thedate, tsc) VALUES ($uid_db, $comment_db, CURDATE(), UNIX_TIMESTAMP())");

if (!$insert_comment) {
	die('database error: '.$mysqli->error);
}

header('Location: index.php');

?>