<?php
require("../lib/common.php");

function display_planet_in_system($planet) {
}

function check_planet_autorization($sid, $position) {
  $player = $_SESSION['player'];
  foreach ($player->get_planets() as $p) {
    if ($p->get_sid() == $sid && $p->get_position() == $position) {
      return $p;
    }
  }
  return false;
}

function display_planet_details($sid, $position) {
  $name = sid_to_name($sid);
  $planet = check_planet_autorization($sid, $position);
  if (!$planet) {
    echo "You are not allowed to see details of this planet.<br>\n";
    return;
  }
  
  echo "<table class='planet_view'><tr><td colspan=5 id='title' class='planet_view'>Planet $name (SID $sid) #$position";
  if ($planet->is_bonus()) {
    echo "<br><a class='bonus_planet'>(Bonus planet)</a>";
  }
  echo "</td></tr>\n";
  $current_points = $planet->get_population_points();
  $current_level = $planet->get_population_level();
  $next_level_points = population_level_to_points($current_level+1);
  $remaining_points = $next_level_points-$current_points;
  $progress = round((1-($remaining_points/$next_level_points))*100);
  echo "<tr><td>Population</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td></td></tr>\n";
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
    echo "<tr><td>$name</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td><a class='todo'>[Upgrade]</a></td></tr>\n";
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
    display_planet_details($_GET['sid'], $_GET['position']);
  }
}
build_footer();

?> 
