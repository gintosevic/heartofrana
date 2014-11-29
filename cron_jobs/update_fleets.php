#!/usr/bin/php
#
# Script to handle landed fleets
#

<?php
require(__DIR__."/../lib/common.php");


function update_fleets_all_players() {
  $results = db_query("SELECT fleet_id FROM Flight WHERE arrival_time < NOW()");
  $n_fleets = db_num_rows($results);
  for ($i = 0; $i < $n_fleets; $i++) {
    $line = db_fetch_row($results, $i);
    $flight = new FlyingFleet($line['fleet_id']);
    $flight->load();
    $flight->land();
    echo "\n";
  }
}


// build_header('basic/basic.css');
update_fleets_all_players();
// build_footer();
?>