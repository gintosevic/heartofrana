<?php

/********************************************************************
 * Flying fleet
 ********************************************************************/

class FlyingFleet extends Fleet {

  private $departure_time;
  private $departure_sid;
  private $departure_position;
  private $arrival_time;
  private $arrival_sid;
  private $arrival_position;
  private $departure_planet;
  private $arrival_planet;
  
  function __construct($id) {
    parent::__construct($id);
    $this->departure_time = null;
    $this->departure_sid = null;
    $this->departure_position = null;
    $this->arrival_time = null;
    $this->arrival_sid = null;
    $this->arrival_position = null;
    $this->departure_planet = null;
    $this->arrival_planet = null;
  }

  function load() {
    parent::load();
    $result = db_query("SELECT * FROM Flight WHERE fleet_id = ".$this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->departure_time = strtotime($row['departure_time']);
    $this->departure_sid = $row['departure_sid'];
    $this->departure_position = $row['departure_position'];
    $this->arrival_time = strtotime($row['arrival_time']);
    $this->arrival_sid = $row['arrival_sid'];
    $this->arrival_position = $row['arrival_position'];
  }

  function save() {
    parent::save();
    $result = db_query("INSERT INTO Flight VALUES(".$this->fleet_id.", FROM_UNIXTIME(".$this->departure_time."), ".$this->departure_sid.", ".$this->departure_position.", FROM_UNIXTIME(".$this->arrival_time."), ".$this->arrival_sid.", ".$this->arrival_position.")");
  }
  
  function load_departure_planet() {
    $this->departure_planet = new Planet($this->departure_sid, $this->departure_position);
    $this->departure_planet->load();
  }
  
  function get_departure_planet() {
    if ($this->departure_planet == null && $this->departure_sid !== null && $this->departure_position !== null) {
      $this->load_departure_planet();
    }
    return $this->departure_planet;
  }
  
  function set_departure_planet(Planet $planet) {
    $this->departure_planet = $planet;
    $this->departure_sid = $planet->get_sid();
    $this->departure_position = $planet->get_position();
  }
  
  function load_arrival_planet() {
    $this->arrival_planet = new Planet($this->arrival_sid, $this->arrival_position);
    $this->arrival_planet->load();
  }
  
  function get_arrival_planet() {
    if ($this->arrival_planet == null && $this->arrival_sid !== null && $this->arrival_position !== null) {
      $this->load_arrival_planet();
    }
    return $this->arrival_planet;
  }
  
  function set_arrival_planet(Planet $planet) {
    $this->arrival_planet = $planet;
    $this->arrival_sid = $planet->get_sid();
    $this->arrival_position = $planet->get_position();
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
