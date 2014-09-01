<?php

class Flight {

  private $flight_id;
  private $departure_time;
  private $departure_sid;
  private $departure_position;
  private $arrival_time;
  private $arrival_sid;
  private $arrival_position;
  
  private $fleet_id;
  private $fleet;

  function __construct($id) {
    $this->fleet_id = $id;
    $this->departure_time = null;
    $this->departure_sid = 0;
    $this->departure_position = 0;
    $this->arrival_time = null;
    $this->arrival_sid = 0;
    $this->arrival_position = 0;
    $this->fleet_id = null;
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
    $this->fleet_id = $row['fleet_id'];
  }

  function save() {
    $result = db_query("INSERT INTO Flight VALUES(".$this->fleet_id.", ".$this->departure_time.", ".$this->departure_sid.", ".$this->departure_position.", ".$this->arrival_time.", ".$this->arrival_sid.", ".$this->arrival_position.")");
  }
  
  function get_departure_time() {
    return $this->departure_time;
  }
  
  function get_departure_sid() {
    return $this->departure_sid;
  }
  
  function get_departure_position() {
    return $this->departure_position;
  }
  
  function set_departure_time($t) {
    $this->departure_time = $t;
  }
  
  function set_departure_sid($s) {
    $this->departure_sid = $s;
  }
  
  function set_departure_position($p) {
    $this->departure_position = $p;
  }
  
  function get_arrival_time() {
    return $this->arrival_time;
  }
  
  function get_arrival_sid() {
    return $this->arrival_sid;
  }
  
  function get_arrival_position() {
    return $this->arrival_position;
  }
  
  function set_arrival_time($t) {
    $this->arrival_time = $t;
  }
  
  function set_arrival_sid($s) {
    $this->arrival_sid = $s;
  }
  
  function set_arrival_position($p) {
    $this->arrival_position = $p;
  }
  
  function get_fleet_id() {
    return $this->fleet_id;
  }
  
  function set_fleet_id($f_id) {
    $this->fleet_id = $f_id;
  }
  
  function load_fleet() {
    if (isset($this->fleet_id)) {
      $this->fleet = new Fleet($this->fleet_id);
      $this->fleet->load();
    }
  }

}

?>
