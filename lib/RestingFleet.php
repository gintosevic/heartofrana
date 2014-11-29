<?php

/**
 * Resting fleet
 */

class RestingFleet extends Fleet {
  private $sid;
  private $position;
  private $planet;
  
  public function __construct($sid, $position, $fleet_id=null) {
    parent::__construct($fleet_id);
    $this->sid = $sid;
    $this->position = $position;
    $this->planet = null;
  }
  
  public function get_sid() { return $this->sid; }
  
  public function get_position() { return $this->position; }
  
  public function load_planet() {
    $this->planet = new Planet($this->sid, $this->position);
    $this->planet->load();
  }
  
  function unset_planet() {
    if ($this->planet != null) {
      $this->planet->unset_owner_fleet();
    }
    $this->planet = null;
  }
  
  public function destroy() {
    if ($this->planet != null) {
      $planet = $this->get_planet();
      $this->unset_planet();
      $planet->save();
    }
    parent::destroy();
  }
  
  public function get_planet() {
    if ($this->planet === null) {
      $this->load_planet();
    }
    return $this->planet;
  }
  
  public function set_planet(Planet $planet) {
    if ($planet->get_owner_id() === $this->get_owner_id()) {
      $this->planet = $planet;
      $planet->set_owner_fleet($this);
    }
    else {
      throw new Exception("Owner of the resting fleet and of the planet must be the same.");
    }
  }
  
  public function launch(Planet $target) {
    if (!$this->get_owner()->can_see_planet($target)) {
      throw new Exception("Planet ".$target->to_html()." does not exist or is not currently visible");
    }
    $duration = 10;
    $flying_fleet = new FlyingFleet($this->fleet_id);
    $flying_fleet->copy($this);
    $flying_fleet->set_departure_planet($this->get_planet());
    $flying_fleet->set_departure_time(time());
    $flying_fleet->set_arrival_planet($target);
    $flying_fleet->set_arrival_time(time()+$duration);
    $this->destroy();
    return $flying_fleet;
  }
  
}
