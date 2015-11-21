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
        $start_planet = $f->get_planet();
        $p = new ProxyPlanet($_GET['sid'], $_GET['position']);
        $p->load();
        
        $to_be_launched = new RestingFleet($f->get_sid(), $f->get_position());
        foreach (Fleet::$ALL_SHIPS as $ship) {
          if (!isset($_GET[$ship])) { $_GET[$ship] = 0; }
          $to_be_launched->set_ships($ship, $_GET[$ship]);
        }
        $to_be_launched->set_owner_id($player->get_player_id());
        
        $f->substract($to_be_launched);
        $launched = $to_be_launched->launch($p);
        $player->add_fleet($launched);
        if (!$f->is_empty()) {
          $player->update_fleet($f);
          $f->save();
        }
        else {
          $player->remove_fleet($f);
          $f->destroy();
        }
        $launched->save();
      } catch (Exception $ex) {
        print_error($ex->getMessage());
      }
    }
  }
}
//
//function build_target_planet_form() {
//  $str = "<div class='fleets' style='margin: 10px; padding: 20px;'><h3>Target selection</h3>\n";
//  $str .= "<form id='target_selection'>";
//  $str .= "System: <select id='target_sid'>\n";
//  $player = $_SESSION['player'];
//  $systems = $player->list_visible_systems();
//  foreach ($systems as $s) {
//    $str .= "<option value='".$s->get_sid()."'>".$s->get_sid().": ".$s->get_name()."</option>\n";
//  }
//  $str .= "</select><br>\n";
//  $value = "";
//  if (isset($_GET['position'])) { $value = $_GET['position']; }
//  $str .= "Planet: #<input id='target_position' type='text' size='2' maxlength='2' value='$value'/><br>\n";
//  $str .= "</form>\n";
//  $str .= "</div>\n";
//  return $str;
//}
//
//function build_ship_selector($ship, $n, $root_id) {
//  echo "<table class='fleet_selector'><tr>";
//  echo "<td class='fleet_selector' onClick='set_n_ships(${ship}_${root_id}, 0, $n)'>0</td>\n";
//  echo "<td class='fleet_selector' rowspan='2'><input class='fleet_selector' id='${ship}_${root_id}' value='$n' size='".(log10($n)+1)."' maxlength='".(log10($n)+1)."' onblur='set_n_ships(${ship}_${root_id}, ${ship}_${root_id}.value, $n)'/></td>\n";
//  echo "<td class='fleet_selector' onClick='set_n_ships(${ship}_${root_id}, $n, $n)'>All</td>\n";
//  echo "</tr><tr>";
//  echo "<td class='fleet_selector' onClick='set_n_ships(${ship}_${root_id}, ${ship}_${root_id}.value-1, $n)'>&ndash;</td>\n";
//  echo "<td class='fleet_selector' onClick='set_n_ships(${ship}_${root_id}, parseInt(${ship}_${root_id}.value)+1, $n)'>+</td>\n";
//  echo "</tr></table>";
//  echo <<<EOJS
//<script>
//  function set_n_ships(object, n, max) {
//      object.value = Math.max(0, Math.min(n, max));
//  }
//</script>
//EOJS;
//  return "document.getElementById('${ship}_${root_id}').value";
//}
//
//function display_stationary_fleet(RestingFleet $fleet) {
//  echo "<tr>\n";
//  echo "<td>".$fleet->get_sid()."</td><td>".$fleet->get_position()."</td>\n";
//  foreach (Fleet::$ALL_SHIPS as $ship) {
//    echo "<td>".$fleet->get_ships($ship)."</td>\n";
//  }
//  echo "<td></td></tr>\n";
//  echo "<tr>\n";
//  echo "<td></td><td></td>\n";
//  $js_cmd = "";
//  foreach (Fleet::$ALL_SHIPS as $ship) {
//    $n = $fleet->get_ships($ship);
//    echo "<td>";
//    if ($n > 0) {
//      $js_cmd .= "&$ship='+".build_ship_selector($ship, $n, $fleet->get_fleet_id())."+'";
//    }
//    echo "</td>\n";
//  }
//  
//  $selected_sid = "document.getElementById('target_sid').value";
//  $specified_planet = "document.getElementById('target_position').value";
//  echo "<td><a class='button' onclick=\"window.location.href='".basename(__FILE__)."?fleet_id=".$fleet->get_fleet_id()."$js_cmd&sid='+$selected_sid+'&position='+$specified_planet\">Launch</a></td>\n";
//  echo "</tr>\n";
//}
//
//function display_flying_fleet(FlyingFleet $fleet) {
//  echo "<tr>\n";
//  $from_sid = $fleet->get_departure_sid();
//  $from_pos = $fleet->get_departure_position();
//  echo "<td><a href='view_system.php?sid=$from_sid'>SID $from_sid</a></td><td>#$from_pos</td>\n";
//  echo "<td>(".date('l j M H:i:s', $fleet->get_departure_time()).")</td>\n";
//  echo "<td> --[ </td>\n";
//  foreach (Fleet::$ALL_SHIPS as $ship) {
//    echo "<td>".$fleet->get_ships($ship)."</td>\n";
//  }
//  echo "<td> ]-> </td>\n";
//  $to_sid = $fleet->get_arrival_sid();
//  $to_pos = $fleet->get_arrival_position();
//  echo "<td><a href='view_system.php?sid=$to_sid'>SID $to_sid</a></td><td>#$to_pos</td>\n";
//  echo "<td>(".date('l j M H:i:s', $fleet->get_arrival_time()).")</td>\n";
//  echo "</tr>\n";
//}

function list_fleets() {
  $resting_fleets = array();
  $sieging_fleets = array();
  $flying_fleets = array();
  foreach ($_SESSION['player']->get_fleets() as $f) {
    $f->load();
    error_log("-------------".json_encode($f)."---------------\n");
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
  return array(
      "resting" => $resting_fleets,
      "sieging" => $sieging_fleets,
      "flying" => $flying_fleets
  );
}

session_start();
check_fleet_landing($_SESSION['player']);
try_launching();
print json_encode(list_fleets());

?> 
