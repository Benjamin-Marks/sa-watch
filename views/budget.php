<?php 

//Register our Budget graph shortcode
function sa_watch_graph_shortcode() { ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
      //TODO: how to make this continuous
      var data = google.visualization.arrayToDataTable([
        ['Date', 'Things', 'Stuff'],
        ['Jan 1, 2015',   800,   400],
        ['Feb 1, 2015',   700,   390],
        ['Feb 6, 2015',   660,   280],
        ['April 1 2015',  300,   0]
      ]);
      data.setColumnProperty(0, 'type', 'date');
      var options = {
        title: 'Company Performance',
        hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}, format: ['MMM d, y']},
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
add_shortcode('sa_watch_graph_content', 'sa_watch_graph_shortcode');


//Database handling for our javascript
add_action('wp_sa_watch_ajax_budget_graph', 'sa_watch_budget_callback');

function sa_watch_budget_callback() {
  global $wpdb;

  //Grab Budget entries out of the database, place in array



  wp_die(); //To prevent slow or trailing output
}