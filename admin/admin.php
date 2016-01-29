<?php

global $capability;

$capability = 'edit_pages';

add_action( 'admin_menu', 'sawatch_admin_creation' );

//Registers our admin pane
function sawatch_admin_creation() {
	global $capability;
	add_submenu_page( 'tools.php', 'SA Data Entry', 'SA Data Entry', $capability, 'sa_data_entry', 'sa_data_entry');
	wp_register_style( 'sa-watch', plugins_url( 'sa_watch/style.css' ) );
	wp_enqueue_style( 'sa-watch' );
	wp_enqueue_script( 'sa-watch-admin', plugin_dir_url( __FILE__ ) . 'admin_pane.js' );
}

function sa_process_rep() {
	global $wpdb;

	//Validate data
	if (empty($_POST["firstname"]) || empty($_POST["lastname"]) || 
		empty($_POST["classyear"])) {

		echo "ERROR: Missing required name or classyear data";
		return;
	} else if (intval($_POST["classyear"]) < 2010 || 
		intval($_POST["classyear"]) > (intval(date("Y")) + 10)) {

		echo "ERROR: Class year is not an integer or is an outlandish value";
		return;
	} else if (!empty($_POST["picture_url"]) && filter_var($_POST["picture_url"], FILTER_VALIDATE_URL)) {

		echo "The picture is not a valid URL";
		return;
	}

	$rep_table = $wpdb->prefix . "sa_watch_representative"; //TODO: Refactor this with sa_watch.php
	//Search the database for this userID
	$results = $wpdb->get_results( "SELECT rep_id FROM " . $rep_table . " WHERE firstname='" . $_POST["firstname"] . 
									"' AND lastname='" . $_POST["lastname"] ."';", OBJECT);
	//If this representative does not exist, add them. If they do, output an error
	if (count($results) <= 1) {
		//TODO: check for duplicate president and vice president
			$wpdb->insert(
		$rep_table,
		array(
			'firstname' => $_POST["firstname"],
			'lastname' => $_POST["lastname"],
			'classyear' => $_POST["classyear"],
			'position' => $_POST["position"],
			'bio' => $_POST["bio"],
			'picture_url' => $_POST["picture_url"]
		)
	);
	} else {
		//TODO: Make this more elegant
		echo "Error: This name already exists in the database";
	}
}

function sa_process_bill() {
	//I am a stub
}

function sa_process_vote() {
	//I am a stub
}

function sa_process_cat() {
	//I am a stub
}

function sa_process_val() {
	//I am a stub
}



function sa_process_form() {
	//Check if this is a form submission
	if (!isset($_POST["type"])) {
		return;
	}
	switch($_POST["type"]) {
		case "rep":
			sa_process_rep();
			break;
		case "bill":
			sa_process_bill();
			break;
		case "vote":
			sa_process_vote();
			break;
		case "cat":
			sa_process_cat();
			break;
		case "val":
			sa_process_val();
			break;
		default:
			//This should never happen
			echo "ERROR: Illegal submission type";
	}
}


//Creates the HTML Admin Pane
function sa_data_entry() {
	sa_process_form();
?>
	<div class="sawatch-admin-pane">
		<h1> SA Data Entry </h1>
		<nav class="main-menu">
			<ul>
				<li id="rep" onclick="updatePane(this.id)">Representative</li>
				<li id="bill" onclick="updatePane(this.id)">Bill</li>
				<li id="vote" onclick="updatePane(this.id)">Votes</li>
				<li id="cat" onclick="updatePane(this.id)">Budget Categories</li>
				<li id="val" onclick="updatePane(this.id)">Budget Values</li>
			</ul>
		</nav>
		<div id="rep-data">
			<p>Representative Input</p>
			<form action="" method="post">
				First name:<br>
				<input type="text" name="firstname"><br>
				Last name:<br>
				<input type="text" name="lastname"><br>
				Class Year:<br>
				<input type="text" name="classyear"><br>
				Position:<br>
				<input type="radio" name="position" value="pres" checked>President<br>
				<input type="radio" name="position" value="vp">Vice President<br>
				<input type="radio" name="position" value="senator">Senator<br>
				Bio:<br>
				<input type="text" name="bio"><br>
				Picture URL:<br>
				<input type="text" name="picture_url"><br>
				<input type="submit" value="Submit">
				<input type="hidden" name="type" value="rep"> <!-- Used when processing form -->
			</form>
		</div>
		<div id="bill-data" style="display:none;">
			<p>Bill Input</p>
		</div>
		<div id="vote-data" style="display:none;">
			<p>Votes Input</p>
		</div>
		<div id="cat-data" style="display:none;">
			<p>Budget Category Input</p>
		</div>
		<div id="val-data" style="display:none;">
			<p>Budget Value Input</p>
		</div>
	</div>
<?php
}