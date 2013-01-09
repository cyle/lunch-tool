<?php

require_once('login_check.php');

if (!isset($_POST['vote']) || !is_numeric($_POST['vote'])) {
	die('You need to vote.');
}

$vote = (int) $_POST['vote'] * 1;

require_once('includes/dbconn.php');

// check if they've voted already
$check_vote = $mysqli->query('SELECT * FROM votes WHERE uid='.$user_cookie['user_id'].' AND lunchdate=CURDATE()');
if ($check_vote->num_rows > 0) {
	die('Sorry but you cannot vote more than one. Nice try.');
}

// submit vote
$submit_vote = $mysqli->query('INSERT INTO votes (uid, oid, lunchdate, ts) VALUES ('.$user_cookie['user_id'].', '.$vote.', CURDATE(), UNIX_TIMESTAMP())');
if (!$submit_vote) {
	die('Mysql error: '.$mysqli->error);
}

header('Location: index.php');
die();

?>
