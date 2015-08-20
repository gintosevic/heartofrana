<?php

/**
 * Resting fleet
 */
class RestingFleet extends Fleet {

  private $sid;
  private $position;
  private $planet;

  public function __construct($sid, $position, $fleet_id = null) {
    parent::__construct($fleet_id);
    $this->sid = $sid;
    $this->position = $position;
    $this->planet = null;
  }

  public function replace(Fleet $fleet) {
    parent::replace($fleet);
    if ($fleet instanceof RestingFleet) {
      $this->sid = $fleet->sid;
      $this->positiond = $fleet->position;
      $this->planet = $fleet->planet;
    }
  }

  public function get_sid() {
    return $this->sid;
  }

  public function get_position() {
    return $this->position;
  }

  public function load_planet() {
    $this->planet = new ProxyPlanet($this->sid, $this->position);
    $this->planet->load();
  }

  function unset_planet() {
//    echo "Unset owner fleet<br>\n";
    $this->get_planet()->unset_owner_fleet();
//    print_r($this->get_planet())."<br>\n";
    $this->planet = null;
  }

  public function destroy() {
    $planet = $this->get_planet();
    $this->unset_planet();
    $planet->save();
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
    } else {
      throw new Exception("Owner of the resting fleet (owner ID = " . $this->get_owner_id() . ") and of the planet (".$planet->to_string().", owner ID = " . $planet->get_owner_id() . ") must be the same.");
    }
  }

  public function substract(Fleet $fleet) {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->set_ships($ship, max(0, $this->get_ships($ship) - $fleet->get_ships($ship)));
    }
  }

  public function launch(Planet $target) {
    if (!$this->get_owner()->can_see_planet($target)) {
      throw new Exception("Planet " . $target->to_html() . " does not exist or is not currently visible");
    }
    $duration = 1;
    $flying_fleet = new FlyingFleet(null);
    $flying_fleet->replace($this);
    $flying_fleet->set_departure_planet($this->get_planet());
    $flying_fleet->set_departure_time(time());
    $flying_fleet->set_arrival_planet($target);
    $flying_fleet->set_arrival_time(time() + $duration);
    $this->destroy();
    return $flying_fleet;
  }

}
