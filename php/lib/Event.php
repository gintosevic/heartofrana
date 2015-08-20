<?php

class Event {

  static $ALL_EVENT_TYPES = array("normal", "important", "enemy_attack", "allied_attack", "won_fight", "lost_fight", "new_planet", "lost_planet");

  var $event_id;
  var $player_id;
  var $time;
  var $type;
  var $title;
  var $text;
  var $new;

  function __construct($id=null) {
    $this->event_id = $id;
    $this->player_id = 0;
    $this->time = 0;
    $this->type = 0;
    $this->title = 0;
    $this->text = 0;
    $this->new = true;
  }

  function load() {
    $result = db_query("SELECT * FROM Event WHERE event_id = ".$this->event_id);
    $row = db_fetch_assoc($result);
    $this->player_id = $row['player_id'];
    $this->time = $row['time'];
    $this->type = $row['type'];
    $this->title = $row['title'];
    $this->text = $row['text'];
    $this->new = $row['new'];
  }

  function save() {
    $id = "DEFAULT";
    if ($this->event_id != null) {
      $id = $this->event_id;
    }
    $escaped_title = str_replace("'", "''", $this->title);
    $escaped_text = str_replace("'", "''", $this->text);
    $time_field = "";
    if ($this->time === null) {
      $time_field = "NOW()";
    }
    else {
      $time_field = "FROM_UNIXTIME(".$this->time.")";
    }
    db_query("INSERT INTO Event VALUES($id, ".$this->player_id.", $time_field, '".$this->type."', '$escaped_title', '$escaped_text', ".($this->new?1:0).")");
    $result = db_query("SELECT * FROM Event WHERE event_id = LAST_INSERT_ID()");
    $row = db_fetch_assoc($result);
    $this->event_id = $row['event_id'];
    if ($this->time === null) {
      $this->time = strtotime($row['time']);
    }
  }
  
  static function create_and_save($player_id, $type, $title, $text, $time=null) {
    if (!array_key_exists($type, Event::$ALL_EVENT_TYPES)) {
      $event = new Event();
      $event->player_id = $player_id;
      $event->type = $type;
      $event->title = $title;
      $event->text = $text;
      $event->time = $time;
      $event->save();
    }
    else {
      die("Invalid event type \"$type\".\n");
    }
  }
  
}

?>
