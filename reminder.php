<?php

// send a reminder to vote!

require_once('includes/dbconn.php');

// get people to send a reminder to
// people who have not voted today!
$mail_people = array();
$get_mail_people = $mysqli->query('SELECT ecnet FROM users WHERE id NOT IN (SELECT uid FROM votes WHERE lunchdate=CURDATE())');
while ($mail_row = $get_mail_people->fetch_assoc()) {
	$mail_people[] = strtolower(trim($mail_row['ecnet'])).'@whatever.com';
}

if (count($mail_people) == 0) {
	die('no one to send to');
}

// write mail body

$mail_body = 'Hey. You haven\'t voted on where to eat lunch yet. Do it: http://www.whatever.com/lunch/'."\n\n".' - Cylebot';

require_once('includes/mail_config.php');
$mailer->send(implode(',', $mail_people), array('From' => $mail_from, 'Subject' => 'REMINDER: VOTE ON LUNCH.'), $mail_body);

die('sent');

?>
