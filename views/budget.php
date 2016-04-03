<?php 

add_shortcode('sa_watch_graph_content', 'sa_watch_graph_shortcode');


//Database handling for our javascript
add_action('wp_sa_watch_ajax_budget_graph', 'sa_watch_budget_callback');

function sa_watch_budget_callback() {
  global $wpdb, $cat_table, $val_table;
  //Grab Budget entries out of the database, place in array
  //Get full list of dates
  $dates = $wpdb->get_results("SELECT DISTINCT date FROM $val_table ORDER BY date;", OBJECT);

  //Get full list of names and output it
  $names = $wpdb->get_results( "SELECT budget_id, name FROM {$cat_table};", OBJECT);
  echo "['Date'";
  //Verify each category has at least one data point, otherwise chart won't load
  $numNames = count($names);
  for ($i = 0; $i < $numNames; $i++) {
    $data = $wpdb->get_results("SELECT amount FROM $val_table WHERE budget_id = " . $names[$i]->budget_id . ";", OBJECT);
    if (count($data) != 0) {
      echo ", '" . $names[$i]->name . "' ";
    } else {
      //If this category has no data, remove it from our array
      unset($names[$i]);
    }
  }
  echo "]";

  //Get data for names and dates TODO: this is super inefficient - we call the database a ton of times. Optimize it
  $date_size = count($dates);
  for ($i = 0; $i < $date_size; $i++) {
    $data = $wpdb->get_results("SELECT budget_id, amount FROM $val_table WHERE date = '" . $dates[$i]->date . "' AND budget_id = " . $names[0]->budget_id . ";", OBJECT);
    if (count($data) == 0) {
      echo ", ['" . strval($dates[$i]->date) . "', null";
    } else {
      echo ", ['" . strval($dates[$i]->date) . "', " . strval($data[0]->amount);
    }
    foreach (array_slice($names, 1) as $name) {
      $data = $wpdb->get_results("SELECT budget_id, amount, date FROM $val_table WHERE date = '" . $dates[$i]->date . "' AND budget_id = " . $name->budget_id . ";", OBJECT);
      if (count($data) < 1) { //Assuming it's 0, otherwise weird and invalid
        echo ", null";
      } else {
        echo ", " . strval($data[0]->amount);
      }
    }
    echo "]";
  }
}


//Register our Budget graph shortcode
function sa_watch_graph_shortcode() { ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    var data;
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      //TODO: how to make this continuous
      var data = google.visualization.arrayToDataTable([
        <?php echo sa_watch_budget_callback(); ?>
      ]);
      data.setColumnProperty(0, 'type', 'date');
      var options = {
        title: 'SA Budgeted Funds Remaining',
        hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}, format: ['YYYY-MM-DD']},
        vAxis: {minValue: 0},
        isStacked: true
      };

      var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
  </script>
  <div id="chart_div" style="width: 900px; height: 500px;"></div>

<?php
}
