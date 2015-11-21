<?php

require '../lib/common.php';

session_start();

function get_event($event) {
  $event->load();
  return array(
      'type' => $event->type,
      'time' => $event->time,
      'title' => $event->title,
      'text' => $event->text
  );
}

function get_all_news() {
  $all_news = array();
  $player_id = $_SESSION['player']->get_player_id();
//  $results = db_query("SELECT event_id FROM Event WHERE player_id = $player_id ORDER BY event_id DESC");
//  $n = db_num_rows($results);
//  for ($i = 0; $i < $n; $i++) {
//    $row = db_fetch_assoc($results);
//    $event = new Event($row['event_id']);
//    array_push($all_news, get_event($event));
//  }
  $results = db_query("SELECT * FROM Event WHERE player_id = $player_id ORDER BY event_id DESC");
  $n = db_num_rows($results);
  for ($i = 0; $i < $n; $i++) {
    $row = db_fetch_assoc($results);
//    $timestamp = DateTime::createFromFormat('Y-m-j H:i:s', $row['time']);
//    $row['time'] = $timestamp->getTimestamp();
    $row['time'] = strtotime($row['time'])*1000;
    array_push($all_news, $row);
  }
  echo json_encode($all_news);
}

get_all_news();
?> 
