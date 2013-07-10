<?php

//die('The Lunch Utility will be unavailable until the Eating Outside Season continues next year.');

require_once('login_check.php');
require_once('includes/dbconn.php');

$here_zipcode = '02116'; // where are we? this will be used to get weather data
$weather_api_key = 'your-api-key-here'; // our API key for worldweatheronline.com

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

$possible_bar_colors = array('#999', 'red', 'blue', 'green', 'black', 'orange', '#43305E', '#5C2E10', '#546F2E', '#C01056', '#F05E7F', '#FF6050');

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
		$weather_url = 'http://api.worldweatheronline.com/free/v1/weather.ashx?q='.$here_zipcode.'&format=json&num_of_days=1&key='.$weather_api_key;
		$weather_http_options = array('http' => array('timeout' => 1));
		$weather_ctx = stream_context_create($weather_http_options);
		$weather_content = @file_get_contents($weather_url, false, $weather_ctx);
		if ($weather_content == false || trim($weather_content) == '') {
			echo '<p>Could not retrieve weather data, sorry. Go to <a href="http://weatherspark.com/#!dashboard;q='.$here_zipcode.'" target="_blank">Weatherspark</a> and check it out.</p>';
		} else {
			$weather_data = json_decode($weather_content, true);
			if (is_array($weather_data)) {
				//echo '<pre>'.print_r($weather_data, true).'</pre>';
				$weather_temp = $weather_data['data']['weather'][0]['tempMaxF'] * 1;
				$weather_today = strtolower($weather_data['data']['weather'][0]['weatherDesc'][0]['value']);
				echo '<p>Current info: '.$weather_data['data']['current_condition'][0]['temp_F'].'&deg;, '.$weather_data['data']['current_condition'][0]['weatherDesc'][0]['value'].'</p>';
				echo '<p>Today\'s info: High of '.$weather_data['data']['weather'][0]['tempMaxF'].'&deg;, '.$weather_data['data']['weather'][0]['weatherDesc'][0]['value'].'</p>';
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
				echo '<p>Could not parse weather data, sorry. Go to <a href="http://weatherspark.com/#!dashboard;q='.$here_zipcode.'" target="_blank">Weatherspark</a> and check it out.</p>';
			}
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
				if (isset($all_results[0]) && isset($all_results[1]) && $all_results[0]['votecount'] == $all_results[1]['votecount']) {
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
				echo '<tr><td>'.$vote_option['optiontxt'].'</td><td style="width:100px;"><div style="background-color:'.$possible_bar_colors[array_rand($possible_bar_colors)].';width:'.(($vote_result['votecount']/$total_votes) * 100).'%">&nbsp;</div></td><td>'.$vote_result['votecount'].'</td></tr>'."\n";
			}
			echo '</table>'."\n";
		} else {
			echo '<p>Ooops, nobody has voted.</p>';
		}
		?>
		<p>And where should you get food? Do you even have to get food? Spin the wheel.</p>
		<form action="getting_food_at.php" method="post">
		<p id="random-destination-form"><input type="button" value="Pick Random Eatery" id="get-random-destination-btn" /> <select name="eatery_id" id="random-destination-list"><?php
		$get_eateries = $mysqli->query('SELECT * FROM destinations ORDER BY name ASC');
		while ($eatery = $get_eateries->fetch_assoc()) {
			echo '<option value="'.$eatery['id'].'">'.$eatery['name'].'</option>'."\n";
		}
		?></select> <input type="submit" value="I'm getting food from here!" id="random-destination-choice" /></p>
		</form>
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
	
	<div>
	<p><b>Comments</b> (Latest first, cleared every day.)</p>
	<div id="comments-list">
	<?php
	
	$get_todays_comments = $mysqli->query('SELECT commenting.*, users.username FROM commenting LEFT JOIN users ON users.id=commenting.uid WHERE thedate=CURDATE() ORDER BY tsc DESC');
	if ($get_todays_comments->num_rows > 0) {
		while ($comment = $get_todays_comments->fetch_assoc()) {
			?>
			<div class="comment">
			<p><span class="comment-timestamp"><?php echo date('h:iA', $comment['tsc']); ?></span> <b><?php echo $comment['username']; ?></b>: <?php echo $comment['thecomment']; ?></p>
			</div>
			<?php
		}
	} else {
		?>
		<p>There are no comments to display today.</p>
		<?php
	}
	?>
	</div>
	<div>
	<form action="add_comment.php" method="post" id="new-comment-form">
	<p>Add a comment:</p>
	<textarea name="c"></textarea>
	<p><input type="submit" value="add comment" /></p>
	</form>
	</div>
	</div>
	
	<div id="footer">
		<p>Built by Cyle. Version 2.1</p>
	</div>
	
<script type="text/javascript">
document.getElementById('get-random-destination-btn').addEventListener('click', getRandomDestination);
function getRandomDestination() {
	var eateries = document.getElementById('random-destination-list');
	var number_of_options = eateries.options.length;
	var random_index = Math.floor(Math.random()*number_of_options);
	eateries.selectedIndex = random_index;
}
</script>
</body>
</html>
