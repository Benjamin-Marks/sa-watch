<?php

//TODO: make conditional for page
add_action ('wp_enqueue_scripts', 'sa_watch_load_style');
function sa_watch_load_style() {
	wp_register_style( 'sa-watch', plugins_url( 'sa_watch/style.css' ) );
	wp_enqueue_style( 'sa-watch' );
	wp_enqueue_script( 'sa-watch-showVotes', plugin_dir_url( __FILE__ ) . 'show_votes.js' );
}

add_shortcode('sa_watch_senate_content', 'sa_watch_senate_shortcode');

function sa_watch_senate_shortcode () {
	global $wpdb, $rep_table;
	//Check if we're showing an individual senator's bio and redirect
	if (isset($_GET["rep"])) {
		sa_watch_senator_bio();
		return;
	}

	//Load and output the President
	echo "<div class=sawatch-senate-table>";
	$president = $wpdb->get_results("SELECT * FROM $rep_table WHERE position='pres';", OBJECT);
	//Handle case where data hasn't been entered
	if (count($president) != 0) {
		echo "<table>";
		sa_watch_output_senator($president[0]);
		echo "</table>";
	}

	$vp = $wpdb->get_results("SELECT * FROM $rep_table WHERE position='vp';", OBJECT);
	//Handle case where data hasn't been entered
	if (count($vp) != 0) {
		echo "<table>";
		sa_watch_output_senator($vp[0]);
		echo "</table>";
	}

	//Get list of class years
	$years = $wpdb->get_results("SELECT DISTINCT classyear FROM $rep_table ORDER BY classyear;", OBJECT);
	$numyears = count($years);
	for ($i = 0; $i < $numyears; $i++) {
		//Output president
		$classpres = $wpdb->get_results("SELECT * FROM $rep_table WHERE position='classpres' AND classyear=" . $years[$i]->classyear . ";", OBJECT);
		$senators = $wpdb->get_results("SELECT * FROM $rep_table WHERE position='senator' AND classyear=" . $years[$i]->classyear. ";", OBJECT);
		$numsenators = count($senators);
		if ($numsenators != 0) {
			echo "<h4>Class of " . $years[$i]->classyear . "</h4>";
		}
		echo "<table>";
		if (count($classpres) != 0) {
			sa_watch_output_senator($classpres[0]);
		}
		for ($j = 0; $j < $numsenators; $j++) {
			sa_watch_output_senator($senators[$j]);
		}
		echo "</table>";
	}
	echo "</div>";
}

function sa_watch_output_senator($senator) {
	global $wpdb, $rep_table, $bill_table, $vote_table;

	echo "<tr onclick='showVotes(rep_" . $senator->rep_id . ")'><td><img src=" . $senator->picture_url . "></td><td><p><b>";
	if ($senator->position == 'pres') {
		echo "President: ";
	} else if ($senator->position == 'vp') {
		echo "Vice President: ";
	} else if ($senator->position == 'classpres') {
		echo "Class President: ";
	}
	echo stripslashes($senator->firstname) . " " . stripslashes($senator->lastname) . "</b><br>";
	if (!empty($senator->bio)) {
		echo stripslashes($senator->bio) . "<br>";
	}
	echo "Email: <a href=\"mailto:" . $senator->student_id . "@email.wm.edu\">" . $senator->student_id . "@email.wm.edu" . "</a></p></td></tr>";

	//Output senator's voting history
	echo "<tr id=rep_" . $senator->rep_id . " class='votes'><td>";
	//Get list of bills they've sponsored
	$sponsor = $wpdb->get_results("SELECT * FROM $vote_table WHERE rep_id=" . $senator->rep_id . " AND vote_type='sponsor';", OBJECT);
	$numSponsor = count($sponsor);
	if ($numSponsor != 0) {
		echo "<b>Sponsored Bills:</b> <br>";
		for ($i = 0; $i < $numSponsor; $i++) {
			//TODO: like, use a join. cmon now.
			$bill = $wpdb->get_results("SELECT * FROM $bill_table WHERE bill_id=" . $sponsor[$i]->bill_id . ";", OBJECT);
			echo $bill[0]->name . "<br>";
		}
	} else {
		echo "This representative has not sponsored any bills";
	}
	//Get list of their votes
	echo "</td><td>";
	$votes = $wpdb->get_results("SELECT * FROM $vote_table WHERE rep_id=" . $senator->rep_id . " AND vote_type<>'sponsor';", OBJECT);
	$numVotes = count($votes);
	if ($numVotes != 0) {
		echo "<b>Voting Record:</b> <br>";
		for ($i = 0; $i < $numVotes; $i++) {
			//TODO: like, use a join. cmon now.
			$bill = $wpdb->get_results("SELECT * FROM $bill_table WHERE bill_id=" . $votes[$i]->bill_id . ";", OBJECT);
			echo $bill[0]->name . ": " . $votes[$i]->vote_type . "<br>";
		}
	} else {
		echo "This representative has not voted on any bills";
	}
	echo "</td></tr>";


}