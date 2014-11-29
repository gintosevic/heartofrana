<?php
require("../lib/common.php");

function display_science($player) {
  echo "<div class='tab' id='science_tab'>\n<h1>Science</h1>\n";
  echo "<table class='science'>\n";
  echo "<tr class='science' id='description'><td>Discipline</td><td>Level</td><td>Progress</td><td>Remain</td><td></td></tr>\n";
  $buildings = array("biology" => "Biology",
                     "economy" => "Economy",
		     "energy" => "Energy",
		     "mathematics" => "Mathematics",
		     "physics" => "Physics",
		     "social" => "Social");
  foreach ($buildings as $type => $name) {
    $current_points = $player->get_science_points($type);
    $current_level = $player->get_science_level($type);
    $next_level_points = science_level_to_points($current_level+1);
    $remaining_points = $next_level_points-$current_points;
    $progress = round((1-($remaining_points/$next_level_points))*100);
    if ($type == $player->get_current_science())  {
      echo "<tr id='current_science' class='science'><td>$name</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td></td></tr>\n";
    }
    else {
      echo "<tr><td>$name</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td><a class='button' onclick=\"window.location.href='".basename(__FILE__)."?set_science=$type'\">Select</a></td></tr>\n";
    }
  }
  $current_points = $player->get_culture_points();
  $current_level = $player->get_culture_level();
  $next_level_points = culture_level_to_points($current_level+1);
  $remaining_points = $next_level_points-$current_points;
  $progress = round((1-($remaining_points/$next_level_points))*100);
  echo "<tr><td>Culture</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td></td></tr>\n";
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
  if (isset($_GET['set_science'])) {
    $_SESSION['player']->set_current_science($_GET['set_science']);
    $_SESSION['player']->save();
  }
  display_science($_SESSION['player']);
}
build_footer();

?> 
