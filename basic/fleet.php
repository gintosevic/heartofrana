<?php
require("../lib/common.php");


function display_fleet(Fleet $fleet, $sid, $position) {
  echo "<tr>\n";
  echo "<td>$sid</td><td>$position</td>\n";
  foreach (Fleet::$ALL_SHIPS as $ship) {
    echo "<td>".$fleet->get_ships($ship)."</td>\n";
  }
  echo "<td><a class='todo'>Launch</a></td>\n";
  echo "</tr>\n";
}

function build_fleet() {
  echo <<<EOL
<div class="tab" id="fleet_tab">
<h1>Fleet</h1>
EOL;
  echo "<table class='fleets'>\n";
  echo "<tr id='description' class='fleets'><td>System</td><td>Position</td><td>Colonyships</td><td>Transports</td><td>Destroyers</td><td>Cruisers</td><td>Battleships</td><td></td></tr>\n";
//   foreach ($_SESSION['player']->get_fleets() as $f) {
//     display_fleet($f);
//   }
  foreach ($_SESSION['player']->get_planets() as $p) {
    if ($p->has_owner_fleet()) {
      display_fleet($p->get_owner_fleet(), $p->get_sid(), $p->get_position());
    }
  }
  echo "</table>\n";
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
