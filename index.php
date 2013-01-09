<?php

//die('The Lunch Utility will be unavailable until the Eating Outside Season continues next year.');

require_once('login_check.php');
require_once('includes/dbconn.php');

$here_zipcode = '02116'; // where are we? this will be used to get weather data

/*

	LUNCH UTILITY!

		- vote for where to eat lunch!
			- anonymous voting
			- include "don't care/not here" option
			- if voted, display current vote data, CANNOT CHANGE VOTE
		- include latest weather
			- if rain, suggest inside
			- if low temp, suggest inside
			- if beautiful out, suggest outside
		- send email to members at 11:00AM to vote

		v2
			- WHERE people are getting food

 */
 
$all_results = array();
$get_vote_results = $mysqli->query('SELECT COUNT(id) AS votecount, oid FROM votes WHERE lunchdate=CURDATE() GROUP BY oid ORDER BY votecount DESC');
if ($get_vote_results->num_rows > 0) {
	while ($vote_result = $get_vote_results->fetch_assoc()) {
		$all_results[] = $vote_result;
	}
}



?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>  
<title>LUNCH UTILITY! YAY!</title>
<link rel="stylesheet" href="lunch.css" type="text/css" />
</head>
<body>

	<div>
		<h1>LUNCH</h1>
		<p>Vote on lunch and shit, <?php echo $user_cookie['username']; ?>. Polls open at 9am and close at 11:45am. Btw, you cannot change your vote!</p>
	</div>
	
	<div>
		<?php
		// load weather
		$weather = @simplexml_load_file('http://www.google.com/ig/api?weather='.$here_zipcode);
		//echo '<pre>'.print_r($weather, true).'</pre>';
		if (isset($weather->weather)) {
			$weather_now = $weather->weather->current_conditions->condition['data'];
			$weather_temp = $weather->weather->current_conditions->temp_f['data'];
			$weather_high = $weather->weather->forecast_conditions[0]->high['data'];
			$weather_today = $weather->weather->forecast_conditions[0]->condition['data'];
			?>
			<p>Current weather: <?php echo $weather_now; ?>, <?php echo $weather_temp; ?> degrees.</p>
			<p>High of <?php echo $weather_high; ?> degrees expected.</p>
			<?php
			if (preg_match('/(rain|thunderstorm|snow|showers)/i', $weather_today)) {
				echo '<p class="weather-recommend">It\'s supposed to rain, eat inside.</p>';
			} else if ($weather_temp >= 68 && $weather_temp <= 82 && preg_match('/(clear|sunny|overcast|cloudy)/i', $weather_today)) {
				echo '<p class="weather-recommend">I think you should eat outside!</p>';
			} else if ($weather_temp > 82) {
				echo '<p class="weather-recommend">It\'s too damn hot out.</p>';
			} else if ($weather_temp < 65) {
				echo '<p class="weather-recommend">It\'s too damn cold out.</p>';
			}
			
		} else {
			echo '<p>Cannot get weather info, sorry. Try refreshing.</p>';
		}
		?>
	</div>
	
	<?php
	$current_hour = date('G') * 1;
	// between 9am and 2pm, show the voting area
	if ($current_hour >= 9 && $current_hour <= 14) {
		
		$results_only = false;
		
		if (date('Gi') * 1 >= 1145) {
			// it's after 11:45am, just show the results, no voting!
			$results_only = true;
			
			// show big result
			if (count($all_results) > 0) {
				if ($all_results[0]['votecount'] == $all_results[1]['votecount']) {
					echo '<div id="final-result"><p>Final result: <b>A tie, lol.</b></p></div>';
				} else {
					$top_result = $all_results[0];
					$get_vote_option = $mysqli->query('SELECT optiontxt FROM vote_options WHERE id='.$top_result['oid']);
					$vote_option = $get_vote_option->fetch_assoc();
					echo '<div id="final-result"><p>Final result: <b>'.$vote_option['optiontxt'].'</b></p></div>';
				}
			} else {
				echo '<div id="final-result"><p>Final result: Nobody voted, so who knows.</p></div>';
			}
		}
		
		// check to see if the current user voted
		$get_voting_status = $mysqli->query('SELECT id FROM votes WHERE lunchdate=CURDATE() AND uid='.$user_cookie['user_id']);
		if ($get_voting_status->num_rows > 0) {
			// if they did, show the results so far
			$results_only = true;
		}
		
		if ($results_only) {
	?>
	<div>
		<p>Okay, the results so far...</p>
		<?php
		$get_total_votes = $mysqli->query('SELECT id FROM votes WHERE lunchdate=CURDATE()');
		if ($get_total_votes->num_rows > 0) {
			$total_votes = $get_total_votes->num_rows;
			echo '<table>'."\n";
			echo '<tr><th>Option</th><th>Votes</th></tr>'."\n";
			//$get_vote_results = $mysqli->query('SELECT COUNT(id) AS votecount, oid FROM votes WHERE lunchdate=CURDATE() GROUP BY oid ORDER BY votecount DESC');
			//while ($vote_result = $get_vote_results->fetch_assoc()) {
			foreach ($all_results as $vote_result) {
				$get_vote_option = $mysqli->query('SELECT optiontxt FROM vote_options WHERE id='.$vote_result['oid']);
				$vote_option = $get_vote_option->fetch_assoc();
				echo '<tr><td>'.$vote_option['optiontxt'].'</td><td style="width:100px;"><div style="background-color:#999;width:'.(($vote_result['votecount']/$total_votes) * 100).'%">&nbsp;</div></td><td>'.$vote_result['votecount'].'</td></tr>'."\n";
			}
			echo '</table>'."\n";
		} else {
			echo '<p>Ooops, nobody has voted.</p>';
		}
		?>
	</div>
	<?php
	
		} else {
			// show the voting options
		
	?>
	<div>
		<p>So, we should eat...</p>
		<form action="vote.php" method="post">
			<?php
			$get_vote_options = $mysqli->query('SELECT * FROM vote_options WHERE enabled=1 ORDER BY displayorder ASC');
			while ($vote_row = $get_vote_options->fetch_assoc()) {
				echo '<div><input type="radio" name="vote" value="'.$vote_row['id'].'" /> '.$vote_row['optiontxt'].'</div>';
			}
			?>
			<div><input type="submit" value="VOTE." /></div>
		</form>
	</div>
	<?php
		} // end if they voted or not
	} else {
		echo '<div><p>No voting yet!</p></div>';
	} // end if time for voting stuff
	?>
	
	
	<div id="footer">
		<p>Built by Cyle. Version 1.0</p>
	</div>

</body>
</html>
