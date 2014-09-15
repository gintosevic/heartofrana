<?php

/********************************************************************
 * Generic fleet
 ********************************************************************/

class Fleet {
  
  static $ALL_SHIPS = array("colonyships", "transports", "destroyers", "cruisers", "battleships");
  
  protected $fleet_id;
  protected $owner_id;
  protected $owner;
  protected $ships;

  function __construct($id=null) {
    $this->fleet_id = $id;
    $this->owner_id = null;
    $this->owner = null;
    $this->ships = array();
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = 0;
    }
  }
  
  function copy(Fleet $f) {
    $this->set_fleet_id($f->get_fleet_id());
    $this->set_owner_id($f->get_owner_id());
    if ($f->owner !== null) {
      $this->owner = $f->owner;
    }
    $this->merge($f);
  }
  
  function load() {
    $result = db_query("SELECT * FROM Fleet WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->owner_id = $row['owner'];
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = $row[$ship];
    }
  }
  
  function load_owner() {
    if ($this->owner_id !== null) {
      $this->owner = new Player($this->owner_id);
    }
  }
  
  function save() {
    if ($this->fleet_id === null) {
      $id = "DEFAULT";
    }
    else {
      $id = $this->fleet_id;
    }
    if ($this->fleet_id !== null && $this->is_empty()) {
      $result = db_query("DELETE FROM Fleet WHERE fleet_id = $id");
    }
    else {
      $result = db_query("INSERT INTO Fleet VALUES($id, ".$this->owner_id.", ".$this->ships['colonyships'].", ".$this->ships['transports'].", ".$this->ships['destroyers'].", ".$this->ships['cruisers'].", ".$this->ships['battleships'].") ON DUPLICATE KEY UPDATE owner = ".$this->owner_id.", colonyships = ".$this->ships['colonyships'].", transports = ".$this->ships['transports'].", destroyers = ".$this->ships['destroyers'].", cruisers = ".$this->ships['cruisers'].", battleships = ".$this->ships['battleships']);
    }
    if ($this->fleet_id === null) {
      $this->fleet_id = db_last_insert_id();
    }
  }
  
  function get_fleet_id() {
    return $this->fleet_id;
  }
  
  function get_owner_id() {
    return $this->owner_id;
  }
  
  function set_owner_id($player_id) {
    $this->owner_id = $player_id;
  }
  
  function get_owner() {
    return $this->owner;
  }
  
  function set_owner(Player $player) {
    $this->owner = $player;
    $this->owner_id = $player->get_player_id();
  }
  
  function set_ships($field, $n) {
    if (array_search($field, Fleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $field."); }
    $this->ships[$field] = $n;
  }
  
  function get_ships($field) {
    if (array_search($field, Fleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $field."); }
    return $this->ships[$field];
  }
  
  function is_empty() {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($this->get_ships($ship) > 0) { return false; }
    }
    return true;
  }
  
  function merge(Fleet $fleet) {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->set_ships($ship, $this->get_ships($ship) + $fleet->get_ships($ship));
      $fleet->set_ships($ship, 0);
    }
  }
  
  function to_html() {
    $str = "<span class='fleet'>\n<ul>\n";
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($this->ships[$ship] > 0) {
	$str .= "<li>".$this->ships[$ship]." ".($this->ships[$ship] > 1?$ship:substr($ship, 0, -1))."</li>\n";
      }
    }
    $str .= "</ul>\n</span>\n";
    return $str;
  }
  
}

/********************************************************************
 * Resting fleet
 ********************************************************************/

class RestingFleet extends Fleet {
  private $sid;
  private $position;
  private $planet;
  
  function __construct($sid, $position, $fleet_id=null) {
    parent::__construct($fleet_id);
    $this->sid = $sid;
    $this->position = $position;
    $this->planet = null;
  }
  
  function get_sid() { return $this->sid; }
  
  function get_position() { return $this->position; }
  
  function load_planet() {
    $this->planet = new Planet($this->sid, $this->position);
    $this->planet->load();
  }
  
  function get_planet() {
    if ($this->planet === null) {
      $this->load_planet();
    }
    return $this->planet;
  }
  
  function set_planet(Planet $planet) {
    if ($planet->get_owner_id() === $this->get_owner_id()) {
      $this->planet = $planet;
      $planet->set_owner_fleet($this);
    }
    else {
      throw new Exception("Owner of the resting fleet and of the planet must be the same.");
    }
  }
  
  function launch(Planet $target) {
    $duration = 100;
    $this->planet->unset_owner_fleet();
    $flying_fleet = new FlyingFleet($this->fleet_id);
    $flying_fleet->copy($this);
    $flying_fleet->set_departure_planet($this->planet);
    $flying_fleet->set_departure_time(time());
    $flying_fleet->set_arrival_planet($target);
    $flying_fleet->set_arrival_time(time()+$duration);
    return $flying_fleet;
  }
  
}

/********************************************************************
 * Sieging fleet
 ********************************************************************/

class SiegingFleet extends RestingFleet {
  function set_planet(Planet $planet) {
    if ($planet->get_owner_id() !== $this->get_owner_id()) {
      $this->planet = $planet;
      $planet->set_sieging_fleet($this);
    }
    else {
      throw new Exception("Owner of the sieging fleet and of the planet must be different.");
    }
  }
}



?>
