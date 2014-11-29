<?php

require("../lib/common.php");


function build_map() {
  $BLOCK_SIZE = 30;
  
  // List systems where the current player owns planets
  $list = $_SESSION['player']->list_visible_systems();
  
  // List systems which can be seen from these home systems
  
  $min_x = 10000;
  $min_y = 10000;
  $max_x = -10000;
  $max_y = -10000;
  foreach ($list as $sid => $s) {
    $min_x = min($min_x, $s->get_x());
    $min_y = min($min_y, $s->get_y());
    $max_x = max($max_x, $s->get_x());
    $max_y = max($max_y, $s->get_y());
  }
  $map_w = ($max_x-$min_x+5)*$BLOCK_SIZE;
  $map_h = ($max_y-$min_y+5)*$BLOCK_SIZE;
  $middle_x = ($max_x + $min_x)/2.0;
  $middle_y = ($max_y + $min_y)/2.0;
  echo "<div class=\"tab\" id=\"map_tab\"><h1>Map</h1>\n";
  echo "<div id=\"map\" style=\"width: ${map_w}px; height: ${map_h}px;\">\n";
  foreach ($list as $s) {
    echo "<div style=\"position: relative; left: 50%; top: 50%;\">";
    echo "<div class=\"system\" style=\"position: absolute; bottom: ".(($s->get_y() - $middle_y)*$BLOCK_SIZE)."px; left: ".(($s->get_x() - $middle_x)*$BLOCK_SIZE)."px;\">\n";
    echo "<a href='view_system.php?sid=".$s->get_sid()."' alt='".$s->get_name()." (".$s->get_x().",".$s->get_y().")'><img src='img/stars/star.png'>".$s->get_sid()."</a>\n";
    echo "</div>";
    echo "</div>\n";
  }
  echo "</div>\n";
  echo "</div>\n";
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
  build_menu();
  build_map();
}
build_footer();

