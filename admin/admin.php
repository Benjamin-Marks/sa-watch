<?php

add_action( 'admin_menu', 'sawatch_admin_creation' );

//Registers our admin pane
function sawatch_admin_creation() {
	$capability = 'edit_pages';
	
	add_submenu_page( 'tools.php', 'SA Data Entry', 'SA Data Entry', $capability, 'sa_data_entry', 'sa_data_entry');
	wp_enqueue_script( 'sa-watch-admin', plugin_dir_url( __FILE__ ) . 'admin_pane.js' );
	wp_register_style( 'sa-watch', plugins_url( 'sa_watch/style.css' ) );
	wp_enqueue_style( 'sa-watch' );
}

function sa_process_rep() {
	global $wpdb, $rep_table, $vote_table;
	if (!empty($_POST["add"])) {
		//Validate data
		if (empty($_POST["firstname"]) || empty($_POST["lastname"]) || 
			empty($_POST["classyear"]) || empty($_POST["student_id"])) {

			echo "ERROR: Missing required name or classyear data";
			return;
		} else if (intval($_POST["classyear"]) < 2010 || 
			intval($_POST["classyear"]) > (intval(date("Y")) + 10)) {

			echo "ERROR: Class year is an outlandish value";
			return;
		} else if (!empty($_POST["picture_url"])) {
			if(!filter_var($_POST["picture_url"], FILTER_VALIDATE_URL)) {
				echo "The picture is not a valid URL";
				return;
			}
		}
		//Search the database for this userID
		$results = $wpdb->get_results( "SELECT rep_id FROM $rep_table WHERE student_id='" . $_POST["student_id"] . "';", OBJECT);
		//If this representative does not exist, add them. If they do, output an error
		if (count($results) < 1) {
			if (strcmp($_POST["position"], 'pres') == 0) {
				$pres = $wpdb->get_results("SELECT firstname, lastname FROM $rep_table WHERE position='pres';", OBJECT);
				if (count($pres) != 0) {
					echo "Error: A president is already in the database: " . $pres[0]->firstname . " " . $pres[0]->lastname;
					return;
				}
			} else if (strcmp($_POST["position"], 'vp') == 0) {
				$vp = $wpdb->get_results("SELECT firstname, lastname FROM $rep_table WHERE position='vp';", OBJECT);
				if (count($vp) != 0) {
					echo "Error: A vice president is already in the database: " . $vp[0]->firstname . " " . $vp[0]->lastname;
					return;
				}
			} else if (strcmp($_POST["position"], 'classpres') == 0) {
				$classpres = $wpdb->get_results("SELECT firstname, lastname FROM $rep_table WHERE position='classpres' AND classyear=" . $_POST["classyear"] .";", OBJECT);
				if (count($classpres) != 0) {
					echo "Error: A class of " . $_POST["classyear"] . " president is already in the database: " . $classpres[0]->firstname . " " . $classpres[0]->lastname;
					return;
				}
			}
			$wpdb->insert(
				$rep_table,
				array(
					'firstname' => $_POST["firstname"],
					'lastname' => $_POST["lastname"],
					'student_id' => $_POST["student_id"],
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
		//Remove rep
		//Check for foreign key constraints
		$votes = $wpdb->get_results("SELECT vote_id FROM $vote_table where rep_id='" . $_POST["rep_id"] . "';");
		if (count($votes) >= 1) {
			//If they have votes but we're supposed to delete them, delete. Otherwise error out
			if (isset($_POST["deleteDep"])) {
				$wpdb->delete($vote_table, array('rep_id' => $_POST["rep_id"]));
				$wpdb->delete($rep_table, array('rep_id' => $_POST["rep_id"]));
			} else {
				echo "Error: This representative has voted on bills. Please either delete these votes manually or confirm you would like them all to be deleted";
			}
		} else {
			//If there are no votes, just delete
			$wpdb->delete($rep_table, array('rep_id' => $_POST["rep_id"]));
		}
	}
}

function sa_process_bill() {
	global $wpdb, $bill_table, $vote_table;
	if (!empty($_POST["add"])) {
		//If this bill does not exist, add it. If it do, output an error
		//Validate data
		if (empty($_POST["name"])) {
			echo "ERROR: Missing bill name";
			return;
		}
		$results = $wpdb->get_results( "SELECT bill_id FROM $bill_table WHERE name='" . $_POST["name"] . "';", OBJECT);
		//If this bill does not exist, add them. If they do, output an error
		if (count($results) < 1) {
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
	} else {
		//Check for foreign key constraints
		$votes = $wpdb->get_results("SELECT vote_id FROM $vote_table where bill_id='" . $_POST["bill_id"] . "';");
		if (count($votes) >= 1) {
			//If the bill has votes but we're supposed to delete them, delete. Otherwise error out
			if (isset($_POST["deleteDep"])) {
				$wpdb->delete($vote_table, array('bill_id' => $_POST["bill_id"]));
				$wpdb->delete($bill_table, array('bill_id' => $_POST["bill_id"]));
			} else {
				echo "Error: This bill has associated votes. Please either delete these votes manually or confirm you would like them all to be deleted";
			}
		} else {
			//If there are no votes, just delete
			$wpdb->delete($bill_table, array('bill_id' => $_POST["bill_id"]));
		}
	} 
}

function sa_process_vote() {
	global $wpdb, $vote_table;
	if (!empty($_POST["add"])) {
		//Validate data
		if (empty($_POST["rep"]) || empty($_POST["bill"])) {
			echo "ERROR: Missing required Representative or Bill name";
			return;
			}
		//If this vote does not exist, add it. If it does, output an error
		$results = $wpdb->get_results( "SELECT vote_id FROM $vote_table WHERE rep_id='" . $_POST["rep"] . 
										"' AND bill_id='" . $_POST["bill"] ."';", OBJECT);
		if (count($results) < 1) {
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
	} else {
		$wpdb->delete($vote_table, array('vote_id' => $_POST["vote_id"]));
	} 
}

function sa_process_cat() {
	global $wpdb, $cat_table, $val_table;
	if (!empty($_POST["add"])) {
		//Validate data
		if (empty($_POST["name"])) {
			echo "ERROR: Missing budget category name";
			return;
		}
		$results = $wpdb->get_results( "SELECT budget_id FROM $cat_table WHERE name='" . $_POST["name"] . 
										"' AND description='" . $_POST["description"] ."';", OBJECT);
		if (count($results) < 1) {
			$wpdb->insert(
				$cat_table,
				array(
					'name' => $_POST["name"],
					'description' => $_POST["description"]
				)
			);
		} else {
			echo "Error: This category already exists";
		}
	} else {
		//Check for foreign key constraints
		$vals = $wpdb->get_results("SELECT budget_value_id FROM $val_table where budget_id='" . $_POST["budget_id"] . "';");
		if (count($vals) >= 1) {
			//If the category has values but we're supposed to delete them, delete. Otherwise error out
			if (isset($_POST["deleteDep"])) {
				$wpdb->delete($val_table, array('budget_id' => $_POST["budget_id"]));
				$wpdb->delete($cat_table, array('budget_id' => $_POST["budget_id"]));
			} else {
				echo "Error: This budget category has values. Please either delete these values manually or confirm you would like them all to be deleted";
			}
		} else {
			//If there are no values, just delete
			$wpdb->delete($cat_table, array('budget_id' => $_POST["budget_id"]));
		}
	}
}

function sa_process_val() {
	global $wpdb, $val_table;
	if (!empty($_POST["add"])) {
		//Validate data
		if (empty($_POST["cat"]) || empty($_POST["amount"]) || empty($_POST["date"])) {
			echo "ERROR: Missing required name, amount or date";
			return;
		} 
		$results = $wpdb->get_results( "SELECT budget_id FROM $val_table WHERE budget_id='" . $_POST["cat"] . 
										"' AND date='" . $_POST["date"] ."';", OBJECT);
		//If this budget does not exist, add it. If they do, output an error
		if (count($results) < 1) {
			$wpdb->insert(
				$val_table,
				array(
					'budget_id' => $_POST["cat"],
					'amount' => $_POST["amount"],
					'date' => $_POST["date"]
				)
			);
		} else {
			echo "Error: You have already entered information about this budget value";
		}
	} else {
		$wpdb->delete($val_table, array('budget_value_id' => $_POST["val_id"]));
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
			$_POST["type"] = "rep";
	}
	return $_POST["type"];
}


//Creates the HTML Admin Pane
function sa_data_entry() {
	global $wpdb, $rep_table, $bill_table, $cat_table, $vote_table, $val_table;
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
					Student ID (for email):<br>
					<input type="text" name="student_id"><br>
					Class Year:<br>
					<input type="number" name="classyear"><br>
					Position:<br>
					<input type="radio" name="position" value="pres" checked>President<br>
					<input type="radio" name="position" value="vp">Vice President<br>
					<input type="radio" name="position" value="senator">Senator<br>
					<input type="radio" name="position" value="classpres">Class President<br>
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
					$results = $wpdb->get_results( "SELECT rep_id, firstname, lastname FROM {$rep_table};", OBJECT);
					echo '<select name="rep_id">';
					foreach ($results as $rep) {
						echo "<option value=" . $rep->rep_id . "> " . $rep->firstname . " " . $rep->lastname . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="checkbox" name="deleteDep" value=1>Delete all votes associated with this representative<br>
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
					<input type="radio" name="result" value="withdrawn">Withdrawn<br>
					<input type="radio" name="result" value="fail">Failed<br>
					<input type="radio" name="result" value="tabled">Tabled<br>
					<input type="submit" name="add" value="Submit">
					<input type="hidden" name="type" value="bill"> <!-- Used when processing form -->
				</div>
				<div class="form-remove">
					<h3>Bill Removal</h3>
					<?php
					//Load all bills
					$results = $wpdb->get_results( "SELECT bill_id, name, vote_date FROM {$bill_table};", OBJECT);
					echo '<select name="bill_id">';
					foreach ($results as $bill) {
						echo "<option value=" . $bill->bill_id . "> " . $bill->name . " " . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="checkbox" name="deleteDep" value=1>Delete all votes associated with this bill<br>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
		<div id="vote-data" <?php if ($type != "vote") echo 'style="display:none;"'; ?>> 
			<form action="" method="post">
				<div class="form-add">
					<h3>Votes Input</h3>
					Representative:<br>
					<?php 
					$results = $wpdb->get_results( "SELECT rep_id, firstname, lastname FROM {$rep_table};", OBJECT);
					echo '<select name="rep">';
					foreach ($results as $rep) {
						echo "<option value=" . $rep->rep_id . "> " . $rep->firstname . " " . $rep->lastname . "</option>";
					}
					echo "</select><br>";
					?>
					Bill Name:<br>
					<?php 
					$results = $wpdb->get_results( "SELECT bill_id, name FROM {$bill_table};", OBJECT);
					echo '<select name="bill">';
					foreach ($results as $bill) {
						echo "<option value=" . $bill->bill_id . "> " . $bill->name . "</option>";
					}
					echo "</select><br>";
					?>
					Result:<br>
					<input type="radio" name="vote" value="sponsor" checked>Sponsored (Aye)<br>
					<input type="radio" name="vote" value="aye">Aye<br>
					<input type="radio" name="vote" value="nay">Nay<br>
					<input type="radio" name="vote" value="abstain">Abstain<br>
					<input type="submit" name="add" value="Submit">
					<input type="hidden" name="type" value="vote"> <!-- Used when processing form -->
				</div>
				<div class="form-remove">
					<h3>Vote Removal</h3>
					<?php
					//Load all votes
					$results = $wpdb->get_results( "SELECT vote_id, rep_id, bill_id FROM {$vote_table};", OBJECT);
					echo '<select name="vote_id">';
					foreach ($results as $vote) {
						//TODO: refactor, use joins for god's sake
						$rep = $wpdb->get_results("SELECT firstname, lastname FROM $rep_table WHERE rep_id=" . $vote->rep_id . ";", OBJECT);
						$bill = $wpdb->get_results("SELECT name FROM $bill_table WHERE bill_id=" . $vote->bill_id . ";", OBJECT);
						echo "<option value=" . $vote->vote_id . "> " . $rep[0]->firstname . " " . $rep[0]->lastname . ", " . $bill[0]->name . "</option>";
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
					<input type="submit" name="add" value="Submit">
					<input type="hidden" name="type" value="cat"> <!-- Used when processing form -->
				</div>
				<div class="form-remove">
					<h3>Category Removal</h3>
					<?php
					//Load all categories
					$results = $wpdb->get_results( "SELECT budget_id, name FROM {$cat_table};", OBJECT);
					echo '<select name="budget_id">';
					foreach ($results as $cat) {
						echo "<option value=" . $cat->budget_id . "> " . $cat->name . " " . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="checkbox" name="deleteDep" value=1>Delete all values associated with this budget category<br>
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
					$results = $wpdb->get_results( "SELECT budget_id, name FROM {$cat_table};", OBJECT);
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
					<input type="submit" name="add" value="Submit">
					<input type="hidden" name="type" value="val"> <!-- Used when processing form -->
				</div>
				<div class="form-remove">
					<h3>Budget Value Removal</h3>
					<?php
					//Load all categories
					$results = $wpdb->get_results("SELECT budget_value_id, budget_id, date FROM {$val_table};", OBJECT);
					echo '<select name="val_id">';
					foreach ($results as $cat) {
						//TODO: lrn 2 sql pls
						$category = $wpdb->get_results("SELECT name FROM $cat_table WHERE budget_id=" . $cat->budget_id . ";", OBJECT);
						echo "<option value=" . $cat->budget_value_id . "> " . $category[0]->name . ", " . $cat->date . "</option>";
					}
					echo "</select><br>";
					?>
					<input type="submit" name="remove" value="Submit">
				</div>
			</form>
		</div>
	</div>
<?php
}