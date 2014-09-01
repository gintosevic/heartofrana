<?php

class Fleet {
  
  private $fleet_id;
  private $owner;
  private $colonyships;
  private $transports;
  private $destroyers;
  private $cruisers;
  private $battleships;

  function __construct($id=null) {
    $this->fleet_id = $id;
    $this->owner = null;
    $this->colonyships = 0;
    $this->transports = 0;
    $this->destroyers = 0;
    $this->cruisers = 0;
    $this->battleships = 0;
  }
  
  function load() {
    $result = db_query("SELECT * FROM Fleet WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->owner = $row['owner'];
    $this->colonyships = $row['colonyships'];
    $this->transports = $row['transports'];
    $this->destroyers = $row['destroyers'];
    $this->cruisers = $row['cruisers'];
    $this->battleships = $row['battleships'];
  }
  
  function save() {
    if ($this->fleet_id == null) {
      $id = "DEFAULT";
    }
    else {
      $id = $this->fleet_id;
    }
    $result = db_query("INSERT INTO Fleet VALUES($id, ".$this->colonyships.", ".$this->transports.", ".$this->destroyers.", ".$this->cruisers.", ".$this->battleships.") ON DUPLICATE KEY UPDATE owner = ".$this->owner.", colonyships = ".$this->colonyships.", transports = ".$this->transports.", destroyers = ".$this->destroyers.", cruisers = ".$this->cruisers.", battleships = ".$this->battleships);
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
  
  function get_colonyships() {
    return $this->colonyships;
  }
  
  function set_colonyships($n) {
    $this->colonyships = $n;
  }
  
  function get_transports() {
    return $this->transports;
  }
  
  function set_transports($n) {
    $this->transports = $n;
  }
  
  function get_destroyers() {
    return $this->destroyers;
  }
  
  function set_destroyers($n) {
    $this->destroyers = $n;
  }
  
  function get_cruisers() {
    return $this->cruisers;
  }
  
  function set_cruisers($n) {
    $this->cruisers = $n;
  }
  
  function get_battleships() {
    return $this->battleships;
  }
  
  function set_battleships($n) {
    $this->battleships = $n;
  }
}

?>
