<?php

//Globals
$capability = 'edit_pages';
$rep_table = $wpdb->prefix . "sa_watch_representative"; //TODO: Refactor these with sa_watch.php
$bill_table = $wpdb->prefix . "sa_watch_bill";
$vote_table = $wpdb->prefix . "sa_watch_vote_id";
$cat_table = $wpdb->prefix . "sa_watch_budget_item";
$val_table = $wpdb->prefix . "sa_watch_budget_value";



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
	global $rep_table;
	if (!empty($_POST["add"])) {
		//Validate data
		if (empty($_POST["firstname"]) || empty($_POST["lastname"]) || 
			empty($_POST["classyear"])) {

			echo "ERROR: Missing required name or classyear data";
			return;
		} else if (intval($_POST["classyear"]) < 2010 || 
			intval($_POST["classyear"]) > (intval(date("Y")) + 10)) {

			echo "ERROR: Class year is an outlandish value";
			return;
		} else if (!empty($_POST["picture_url"]) && filter_var($_POST["picture_url"], FILTER_VALIDATE_URL)) {

			echo "The picture is not a valid URL";
			return;
		}
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
			echo "Error: This name already exists in the database";
		}
	} else {
		//Remove rep - see https://codex.wordpress.org/Class_Reference/wpdb#DELETE_Rows for usage
		$wpdb->delete($rep_table, array('rep_id' => $_POST["rep_id"]));
	}
}

function sa_process_bill() {
	global $bill_table;
	global $wpdb;
    //If this bill does not exist, add it. If it do, output an error
	//Validate data
	if (empty($_POST["name"]) || empty($_POST["vote_date"])) {
        echo "ERROR: Missing required Bill Name or Vote Date";
		return;
	}
	$bill_table = $wpdb->prefix . "sa_watch_bill"; //TODO: Refactor this with sa_watch.php
    $results = $wpdb->get_results( "SELECT bill_id FROM " . $bill_table . " WHERE name='" . $_POST["name"] . 
									"' AND vote_date='" . $_POST["vote_date"] ."';", OBJECT);
    //If this bill does not exist, add them. If they do, output an error
    if (count($results) <= 1) {
		//TODO: check for duplicate bill
            $wpdb->insert(
		$bill_table,
        array(
			'name' => $_POST["name"],
			'vote_date' => $_POST["vote_date"],
			'description' => $_POST["description"],
            'result' => $_POST["result"]
            )
	   );
	   } else {
		  echo "Error: This Bill already exists in the database";
	} 
}
function sa_process_vote() {
	global $wpdb;
	global $vote_table;
	//Validate data
	if (empty($_POST["rep"]) || empty($_POST["bill"])) {
        echo "ERROR: Missing required name or Bill name";
		return;
        }
    $vote_table = $wpdb->prefix . "sa_watch_vote_id"; //TODO: Refactor this with sa_watch.php
    //If this vote does not exist, add it. If it does, output an error
    $results = $wpdb->get_results( "SELECT vote_id FROM " . $vote_table . " WHERE rep_id='" . $_POST["rep"] . 
									"' AND bill_id='" . $_POST["bill"] ."';", OBJECT);
    if (count($results) <= 1) {
		//TODO: check for duplicate vote
			$wpdb->insert(
		$vote_table,
        array(
			'rep_id' => $_POST["rep"],
			'bill_id' => $_POST["bill"],
            'vote_type' => $_POST["vote"]
		)
	);
	} else {
		echo "Error: You have already voted";
	}
}

function sa_process_cat() {
	global $wpdb;
	global $cat_table;
		//Validate data
	if (empty($_POST["name"])) {
        echo "ERROR: Missing budget category name";
		return;
}
    $cat_table = $wpdb->prefix . "sa_watch_budget_item"; //TODO: Refactor this with sa_watch.php
    $results = $wpdb->get_results( "SELECT budget_id FROM " . $cat_table . " WHERE name='" . $_POST["name"] . 
									"' AND description='" . $_POST["description"] ."';", OBJECT);
    if (count($results) <= 1) {
		//TODO: check for duplicate category
			$wpdb->insert(
		$cat_table,
        array(
			'name' => $_POST["name"],
			'description' => $_POST["description"]
)
	);
	} else {
		echo "Error: You have already voted";
	}
}

function sa_process_val() {
	global $wpdb;
	global $val_table;
		//Validate data
	if (empty($_POST["cat"]) || empty($_POST["amount"]) || empty($_POST["date"])) {
        echo "ERROR: Missing required name, amount or date";
		return;
        } 
	$val_table = $wpdb->prefix . "sa_watch_budget_value"; //TODO: Refactor this with sa_watch.php
    $results = $wpdb->get_results( "SELECT budget_id FROM " . $val_table . " WHERE budget_id='" . $_POST["cat"] . 
									"' AND date='" . $_POST["date"] ."';", OBJECT);
	//If thisbudget does not exist, add it. If they do, output an error
	if (count($results) <= 1) {
			$wpdb->insert(
		$val_table,
		array(
			'budget_id' => $_POST["cat"],
			'amount' => $_POST["amount"],
			'date' => $_POST["date"]
)
	);
	} else {
		echo "Error: You have already entered information about this budget category";
	}
}

function sa_process_form() {
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
	return $_POST["type"];
}


//Creates the HTML Admin Pane
function sa_data_entry() {
	global $wpdb;
	global $rep_table;
	global $bill_table;
	global $cat_table;
    global $vote_table;
	//Check if this is a form submission
	if (isset($_POST["type"])) {
		$type = sa_process_form();
	} else {
		$type = "rep"; //Default to representative screen
	}
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
		<div id="rep-data" <?php if ($type != "rep") echo 'style="display:none;"'; ?>>
			<form action="" method="post">
				<div class="form-add">
					<h3>Representative Input</h3>
					First name:<br>
					<input type="text" name="firstname"><br>
					Last name:<br>
					<input type="text" name="lastname"><br>
					Class Year:<br>
					<input type="number" name="classyear"><br>
					Position:<br>
					<input type="radio" name="position" value="pres" checked>President<br>
					<input type="radio" name="position" value="vp">Vice President<br>
					<input type="radio" name="position" value="senator">Senator<br>
					Bio:<br>
					<textarea type="text" name="bio"></textarea><br>
					Picture URL:<br>
					<input type="text" name="picture_url"><br>
					<input type="submit" name="add" value="Submit">
					<input type="hidden" name="type" value="rep"> <!-- Used when processing form -->
				</div>
				<div class="form-remove">
					<h3>Representative Removal</h3>
					<?php
					//Load all representatives
					$results = $wpdb->get_results( "SELECT rep_id, firstname, lastname FROM " . $rep_table  .";", OBJECT);
					echo '<select name="rep_id">';
					foreach ($results as $rep) {
						echo "<option value=" . $rep->rep_id . "> " . $rep->firstname . " " . $rep->lastname . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
		<div id="bill-data" <?php if ($type != "bill") echo 'style="display:none;"'; ?>>
            <form action="" method="post">
                <div class="form-add">
                     <h3>Bill Input</h3>
                Bill Name:<br>
				<input type="text" name="name"><br>
				Vote Date:<br>
				<input type="date" name="vote_date"><br>
				Description:<br>
				<textarea type="text" name="description"></textarea><br>
				Result:<br>
				<input type="radio" name="result" value="pass" checked>Passed<br>
				<input type="radio" name="result" value="fail">Failed<br>
				<input type="radio" name="result" value="tabled">Tabled<br>
				<input type="submit" value="Submit">
				<input type="hidden" name="type" value="bill"> <!-- Used when processing form -->
			</form>
		</div>
        	<div class="form-remove">
					<h3>Bill Removal</h3>
					<?php
					//Load all bills
					$results = $wpdb->get_results( "SELECT bill_id, name, vote_date FROM " . $bill_table  .";", OBJECT);
					echo '<select name="bill_id">';
					foreach ($results as $bill) {
						echo "<option value=" . $bill->name . "> " . $bill->vote_date . " " . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
		<div id="vote-data" <?php if ($type != "vote") echo 'style="display:none;"'; ?>> <form action="" method="post">
            <div class="form-add">
			<h3>Votes Input</h3>
				Representative:<br>
				<?php 
				$results = $wpdb->get_results( "SELECT rep_id, firstname, lastname FROM " . $rep_table  .";", OBJECT);
				echo '<select name="rep">';
				foreach ($results as $rep) {
					echo "<option value=" . $rep->rep_id . "> " . $rep->firstname . " " . $rep->lastname . "</option>";
				}
				echo "</select><br>";
				?>
				Bill Name:<br>
				<?php 
				$results = $wpdb->get_results( "SELECT bill_id, name FROM " . $bill_table  .";", OBJECT);
				echo '<select name="bill">';
				foreach ($results as $bill) {
					echo "<option value=" . $bill->bill_id . "> " . $bill->name . "</option>";
				}
				echo "</select><br>";
				?>
				Result:<br>
				<input type="radio" name="vote" value="aye" checked>Aye<br>
				<input type="radio" name="vote" value="nay">Nay<br>
				<input type="radio" name="vote" value="abstain">Abstain<br>
				<input type="submit" value="Submit">
				<input type="hidden" name="type" value="vote"> <!-- Used when processing form -->
			</form>
		</div>
        <div class="form-remove">
					<h3>Vote Removal</h3>
					<?php
					//Load all votes
					$results = $wpdb->get_results( "SELECT vote_id, vote_type FROM " . $vote_table  .";", OBJECT);
					echo '<select name="vote_id">';
					foreach ($results as $vote) {
						echo "<option value=" . $vote->vote_type . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
		<div id="cat-data" <?php if ($type != "cat") echo 'style="display:none;"'; ?>>
            <form action="" method="post">
            <div class="form-add">
			<h3>Budget Category Input</h3>
				Budget Category Name:<br>
				<input type="text" name="name"><br>
				Category Description:<br>
				<textarea type="text" name="description"></textarea><br>
				<input type="submit" value="Submit">
				<input type="hidden" name="type" value="cat"> <!-- Used when processing form -->
			</form>
		</div>
    	<div class="form-remove">
					<h3>Category Removal</h3>
					<?php
					//Load all categories
					$results = $wpdb->get_results( "SELECT budget_id, name, description FROM " . $cat_table  .";", OBJECT);
					echo '<select name="budget_id">';
					foreach ($results as $cat) {
						echo "<option value=" . $cat->name . "> " . $cat->description . " " . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
		<div id="val-data" <?php if ($type != "val") echo 'style="display:none;"'; ?>>
            <form action="" method="post">
            <div class="form-add">
			<h3>Budget Value Input</h3>
				Budget Category Name:<br>
				<?php 
				$results = $wpdb->get_results( "SELECT budget_id, name FROM " . $cat_table  .";", OBJECT);
				echo '<select name="cat">';
				foreach ($results as $cat) {
					echo "<option value=" . $cat->budget_id . "> " . $cat->name . "</option>";
				}
				echo "</select><br>";
				?>
				Date:<br>
				<input type="date" name="date"><br>
				Amount:<br>
				<input type="number" step="0.01" name="amount"><br>
				<input type="submit" value="Submit">
				<input type="hidden" name="type" value="val"> <!-- Used when processing form -->
			</form>
		</div>
        <div class="form-remove">
					<h3>Budget Value Removal</h3>
					<?php
					//Load all categories
					$results = $wpdb->get_results( "SELECT budget_id, name FROM " . $cat_table  .";", OBJECT);
					echo '<select name="cat">';
					foreach ($results as $cat) {
						echo "<option value=" . $cat->budget_id . "> " . $cat->name . " " . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
	</div>
</form>
</div>
<?php
}