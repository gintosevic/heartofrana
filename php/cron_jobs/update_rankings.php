<?php
require("lib/common.php");

function update_player_ranking(Player $player) {
  echo "<a class='todo'>Player ranking is not implemented yet.</a>\n";
}

function update_alliance_ranking() {
  echo "<a class='todo'>Alliance ranking is not implemented yet.</a>\n";
}


build_header('basic/basic.css');
update_player_ranking();
update_alliance_ranking();
build_footer();
?>