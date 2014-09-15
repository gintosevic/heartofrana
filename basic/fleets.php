<?php
require("../lib/common.php");


function display_stationary_fleet(RestingFleet $fleet) {
  echo "<tr>\n";
  echo "<td>".$fleet->get_sid()."</td><td>".$fleet->get_position()."</td>\n";
  foreach (Fleet::$ALL_SHIPS as $ship) {
    echo "<td>".$fleet->get_ships($ship)."</td>\n";
  }
  echo "<td><a class='todo'>Launch</a></td>\n";
  echo "</tr>\n";
}

function display_flying_fleet(FlyingFleet $fleet) {
  echo "<tr>\n";
  echo "<td>".$fleet->get_departure_sid()."</td><td>".$fleet->get_departure_position()."</td>\n";
  echo "<td>(".date('l j M H:i:s', $fleet->get_departure_time()).")</td>\n";
  echo "<td> --[ </td>\n";
  foreach (Fleet::$ALL_SHIPS as $ship) {
    echo "<td>".$fleet->get_ships($ship)."</td>\n";
  }
  echo "<td> ]-> </td>\n";
  echo "<td>".$fleet->get_arrival_sid()."</td><td>".$fleet->get_arrival_position()."</td>\n";
  echo "<td>(".date('l j M H:i:s', $fleet->get_arrival_time()).")</td>\n";
  echo "</tr>\n";
}

function build_fleet() {
  $resting_fleets = array();
  $sieging_fleets = array();
  $flying_fleets = array();
  foreach ($_SESSION['player']->get_fleets() as $f) {
    if (get_class($f) == "RestingFleet") {
      array_push($resting_fleets, $f);
    }
    if (get_class($f) == "SiegingFleet") {
      array_push($sieging_fleets, $f);
    }
    if (get_class($f) == "FlyingFleet") {
      array_push($flying_fleets, $f);
    }
  }
  echo <<<EOL
<div class="tab" id="fleet_tab">
<h1>Fleet</h1>
EOL;

  echo "<h2>Resting fleets</h2>\n";
  if (count($resting_fleets) > 0) {
    echo "<table class='fleets'>\n";
    echo "<tr id='description' class='fleets'><td>System</td><td>Position</td><td>Colonyships</td><td>Transports</td><td>Destroyers</td><td>Cruisers</td><td>Battleships</td><td></td></tr>\n";
    foreach ($resting_fleets as $f) {
      display_stationary_fleet($f);
    }
    echo "</table>\n";
  }
  else {
    echo "None<br>\n";
  }
  
  echo "<h2>Sieging fleets</h2>\n";
  if (count($sieging_fleets) > 0) {
    echo "<table class='fleets'>\n";
    echo "<tr id='description' class='fleets'><td>System</td><td>Position</td><td>Colonyships</td><td>Transports</td><td>Destroyers</td><td>Cruisers</td><td>Battleships</td></tr>\n";
    foreach ($sieging_fleets as $f) {
      display_stationary_fleet($f);
    }
    echo "</table>\n";
  }
  else {
    echo "None<br>\n";
  }
  
  echo "<h2>Flying fleets</h2>\n";
  if (count($flying_fleets) > 0) {
    echo "<table class='fleets'>\n";
    echo "<tr id='description' class='fleets'><td colspan='3'>From (time)</td><td></td><td>Colonyships</td><td>Transports</td><td>Destroyers</td><td>Cruisers</td><td>Battleships</td><td></td><td colspan='3'>To (time)</td></tr>\n";
    foreach ($flying_fleets as $f) {
      display_flying_fleet($f);
    }
    echo "</table>\n";
  }
  else {
    echo "None<br>\n";
  }
  echo "</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  build_menu();
  build_fleet();
}
build_footer();

?> 
