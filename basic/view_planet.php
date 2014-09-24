<?php
require("../lib/common.php");

function check_planet_autorization($sid, $position) {
  $player = $_SESSION['player'];
  foreach ($player->get_planets() as $p) {
    if ($p->get_sid() == $sid && $p->get_position() == $position) {
      return $p;
    }
  }
  return false;
}

function display_planet_details(Planet $planet) {
  $sid = $planet->get_sid();
  $position = $planet->get_position();
  $name = sid_to_name($sid);
  echo "<table class='planet_view'><tr><td colspan=5 id='title' class='planet_view'>Planet $name (SID $sid) #$position";
  if ($planet->is_bonus()) {
    echo "<br><a class='bonus_planet'>(Bonus planet)</a>";
  }
  echo "</td></tr>\n";
  // Population
  $current_points = $planet->get_population_points();
  $current_level = $planet->get_population_level();
  $next_level_points = population_level_to_points($current_level+1);
  $remaining_points = $next_level_points-$current_points;
  $progress = round((1-($remaining_points/$next_level_points))*100);
  echo "<tr><td>Population</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td></td></tr>\n";
  // Production
  $current_points = $planet->get_production_points();
  echo "<tr><td>Production</td><td colspan='3'>$current_points units</td><td></td></tr>\n";
  // Buildings
  echo "<tr class='planet_view' id='description'><td>Building type</td><td>Level</td><td>Progress</td><td>Remain</td><td></td></tr>\n";
  $buildings = array("farm" => "Hydroponic farm",
                     "factory" => "Robotic factory",
		     "cybernet" => "Galactic cybernet",
		     "lab" => "Research lab",
		     "starbase" => "Starbase");
  foreach ($buildings as $type => $name) {
    $current_points = $planet->get_building_points($type);
    $current_level = $planet->get_building_level($type);
    $next_level_points = building_level_to_points($current_level+1);
    $remaining_points = $next_level_points-$current_points;
    $progress = round((1-($remaining_points/$next_level_points))*100);
    echo "<tr><td>$name</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td>";
    if ($planet->get_production_points() > 0) {
      echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&building=$type&spend=".$planet->get_production_points()."'\">Spend all</a>";
    }
    else {
      echo "<a class='disabled_button'>Spend all</a>";
    }
    if ($remaining_points <= $planet->get_production_points()) {
      echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&building=$type&spend=$remaining_points'\">Upgrade</a>";
    }
    else {
      echo "<a class='disabled_button'>Upgrade</a>";
    }
    echo "</td></tr>\n";
  }
    // Fleet
    echo "<tr class='planet_view' id='description'><td>Ship type</td><td colspan='3'>Quantity</td><td></td></tr>\n";
    $player = $_SESSION['player'];
    $planet->load_owner_fleet();
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($player->is_enabled($ship)) {
	$name = ucfirst($ship);
	$n_ships = $planet->get_owner_ships($ship);
	echo "<tr><td>$name</td><td colspan='3'>$n_ships</td><td><a class='todo'>Build</a></td></tr>\n";
      }
    }
  echo "</table>";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  build_menu();
  if (isset($_GET['sid']) && isset($_GET['position'])) {
    $planet = check_planet_autorization($_GET['sid'], $_GET['position']);
    if (!$planet) {
      echo "You are not allowed to see details of planet SID".$_GET['sid']." #".$_GET['position'].".<br>\n";
      return;
    }
    if (isset($_GET['spend']) && (isset($_GET['building']) || isset($_GET['ship']))) {
      if (isset($_GET['building'])) {
	$planet->upgrade_building($_GET['building'], $_GET['spend']);
      }
      elseif (isset($_GET['ship'])) {
	$planet->build_fleet($_GET['ship'], $_GET['spend']);
      }
      $planet->save();
    }
    display_planet_details($planet);
  }
}
build_footer();

?> 
