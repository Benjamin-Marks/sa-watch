<?php
/**
 * Plugin Name: Know Your Senators
 * Plugin URI: http://github.com/Benjamin-Marks/sa_watch
 * Description: This plugin provides summary information on the W&M Student Assembly Budget and members
 * Version: 1.0.0
 * Author: Benjamin Marks
 * Author URI: http://github.com/Benjamin-Marks
 * License: GPL2
 */

//This file handles install/uninstall capabilities

$sa_watch_db_version = "1.0";

register_activation_hook(__FILE__, 'sa_watch_install');
register_deactivation_hook(__FILE__, 'sa_watch_uninstall'); //FOR TESTING PURPOSES ONLY
//TODO: add delete data option upon Uninstalling

//Include our admin page scripts
require_once(plugin_dir_path(__FILE__) . 'admin/admin.php');

//Include our views scripts and shortcodes
require_once(plugin_dir_path(__FILE__) . 'views/budget.php');
require_once(plugin_dir_path(__FILE__) . 'views/senate.php');

if (!function_exists('add_action')) {
	echo "Do not call this plugin directly";
	exit;
}

//Table names
global $wpdb;
$prefix = $wpdb->prefix . "sa_watch_";
$GLOBALS['rep_table'] = $rep_table = $wpdb->prefix . "sa_watch_representative";
$GLOBALS['bill_table'] = $bill_table = $wpdb->prefix . "sa_watch_bill";
$GLOBALS['vote_table'] = $vote_table = $wpdb->prefix . "sa_watch_vote_id";
$GLOBALS['cat_table'] = $cat_table = $wpdb->prefix . "sa_watch_budget_item";
$GLOBALS['val_table'] = $val_table = $wpdb->prefix . "sa_watch_budget_value";


function sa_watch_install() {
	global $wpdb, $sa_watch_db_version, $rep_table, $bill_table, $vote_table, $cat_table, $val_table;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	add_option("sa_watch_db_version", $sa_watch_db_version);


	$charset_collate = $wpdb->get_charset_collate();


	//Install all our tables
	$sql = "CREATE TABLE IF NOT EXISTS $rep_table (
	  rep_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  firstname tinytext NOT NULL,
	  lastname tinytext NOT NULL,
	  student_id tinytext NOT NULL,
	  classyear year NOT NULL,
	  position enum ('pres', 'vp', 'senator', 'classpres')  NOT NULL,
	  bio text DEFAULT '' NOT NULL,
	  picture_url varchar(255) DEFAULT '' NOT NULL,
	  PRIMARY KEY rep_id (rep_id)
	) $charset_collate;";
	dbDelta($sql);

	$sql = "CREATE TABLE IF NOT EXISTS $bill_table (
	  bill_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  vote_date date,
	  description text DEFAULT '' NOT NULL,
	  result enum ('passed', 'withdrawn', 'failed', 'tabled')  NOT NULL,
	  PRIMARY KEY bill_id (bill_id)
	) $charset_collate;";
	dbDelta($sql);

	$sql = "CREATE TABLE IF NOT EXISTS $vote_table (
	  vote_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  rep_id mediumint(9) NOT NULL,
	  bill_id mediumint (9) NOT NULL,
	  vote_type enum ('sponsor', 'aye', 'nay', 'abstain'),
	  FOREIGN KEY (rep_id) REFERENCES {$rep_table}(rep_id),
	  FOREIGN KEY (bill_id) REFERENCES {$bill_table}(bill_id),
	  PRIMARY KEY vote_id (vote_id)
	) $charset_collate;";
	dbDelta($sql);

	$sql = "CREATE TABLE IF NOT EXISTS $cat_table (
	  budget_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  name tinytext NOT NULL,
	  description text DEFAULT '' NOT NULL,
	  PRIMARY KEY budget_id (budget_id)
	) $charset_collate;";
	dbDelta($sql);

	$sql = "CREATE TABLE IF NOT EXISTS $val_table (
	  budget_value_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  budget_id mediumint(9) NOT NULL,
	  date date NOT NULL,
	  amount mediumint(9) NOT NULL,
	  FOREIGN KEY (budget_id) REFERENCES {$cat_table}(budget_id),
	  PRIMARY KEY budget_value_id (budget_value_id)
	) $charset_collate;";
	dbDelta($sql);

	//TODO: offload code repition into helper function
	//Create our Budget Graph Page
	$graph_title = 'Student Assembly Budget Tracker';
	$graph_slug = 'sa-budget-graph';

	// the menu entry
	delete_option("sa_budget_title");
	add_option("sa_budget_title", $graph_title, '', 'yes');
	// the slug
	delete_option("sa_budget_slug");
	add_option("sa_budget_slug", $graph_slug, '', 'yes');
	// the id
	delete_option("sa_budget_id");
	add_option("sa_budget_id", '0', '', 'yes');

	$graph_page = get_page_by_title($graph_title);

	if (!$graph_page) {
		// Create post object
		$_p = array();
		$_p['post_title'] = $graph_title;
		$_p['post_content'] = "[sa_watch_graph_content]";
		$_p['post_status'] = 'publish';
		$_p['post_type'] = 'page';
		$_p['comment_status'] = 'closed';
		$_p['ping_status'] = 'closed';
		$_p['post_category'] = array(3592); //No Sidebar FIXME TODO WARNING: THIS ID IS HARDCODED THIS SHOULD BE CHANGED

		// Insert the post into the database
		$graph_page_id = wp_insert_post($_p);
	} else {
		// the plugin may have been previously active and the page may just be trashed
		$graph_page_id = $graph_page->ID;
		$graph_page->post_status = 'publish';
		$graph_page_id = wp_update_post($graph_page);
	}

	delete_option('sa_budget_id');
	add_option('sa_budget_id', $graph_page_id);


	//Create our Senate Page
	$senate_title = 'Student Assembly Senators';
	$senate_slug = 'sa-budget-graph';

	// the menu entry
	delete_option("sa_senate_title");
	add_option("sa_senate_title", $senate_title, '', 'yes');
	// the slug
	delete_option("sa_senate_slug");
	add_option("sa_senate_slug", $senate_slug, '', 'yes');
	// the id
	delete_option("sa_senate_id");
	add_option("sa_senate_id", '0', '', 'yes');

	$senate_page = get_page_by_title($senate_title);

	if (!$senate_page) {
		// Create post object
		$_p = array();
		$_p['post_title'] = $senate_title;
		$_p['post_content'] = "[sa_watch_senate_content]";
		$_p['post_status'] = 'publish';
		$_p['post_type'] = 'page';
		$_p['comment_status'] = 'closed';
		$_p['ping_status'] = 'closed';
		$_p['post_category'] = array(3592); //No Sidebar FIXME TODO  WARNING: THIS ID IS HARDCODED THIS SHOULD BE CHANGED

		// Insert the post into the database
		$senate_page_id = wp_insert_post($_p);
	} else {
		// the plugin may have been previously active and the page may just be trashed
		$senate_page_id = $graph_page->ID;
		$senate_page->post_status = 'publish';
		$senate_page_id = wp_update_post($senate_page);
	}

	delete_option('sa_senate_id');
	add_option('sa_senate_id', $senate_page_id);
}


//For Debug Purposes only, don't let some user remove our precious data
function sa_watch_uninstall() {
	global $wpdb, $rep_table, $bill_table, $vote_table, $cat_table, $val_table;

	//Remove options
	delete_option('sa_watch_db_version'); 

	//Delete pages
	delete_option("sa_senate_title");
	delete_option("sa_senate_slug");
	delete_option("sa_senate_id");
	delete_option("sa_budget_title");
	delete_option("sa_budget_slug");
	delete_option('sa_budget_id');

	//drop tables
	$wpdb->query("DROP TABLE IF EXISTS $vote_table");
	$wpdb->query("DROP TABLE IF EXISTS $rep_table");
	$wpdb->query("DROP TABLE IF EXISTS $bill_table");
	$wpdb->query("DROP TABLE IF EXISTS $val_table");
	$wpdb->query("DROP TABLE IF EXISTS $cat_table");
}
