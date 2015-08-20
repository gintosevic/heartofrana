<?php
require("../lib/common.php");

function display_event($event) {
  $event->load();
  echo "<li class='news_".$event->type."'>\n";
  echo "<div class='news'>\n";
  echo "<div class='news_time'>".$event->time."</div>\n";
  echo "<div class='news_title'>".$event->title."</div>\n";
  echo "<div class='news_text'>".$event->text."</div>\n";
  echo "</div>\n";
  echo "</li>\n";
}

function build_news() {
  echo <<<EOL
<div class="tab" id="news_tab">
<h1>News</h1>
<ul class='news_list'>
EOL;
  $player_id = $_SESSION['player']->get_player_id();
  $results = db_query("SELECT event_id FROM Event WHERE player_id = $player_id ORDER BY event_id DESC");
  $n = db_num_rows($results);
  for ($i = 0; $i < $n; $i++) {
    $row = db_fetch_assoc($results);
    $event = new Event($row['event_id']);
    display_event($event);
  }
  echo "</ul>\n</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
  build_menu();
  build_news();
}
build_footer();

?> 
