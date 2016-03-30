<?php

//TODO: make conditional for page
add_action ('wp_enqueue_scripts', 'sa_watch_load_style');
function sa_watch_load_style() {
		wp_register_style( 'sa-watch', plugins_url( 'sa_watch/style.css' ) );
	wp_enqueue_style( 'sa-watch' );
}

add_shortcode('sa_watch_senate_content', 'sa_watch_senate_shortcode');

function sa_watch_senate_shortcode () {
	//TODO: let you show pictures, bio, stuff like that
	global $wpdb;
	$rep_table = $wpdb->prefix . "sa_watch_representative"; //TODO: Refactor this with sa_watch.php
	//Load and output the President
	echo "<div class=sawatch-senate-table>";
	$president = $wpdb->get_results("SELECT * FROM " . $rep_table . " WHERE position='pres';", OBJECT);

	echo "<table>";
	sa_watch_output_senator($president[0]);
	echo "</table>";

	$vp = $wpdb->get_results("SELECT * FROM " . $rep_table . " WHERE position='vp';", OBJECT);


	echo "<table>";
	sa_watch_output_senator($vp[0]);
	echo "</table>";

	//Get list of class years
	$years = $wpdb->get_results("SELECT DISTINCT classyear FROM " . $rep_table . " ORDER BY classyear;", OBJECT);
	$numyears = count($years);
	for ($i = 0; $i < $numyears; $i++) {
		$senators = $wpdb->get_results("SELECT * FROM " . $rep_table . " WHERE position='senator' AND classyear=" . $years[$i]->classyear. ";", OBJECT);
		$numsenators = count($senators);
		if ($numsenators != 0) {
			echo "<h4>Class of " . $years[$i]->classyear . " Senators</h4>";
		}
		echo "<table>";
		for ($j = 0; $j < $numsenators; $j++) {
			sa_watch_output_senator($senators[$j]);
		}
		echo "</table>";
	}
	echo "</div>";
}

function sa_watch_output_senator($senator) {

	echo "<tr><td><img src=" . $senator->picture_url . "></td><td><p><b>";
	if ($senator->position == 'pres') {
		echo "President: ";
	} else if ($senator->position == 'vp') {
		echo "Vice President: ";
	}
	echo stripslashes($senator->firstname) . " " . stripslashes($senator->lastname) . "</b><br>";
	if (!empty($senator->bio)) {
		echo stripslashes($senator->bio) . "<br>";
	}
	echo "Email: <a href=\"mailto:" . $senator->student_id . "@email.wm.edu\">" . $senator->student_id . "@email.wm.edu" . "</a></p></td></tr>";
}




