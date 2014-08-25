<?php

class Flight {

  var $flight_id;
  var $departure_time;
  var $departure_sid;
  var $departure_position;
  var $arrival_time;
  var $arrival_sid;
  var $arrival_position;
  
  var $fleet;

  function __construct($id=null) {
    $this->fleet_id = $id;
    $this->departure_time = 0;
    $this->departure_sid = 0;
    $this->departure_position = 0;
    $this->arrival_time = 0;
    $this->arrival_sid = 0;
    $this->arrival_position = 0;
    $this->fleet = null;
  }

  function load() {
    $result = db_query("SELECT * FROM Flight WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->departure_time = $row['departure_time'];
    $this->departure_sid = $row['departure_sid'];
    $this->departure_position = $row['departure_position'];
    $this->arrival_time = $row['arrival_time'];
    $this->arrival_sid = $row['arrival_sid'];
    $this->arrival_position = $row['arrival_position'];
    $this->fleet = new Fleet($fleet_id);
    $this->fleet->load();
  }

  function save() {
    if ($this->fleet_id == null) {
      $id = "DEFAULT";
    }
    else {
      $id = $this->fleet_id;
    }
    $result = db_query("INSERT INTO fleet VALUES($id, ".$this->departure_time.", ".$this->departure_sid.", ".$this->departure_position.", ".$this->arrival_time.", ".$this->arrival_sid.", ".$this->arrival_position.") ON DUPLICATE KEY UPDATE");
  }

}

?>
