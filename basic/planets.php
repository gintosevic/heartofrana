<?php
require("../lib/common.php");

function display_planet_short($planet) {
//   $planet->load();
  $sid = $planet->get_sid();
  $name = sid_to_name($sid);
  $position = $planet->get_position();
  echo "<tr";
  if ($planet->is_bonus()) {
    echo " class='bonus_planet'";
  }
  echo ">\n";
  echo "<td><a href=\"view_system.php?sid=$sid\">$name ($sid)</a></td>\n";
  echo "<td>$position</td>\n";
//   echo "<td>".$planet->owner."</td>\n";
  echo "<td>".$planet->get_population_level()."</td>\n";
  echo "<td>".$planet->get_building_level("starbase")."</td>\n";
  echo "<td>".$planet->get_production_points()."</td>\n";
  echo "<td><a class='button' href=\"view_planet.php?sid=$sid&position=$position\">See details</a></td>\n";
  echo "</tr>\n";
}

function display_planets() {
  echo "<div class='tab' id='planets_tab'><h1>Planets</h1>";
  echo "<table class='planets'>\n";
  echo "<tr id='description' class='planets'><td>System</td><td>Position</td><td>Population</td><td>Starbase</td><td>Production points</td><td></td></tr>\n";
  foreach ($_SESSION['player']->get_planets() as $p) {
    display_planet_short($p);
  }
  echo "</table>\n";
  echo "</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
  build_menu();
  display_planets();
}
build_footer();

?> 
