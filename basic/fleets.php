<?php
require("../lib/common.php");

function find_fleet($fleet_id) {
  foreach ($_SESSION['player']->get_fleets() as $f) {
    if (get_class($f) == "RestingFleet" && $f->get_fleet_id() == $fleet_id) { return $f; }
    if (get_class($f) == "SiegingFleet" && $f->get_fleet_id() == $fleet_id) { return $f; }
  }
  throw new Exception("Fleet does not exist or is not available.");
}

function try_launching() {
  $player = $_SESSION['player'];
  $n_flying_fleets = $player->count_flying_fleets();
  if ($n_flying_fleets >= FLYING_FLEET_MAX_NUMBER) {
    print_error("You have already reached the maximum number of flying fleets ($n_flying_fleets).");
  }
  elseif (isset($_GET['fleet_id']) || isset($_GET['sid']) || isset($_GET['position'])) {
    if (!isset($_GET['fleet_id'])) {
      print_error("You need to select a fleet.");
    }
    elseif (!isset($_GET['sid'])) {
      print_error("You need to choose a target system.");
    }
    elseif (!isset($_GET['position'])) {
      print_error("You need to choose a target planet in system ".$_GET['sid'].".");
    }
    else {
      try {
        $f = find_fleet($_GET['fleet_id']);
        $p = new Planet($_GET['sid'], $_GET['position']);
        $p->load();
        $new_f = $f->launch($p);
        $player->update_fleet($new_f);
        $new_f->save();
      } catch (Exception $ex) {
        print_error($ex->getMessage());
      }
    }
  }
}

function build_target_planet_form() {
  $str = "<div class='fleets' style='margin: 10px; padding: 20px;'><h3>Target selection</h3>\n";
  $str .= "<form id='target_selection'>";
  $str .= "System: <select id='target_sid'>\n";
  $player = $_SESSION['player'];
  $systems = $player->list_visible_systems();
  foreach ($systems as $s) {
    $str .= "<option value='".$s->get_sid()."'>".$s->get_sid().": ".$s->get_name()."</option>\n";
  }
  $str .= "</select><br>\n";
  $value = "";
  if (isset($_GET['position'])) { $value = $_GET['position']; }
  $str .= "Planet: #<input id='target_position' type='text' size='2' maxlength='2' value='$value'/><br>\n";
  $str .= "</form>\n";
  $str .= "</div>\n";
  return $str;
}

function display_stationary_fleet(RestingFleet $fleet) {
  echo "<tr>\n";
  echo "<td>".$fleet->get_sid()."</td><td>".$fleet->get_position()."</td>\n";
  foreach (Fleet::$ALL_SHIPS as $ship) {
    echo "<td>".$fleet->get_ships($ship)."</td>\n";
  }
  $selected_sid = "document.getElementById('target_sid').value";
  $specified_planet = "document.getElementById('target_position').value";
  echo "<td><a class='button' onclick=\"window.location.href='".basename(__FILE__)."?fleet_id=".$fleet->get_fleet_id()."&sid='+$selected_sid+'&position='+$specified_planet\">Launch</a></td>\n";
  echo "</tr>\n";
}

function display_flying_fleet(FlyingFleet $fleet) {
  echo "<tr>\n";
  $from_sid = $fleet->get_departure_sid();
  $from_pos = $fleet->get_departure_position();
  echo "<td><a href='view_system.php?sid=$from_sid'>SID $from_sid</a></td><td>#$from_pos</td>\n";
  echo "<td>(".date('l j M H:i:s', $fleet->get_departure_time()).")</td>\n";
  echo "<td> --[ </td>\n";
  foreach (Fleet::$ALL_SHIPS as $ship) {
    echo "<td>".$fleet->get_ships($ship)."</td>\n";
  }
  echo "<td> ]-> </td>\n";
  $to_sid = $fleet->get_arrival_sid();
  $to_pos = $fleet->get_arrival_position();
  echo "<td><a href='view_system.php?sid=$to_sid'>SID $to_sid</a></td><td>#$to_pos</td>\n";
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
<h1>Fleets</h1>
EOL;
  
//  echo "<table><tr><td>\n";
  
  echo "<div>";
  echo "<div style='height: 100%; float: right;'>\n";
  echo build_target_planet_form();
  echo "</div>\n";
  echo "<div style='display:table-col'>\n";
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
  echo "</div>\n";
//  echo "</td><td rowspan='2' align='center'>\n";
  

//  echo "</td></tr><tr><td>\n";
  echo "<div style='display:table-col'>\n";
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
  echo "</div>\n";
//  echo "</td></tr><tr><td>\n";
  echo "<div style='display:table-col'>\n";
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
//  echo "</td></tr></table>\n";
  echo "</div>\n";
  echo "</div>\n";
  echo "</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
  build_menu();
  try_launching();
  build_fleet();
}
build_footer();

?> 
