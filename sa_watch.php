<?php
/**
 * Plugin Name: Know Your Senator
 * Plugin URI: http://github.com/Benjamin-Marks/sa_watch
 * Description: This plugin provides summary information on the W&M Student Assembly Budget and members
 * Version: 1.0.0
 * Author: Benjamin Marks
 * Author URI: http://github.com/Benjamin-Marks
 * License: GPL2
 */

global $sa_watch_db_version;
$sa_watch_db_version = "1.0";

register_activation_hook( __FILE__, 'sa_watch_install' );
register_deactivation_hook( __FILE__, 'sa_watch_uninstall' ); //TODO: Remove this in production

if (!function_exists('add_action')) {
	echo "Do not call this plugin directly";
	exit;
}

function sa_watch_install() {
	global $wpdb;
	global $sa_watch_db_version;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	add_option("sa_watch_db_version", $sa_watch_db_version);

	$prefix = $wpdb->prefix . "sa_watch_";
	$charset_collate = $wpdb->get_charset_collate();

	//Install all our tables
	$sql = "CREATE TABLE ".$prefix."representative (
	  rep_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  firstname tinytext NOT NULL,
	  lastname tinytext NOT NULL,
	  classyear year NOT NULL,
	  position enum ('pres', 'vp', 'senator')  NOT NULL,
	  bio text DEFAULT '' NOT NULL,
	  picture_url varchar(55) DEFAULT '' NOT NULL,
	  PRIMARY KEY rep_id (rep_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".$prefix."bill (
	  bill_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  vote_date date NOT NULL,
	  description text DEFAULT '' NOT NULL,
	  result enum ('passed', 'failed', 'tabled')  NOT NULL,
	  PRIMARY KEY bill_id (bill_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".$prefix."vote (
	  vote_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  rep_id mediumint(9) NOT NULL,
	  bill_id mediumint (9) NOT NULL,
	  vote_type enum ('aye', 'nay', 'abstain'),
	  FOREIGN KEY (rep_id) REFERENCES ".$prefix."representative(rep_id),
	  FOREIGN KEY (bill_id) REFERENCES ".$prefix."bill(bill_id),
	  PRIMARY KEY vote_id (vote_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".$prefix."budget_item (
	  budget_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  description text DEFAULT '' NOT NULL,
	  PRIMARY KEY budget_id (budget_id)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE ".$prefix."budget_value (
	  budget_value_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  budget_id mediumint(9) NOT NULL,
	  date date NOT NULL,
	  amount mediumint(9) NOT NULL,
	  FOREIGN KEY (budget_id) REFERENCES ".$prefix."budget_item(budget_id),
	  PRIMARY KEY budget_value_id (budget_value_id)
	) $charset_collate;";
	dbDelta( $sql );
}

function sa_watch_uninstall() {
	//Remove options
	delete_option( 'sa_watch_db_version' );

	//drop tables
	global $wpdb;
	$prefix = $wpdb->prefix . "sa_watch_";
	$wpdb->query( "DROP TABLE IF EXISTS ".$prefix."budget_value" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$prefix."vote" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$prefix."representative" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$prefix."bill" );	
	$wpdb->query( "DROP TABLE IF EXISTS ".$prefix."budget_item" );
}

