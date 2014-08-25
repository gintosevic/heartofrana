<?php

class Fleet {
  
  var $fleet_id;
  var $owner;
  var $colonyships;
  var $transports;
  var $destroyers;
  var $cruisers;
  var $battleships;

  function __construct($id=null) {
    $this->fleet_id = $id;
    $this->colonyships = 0;
    $this->transports = 0;
    $this->destroyers = 0;
    $this->cruisers = 0;
    $this->battleships = 0;
  }
  
  function load() {
    $result = db_query("SELECT * FROM Fleet WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
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
    $result = db_query("INSERT INTO Fleet VALUES($id, ".$this->colonyships.", ".$this->transports.", ".$this->destroyers.", ".$this->cruisers.", ".$this->battleships.") ON DUPLICATE KEY UPDATE");
  }
}

?>
