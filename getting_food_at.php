<?php

// set where the user is getting their food
// add it as a comment

// if it already exists for data, replace it

require_once('login_check.php');

if (!isset($_POST['eatery_id']) || trim($_POST['eatery_id']) == '' || !is_numeric($_POST['eatery_id'])) {
	die('you did not supply a valid eatery!');
}

// snarky suffixes to add to this nonsense
$comment_suffixes = array(', lol', '!', ', oh dear.', '...', ' with your mom.');

$eatery_id = (int) $_POST['eatery_id'] * 1;

require_once('includes/dbconn.php');

$get_eatery_name = $mysqli->query("SELECT name FROM destinations WHERE id=$eatery_id");
if (!$get_eatery_name || $get_eatery_name->num_rows == 0) { die('eatery not found'); }
$eatery_result = $get_eatery_name->fetch_assoc();

$eatery_comment = "I'm getting food at <b>".$eatery_result['name']."</b>".$comment_suffixes[array_rand($comment_suffixes)];

$comment_db = "'".$mysqli->escape_string($eatery_comment)."'";
$uid_db = (int) $user_cookie['user_id'] * 1;

$check_for_existing_eatery_selection = $mysqli->query("SELECT cid FROM commenting WHERE eatery_notice=1 AND thedate=CURDATE() AND uid=$uid_db");

if ($check_for_existing_eatery_selection->num_rows > 0) {
	$existing_eatery_row_result = $check_for_existing_eatery_selection->fetch_assoc();
	$insert_comment = $mysqli->query("UPDATE commenting SET thecomment=$comment_db, tsc=UNIX_TIMESTAMP() WHERE cid=".$existing_eatery_row_result['cid']);
} else {
	$insert_comment = $mysqli->query("INSERT INTO commenting (uid, thecomment, thedate, eatery_notice, tsc) VALUES ($uid_db, $comment_db, CURDATE(), 1, UNIX_TIMESTAMP())");
}

if (!$insert_comment) {
	die('database error: '.$mysqli->error);
}

header('Location: index.php');

?>
