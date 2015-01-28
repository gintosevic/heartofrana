<?php
require("../lib/common.php");

function display_planet_in_system($planet) {
  $planet->load();
  echo "<tr";
  if ($planet->has_owner() && ($planet->get_owner_id() == $_SESSION['player']->get_player_id())
    ) {
    echo " class='me'";
  }
  echo ">\n";
  echo "<td";
  if ($planet->is_bonus()) {
    echo " class='bonus_planet'";
  }
  echo ">".$planet->get_position()."</td>\n";
  if ($planet->has_owner()) {
    echo "<td><a href='profile.php?player_id=".$planet->get_owner_id()."'>".player_id_to_name($planet->get_owner_id())."</a></td>\n";
    echo "<td>".$planet->get_population_level()."</td>\n";
    echo "<td>".$planet->get_building_level("starbase")."</td>\n";
    echo "<td>".($planet->has_sieging_fleet()?"<a class=sieged>sieged</a>":"")."</td>\n";
  }
  else {
    echo "<td colspan=3>F r e e &nbsp;&nbsp; p l a n e t</td>\n";
    echo "<td>".($planet->has_sieging_fleet()?"<a class=sieged>sieged</a>":"")."</td>\n";
  }
  echo "</tr>\n";
}

function display_system($sid) {
  $list = $_SESSION['player']->list_visible_systems();
  if (!array_key_exists($sid, $list)) {
    echo "Your biology level does not allow you to see this system.<br>\n";
    return;
  }
  
  $s = $list[$sid];
  $s->load_planets();
  echo <<<EOL
<div class="tab" id="system_view_tab">
<h1>System details</h1>
EOL;
  echo "<table class='system_view'><tr><td colspan=5 id='title' class='system_view'>System ".$s->get_name()."<br>(sid=".$s->get_sid().", x=".$s->get_x().", y= ".$s->get_y().")</td></tr>\n";
  echo "<tr id='description' class='system_view'><td>Position</td><td>Owner</td><td>Population</td><td>Starbase</td><td></td></tr>\n";
  foreach ($s->get_planets() as $p) {
    display_planet_in_system($p);
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
  if (isset($_GET['sid'])) {
    display_system($_GET['sid']);
  }
}
build_footer();

?> 
