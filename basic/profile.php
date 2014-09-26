<?php
require("../lib/common.php");


function display_profile(Player $player) {
  echo <<<EOL
<div class="tab" id="profile_tab">
<h1>Profile</h1>
EOL;
  echo "<h2>".$player->get_name()."</h2>\n";
  echo "<ul>\n";
  echo "<li>Rank (player): ".$player->get_rank()."</li>\n";
  echo "<li>Experience points: ".$player->get_experience_points()." XPs</li>\n";
  echo "<li>Player level: ".$player->get_player_level()."</li>\n";
  $planets = $player->get_planets();
  echo "<li>Planets: ".count($planets)." / ".$player->get_culture_level()."</li>\n";
  echo "</ul>\n";
  echo "</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  build_menu();
  $player = null;
  echo "<div class='todo'>Visibility verification not implemented.</div>\n";
  if (isset($_GET['player_id'])) {
    $player = new Player($_GET['player_id']);
    $player->load();
  }
  else {
    $player = $_SESSION['player'];
  }
  display_profile($player);
}
build_footer();

?> 
