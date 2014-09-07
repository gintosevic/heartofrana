<?php

class Fleet {
  
  static $ALL_SHIPS = array("colonyships", "transports", "destroyers", "cruisers", "battleships");
  
  private $fleet_id;
  private $owner;
  private $ships;

  function __construct($id=null) {
    $this->fleet_id = $id;
    $this->owner = null;
    $this->ships = array();
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = 0;
    }
  }
  
  function load() {
    $result = db_query("SELECT * FROM Fleet WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->owner = $row['owner'];
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = $row[$ship];
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
      $result = db_query("INSERT INTO Fleet VALUES($id, ".$this->owner.", ".$this->ships['colonyships'].", ".$this->ships['transports'].", ".$this->ships['destroyers'].", ".$this->ships['cruisers'].", ".$this->ships['battleships'].") ON DUPLICATE KEY UPDATE owner = ".$this->owner.", colonyships = ".$this->ships['colonyships'].", transports = ".$this->ships['transports'].", destroyers = ".$this->ships['destroyers'].", cruisers = ".$this->ships['cruisers'].", battleships = ".$this->ships['battleships']);
    }
    if ($this->fleet_id === null) {
      $this->fleet_id = db_last_insert_id();
    }
  }
  
  function get_fleet_id() {
    return $this->fleet_id;
  }
  
  function get_owner() {
    return $this->owner;
  }
  
  function set_owner($player_id) {
    $this->owner = $player_id;
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
  
  function land_on_planet(Planet $planet) {
    if (!$planet->has_owner_fleet() && !$planet->has_sieging_fleet()) {
      if ($planet->get_owner() === $this->get_owner()) {
	$planet->set_owner_fleet($this);
      }
    }
  }
  
  function merge(Fleet $fleet) {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->set_ships($ship, $this->get_ship($ship) + $fleet->get_ship($ship));
      $fleet->set_ship(0);
    }
  }
  
}

?>
