<?php

// send a reminder to vote!

require_once('includes/dbconn.php');

// get people to send a reminder to
// people who have not voted today!
$mail_people = array();
$get_mail_people = $mysqli->query('SELECT ecnet FROM users');
while ($mail_row = $get_mail_people->fetch_assoc()) {
	$mail_people[] = strtolower(trim($mail_row['ecnet'])).'@whatever.com';
}

// get winning vote
$all_results = array();
$get_top_result = $mysqli->query('SELECT COUNT(id) AS votecount, oid FROM votes WHERE lunchdate=CURDATE() GROUP BY oid ORDER BY votecount DESC');
if ($get_top_result->num_rows > 0) {
	while ($result_row = $get_top_result->fetch_assoc()) {
		$all_results[] = $result_row;
	}
	if ($all_results[0]['votecount'] == $all_results[1]['votecount']) {
		$mail_body = 'So, there was a tie. Check out the website to see the results: http://www.whatever.com/lunch/'."\n\n".' - Cylebot';
	} else {
		$top_result = $all_results[0];
		$get_vote_option = $mysqli->query('SELECT optiontxt FROM vote_options WHERE id='.$top_result['oid']);
		$vote_option = $get_vote_option->fetch_assoc();
		$mail_body = 'So, lunch is going to be: '.$vote_option['optiontxt']."\n\n".' - Cylebot';
	}
} else {
	$mail_body = 'So, lunch is undecided, because nobody voted. Way to go, guys.'."\n\n".' - Cylebot';
}

require_once('includes/mail_config.php');
$mailer->send(implode(',', $mail_people), array('From' => $mail_from, 'Subject' => 'LUNCH IS AT...'), $mail_body);

die('sent');

?>
