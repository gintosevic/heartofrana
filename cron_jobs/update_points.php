<?php
require("lib/common.php");

function update_points_one_player(Player $player) {
  print "Updating player ".$player->get_name()."<br>\n";
}

function update_points_all_players() {
  $results = db_query("SELECT player_id FROM Player");
  $n_players = db_num_rows($results);
  for ($i = 0; $i < $n_players; $i++) {
    $row = db_fetch_row($results, $i);
    $player = new Player($row['player_id']);
    $player->load();
    $player->increment_points();
    $player->save();
  }
}


build_header('basic/basic.css');
update_points_all_players();
build_footer();
?>