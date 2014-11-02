<?php

/**
 * Resting fleet
 */

class RestingFleet extends AbstractFleet {
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
