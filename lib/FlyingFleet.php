<?php

/**
 * Flying fleet
 */
class FlyingFleet extends Fleet {

  private $departure_time;
  private $departure_sid;
  private $departure_position;
  private $arrival_time;
  private $arrival_sid;
  private $arrival_position;
  private $departure_planet;
  private $arrival_planet;

  public function __construct($id) {
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

  public function land() {
    $planet = $this->get_arrival_planet();
    $arrival_id = $planet->get_owner_id();
    $owner_id = $this->get_owner_id();
    $battle = null;
    $resulting_fleet = null;
    // Prepare for fight against a sieging fleet
    if ($planet->has_sieging_fleet()) {
      echo "Battle with sieging fleet.\n";
      $sieging_fleet = $planet->get_sieging_fleet();
      $battle = new Battle($sieging_fleet, $this);
    }
    // Prepare for fight against a planet
    elseif (!$planet->has_sieging_fleet() && $planet->has_owner() && $owner_id != $arrival_id) {
      $battle = new Battle($planet, $this);
    }

    // Fight and land if battle is won
    if ($battle !== null) {
      echo $battle->to_string() . "\n";
      $battle->simulate();
      $battle->print_results();
      $resulting_fleet = $battle->apply_results();
    }
    // Simply land if no battle
    else {
      $resulting_fleet = $this->perform_landing();
    }
    // Update the database
    if (!($resulting_fleet === null)) {
      $resulting_fleet->save();
    }
    $planet->save();
    $this->destroy();
    return $resulting_fleet;
  }

  public function perform_landing() {
    $planet = $this->get_arrival_planet();
    if ($this->get_owner_id() === $planet->get_owner_id()) {
      if ($planet->has_owner_fleet()) {
        $owner_fleet = $planet->get_owner_fleet();
        $owner_fleet->merge($this);
        return $owner_fleet;
      } else {
        return $this->convert_to_resting_fleet();
      }
    } else {
      $n_transports = $this->get_ships('transports');
      $n_pop = $planet->get_population_level();
      if ($n_transports >= $n_pop) {
        $planet->set_owner_id($this->get_owner_id());
        return $this->convert_to_resting_fleet();
      } else {
        $planet->set_population_level($n_pop - $n_transports);
        return $this->convert_to_sieging_fleet();
      }
    }
    return null;
  }

  protected function convert_to_resting_fleet() {
    if (!$this->is_empty()) {
      $planet = $this->get_arrival_planet();
      $f = new RestingFleet($this->get_arrival_sid(), $this->get_arrival_position());
      $f->replace($this);
      $f->set_planet($this->get_arrival_planet());
      $f->save(); // This will provide a new fleet ID
      $planet->set_owner_fleet($f);
      return $f;
    } else {
      return null;
    }
  }

  protected function convert_to_sieging_fleet() {
    if (!$this->is_empty()) {
      $planet = $this->get_arrival_planet();
      $f = new SiegingFleet($this->get_arrival_sid(), $this->get_arrival_position());
      $f->replace($this);
      $f->set_planet($planet);
      $f->save(); // This will provide a new fleet ID
      $planet->set_sieging_fleet($f);
      return $f;
    } else {
      return null;
    }
  }

  public function load() {
    parent::load();
    $result = db_query("SELECT * FROM Flight WHERE fleet_id = " . $this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->departure_time = strtotime($row['departure_time']);
    $this->departure_sid = $row['departure_sid'];
    $this->departure_position = $row['departure_position'];
    $this->arrival_time = strtotime($row['arrival_time']);
    $this->arrival_sid = $row['arrival_sid'];
    $this->arrival_position = $row['arrival_position'];
  }

  public function save() {
    parent::save();
    db_query("INSERT INTO Flight VALUES(" . $this->fleet_id . ", FROM_UNIXTIME(" . $this->departure_time . "), " . $this->departure_sid . ", " . $this->departure_position . ", FROM_UNIXTIME(" . $this->arrival_time . "), " . $this->arrival_sid . ", " . $this->arrival_position . ")");

    if (!($this->get_owner_id() === $this->get_arrival_planet()->get_owner_id()) && $this->get_arrival_planet()->has_owner()) {
      $owner_str = $this->get_owner()->get_name();
      Event::create_and_save($this->get_arrival_planet()->get_owner_id(), "enemy_attack", "Attack from $owner_str", "An incoming fleet from " . $this->get_owner()->to_html() . " has been detected. The target planet is " . $this->get_arrival_planet()->to_html() . ". Below the estimation of the incoming fleet:<br>" . $this->to_html());
    }
  }

  public function destroy() {
    if (!($this->fleet_id === null)) {
      db_query("DELETE FROM Flight WHERE fleet_id = " . $this->fleet_id);
      parent::destroy();
    }
  }

  public function load_departure_planet() {
    $this->departure_planet = new ProxyPlanet($this->departure_sid, $this->departure_position);
    $this->departure_planet->load();
  }

  public function get_departure_planet() {
    if ($this->departure_planet == null && $this->departure_sid !== null && $this->departure_position !== null) {
      $this->load_departure_planet();
    }
    return $this->departure_planet;
  }

  public function set_departure_planet(Planet $planet) {
    $this->departure_planet = $planet;
    $this->departure_sid = $planet->get_sid();
    $this->departure_position = $planet->get_position();
  }

  public function load_arrival_planet() {
    $this->arrival_planet = new ProxyPlanet($this->arrival_sid, $this->arrival_position);
    $this->arrival_planet->load();
  }

  public function get_arrival_planet() {
    if ($this->arrival_planet == null && $this->arrival_sid !== null && $this->arrival_position !== null) {
      $this->load_arrival_planet();
    }
    return $this->arrival_planet;
  }

  public function set_arrival_planet(Planet $planet) {
    $this->arrival_planet = $planet;
    $this->arrival_sid = $planet->get_sid();
    $this->arrival_position = $planet->get_position();
  }

  public function get_departure_time() {
    return $this->departure_time;
  }

  public function get_departure_sid() {
    return $this->departure_sid;
  }

  public function get_departure_position() {
    return $this->departure_position;
  }

  public function set_departure_time($t) {
    $this->departure_time = $t;
  }

  public function set_departure_sid($s) {
    $this->departure_sid = $s;
  }

  public function set_departure_position($p) {
    $this->departure_position = $p;
  }

  public function get_arrival_time() {
    return $this->arrival_time;
  }

  public function get_arrival_sid() {
    return $this->arrival_sid;
  }

  public function get_arrival_position() {
    return $this->arrival_position;
  }

  public function set_arrival_time($t) {
    $this->arrival_time = $t;
  }

  public function set_arrival_sid($s) {
    $this->arrival_sid = $s;
  }

  public function set_arrival_position($p) {
    $this->arrival_position = $p;
  }

  public function get_fleet_id() {
    return $this->fleet_id;
  }

//  public function set_fleet_id($f_id) {
//    $this->fleet_id = $f_id;
//  }

  public function load_fleet() {
    if (isset($this->fleet_id)) {
      $this->fleet = new Fleet($this->fleet_id);
      $this->fleet->load();
    }
  }

}

?>
