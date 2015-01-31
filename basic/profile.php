<?php
require("../lib/common.php");


function display_profile(Player $player) {
  if ($_SESSION['player']->get_player_id() != $player->get_player_id() && !$_SESSION['player']->can_see_player($player)) {
    throw new Exception("Information about player <b>".$player->get_name()." (ID ".$player->get_player_id().")</b> cannot be displayed.");
  }
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
  check_fleet_landing($_SESSION['player']);
  build_menu();
  try {
    $displayed_player = null;
    if (isset($_GET['player_id']) && $_SESSION['player']->get_player_id() != $_GET['player_id']) {
      $displayed_player = new Player($_GET['player_id']);
      $displayed_player->load();
    }
    else {
      $displayed_player = $_SESSION['player'];
    }
    display_profile($displayed_player);
  }
  catch (Exception $e) {
    print_error($e->getMessage());
  }
}
build_footer();

?> 
