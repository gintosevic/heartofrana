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
  echo <<<EOL
<div class="tab" id="planet_view_tab">
<h1>Planet details</h1>
EOL;
  echo "<table class='planet_view'><tr><td colspan=5 id='title' class='planet_view'>Planet $name (SID $sid) #$position";
  if ($planet->is_bonus()) {
    echo "<br><a class='bonus_planet'>(Bonus planet)</a>";
  }
  echo "</td></tr>\n";
  // Population
  $current_points = $planet->get_population_points();
  $current_level = $planet->get_population_level();
  $next_level_points = population_level_to_points($current_level+1);
  $next_level_step = $next_level_points - population_level_to_points($current_level);
  $remaining_points = $next_level_points-$current_points;
  $progress = round((1-($remaining_points/$next_level_step))*100);
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
    $next_level_step = $next_level_points - building_level_to_points($current_level);
    $remaining_points = $next_level_points-$current_points;
    $progress = round((1-($remaining_points/$next_level_step))*100);
    echo "<tr><td>$name</td><td>$current_level</td><td>$progress%</td><td>$remaining_points</td><td>";
    if ($remaining_points <= $planet->get_production_points()) {
      echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&building=$type&spend=$remaining_points'\">Upgrade</a>";
    }
    else {
      echo "<a class='disabled_button'>Upgrade</a>";
    }
    if ($planet->get_production_points() > 0) {
      echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&building=$type&spend=".$planet->get_production_points()."'\">Spend all</a>";
    }
    else {
      echo "<a class='disabled_button'>Spend all</a>";
    }
    echo "</td></tr>\n";
  }
    // Fleet
    echo "<tr class='planet_view' id='description'><td>Ship type</td><td>Quantity</td><td>Price</td><td>Remaining</td><td></td></tr>\n";
    $player = $_SESSION['player'];
    $planet->load_owner_fleet();
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($player->is_enabled($ship)) {
	$name = ucfirst($ship);
	$n_ships = $planet->get_owner_ships($ship);
	$price = Fleet::get_ship_price($ship, $player->get_science_level("economy"));
	$remaining = $price - $planet->get_ship_points($ship);
	echo "<tr><td>$name</td><td>$n_ships</td><td>$price</td><td>$remaining</td><td>";
	foreach (array("1", "10", "100", "1000") as $num) {
	  $required = $remaining + ($num-1)*$price;
	  if ($required <= $planet->get_production_points()) {
	    echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&ship=$ship&spend=$required'\">+$num</a>";
	  }
	  else {
	    echo "<a class='disabled_button'>+$num</a>";
	  }
	}
	if ($planet->get_production_points() > 0) {
	  echo "<a class='button'  onclick=\"window.location.href='".basename(__FILE__)."?sid=$sid&position=$position&ship=$ship&spend=".$planet->get_production_points()."'\">Spend all</a>";
	}
	else {
	  echo "<a class='disabled_button'>Spend all</a>";
	}
	echo "</td></tr>\n";
      }
    }
  echo "</table>";
  echo "</div>\n";

}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
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
	$before = $planet->has_owner_fleet();
	$planet->upgrade_ship($_GET['ship'], $_GET['spend']);
	$after = $planet->has_owner_fleet();
	if ($after) {
	  if ($before != $after) {
	    $_SESSION['player']->add_fleet($planet->get_owner_fleet());
	  }
	  else {
	    $_SESSION['player']->update_fleet($planet->get_owner_fleet());
	  }
	  $planet->get_owner_fleet()->save();
	}
      }
      $planet->save();
    }
    display_planet_details($planet);
  }
}
build_footer();

?> 
