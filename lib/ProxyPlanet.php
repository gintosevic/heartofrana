<?php

/**
 * Class to model planets
 */
class ProxyPlanet extends Planet {

  protected $planet;

  public function __construct($s, $p) {
    parent::__construct();
    $this->planet = ProxyPlanet::get_cached_or_new($s, $p);
  }

  public function replace(Planet $planet) {
    $this->planet->replace($planet);
  }

  public function save() {
    $this->planet->save();
  }

  public function load() {
    $this->planet->load();
  }

  public function load_owner_fleet() {
    $this->planet->load_owner_fleet();
  }

  public function load_sieging_fleet() {
    $this->planet->load_sieging_fleet();
  }

  public function get_owner_ships($type) {
    return $this->planet->get_owner_ships($type);
  }

  public function get_sieging_ships($type) {
    return $this->planet->get_sieging_ships($type);
  }

  public function get_sid() {
    return $this->planet->get_sid();
  }

  public function get_position() {
    return $this->planet->get_position();
  }

  public function set_bonus($bool) {
    $this->planet->set_bonus($bool);
  }

  public function is_bonus() {
    return $this->planet->is_bonus();
  }

  public function set_building_points($type, $n) {
    $this->planet->set_building_points($type, $n);
  }

  public function set_ship_points($type, $n) {
    $this->planet->set_ship_points($type, $n);
  }

  public function add_building_points($type, $n) {
    $this->planet->add_building_points($type, $n);
  }

  public function add_ship_points($type, $n) {
    $this->planet->add_ship_points($type, $n);
  }

  public function get_building_points($type) {
    return $this->planet->get_building_points($type);
  }

  public function get_ship_points($type) {
    return $this->planet->get_ship_points($type);
  }

  public function get_building_level($type) {
    return $this->planet->get_building_level($type);
  }

  public function decrease_building_level($type) {
    return $this->planet->decrease_building_level($type);
  }

  public function set_building_level($type, $n) {
    $this->planet->set_building_level($type, $n);
  }

  public function get_starbase_defense_value() {
    return $this->planet->get_starbase_defense_value();
  }

  public function get_defense_value() {
    return $this->planet->get_defense_value();
  }

  public function set_population_points($n) {
    $this->planet->set_population_points($n);
  }

  public function set_population_level($n) {
    $this->planet->set_population_level($n);
  }

  public function get_population_points() {
    return $this->planet->get_population_points();
  }

  public function get_population_level() {
    return $this->planet->get_population_level();
  }

  public function set_production_points($n) {
    $this->planet->set_production_points($n);
  }

  protected function substract_production_points($n) {
    $this->planet->substract_production_points($n);
  }

  public function get_production_points() {
    return $this->planet->get_production_points();
  }

  public function upgrade_building($type, $amount) {
    $this->planet->upgrade_building($type, $amount);
  }

  public function upgrade_ship($type, $amount) {
    $this->planet->upgrade_ship($type, $amount);
  }
  
  public function load_owner() {
    $this->planet->load_owner();
  }


  public function get_owner_id() {
    return $this->planet->get_owner_id();
  }
  
  public function get_owner() {
    return $this->planet->get_owner();
  }

  public function has_owner() {
    return $this->planet->has_owner();
  }

  public function set_owner_id($player_id) {
    $this->planet->set_owner_id($player_id);
  }

  public function set_owner(Player $pl) {
    $this->planet->set_owner($pl);
  }

  public function has_owner_fleet() {
    return $this->planet->has_owner_fleet();
  }

  public function has_sieging_fleet() {
    return $this->planet->has_sieging_fleet();
  }

  public function get_owner_fleet_id() {
    return $this->planet->get_owner_fleet_id();
  }

  public function get_sieging_fleet_id() {
    return $this->planet->get_sieging_fleet_id();
  }

  public function get_owner_fleet() {
    return $this->planet->get_owner_fleet();
  }

  public function get_sieging_fleet() {
    return $this->planet->get_sieging_fleet();
  }

  public function set_owner_fleet_id($fleet_id) {
    $this->planet->set_owner_fleet_id($fleet_id);
  }

  public function set_owner_fleet(Fleet $fleet) {
    $this->planet->set_owner_fleet($fleet);
  }

  public function unset_owner_fleet() {
    $this->planet->unset_owner_fleet();
  }

  public function set_sieging_fleet_id($fleet_id) {
    $this->planet->set_sieging_fleet_id($fleet_id);
  }

  public function set_sieging_fleet(Fleet $fleet) {
    $this->planet->set_sieging_fleet($fleet);
  }

  public function unset_sieging_fleet() {
    $this->planet->unset_sieging_fleet();
  }

  // Cache

  protected static $cache;

  static protected function is_cached($sid, $position) {
    if (!isset(self::$cache)) {
      self::$cache = array();
    }
    return array_key_exists("$sid:$position", self::$cache);
  }

  static protected function get_cached($sid, $position) {
    if (!isset(self::$cache)) {
      self::$cache = array();
    }
    if (array_key_exists("$sid:$position", self::$cache)) {
//      echo "Get cached $sid $position<br>\n";
//      print_r(self::$cache["$sid:$position"]) . "<br><\n";
//      echo "<br>++<br>\n";
//      print_r(self::$cache)."<br>\n";
      return self::$cache["$sid:$position"];
    } else {
//      echo "Get cached $sid $position = null<br>\n";
      return null;
    }
  }

  static protected function set_cached(Planet $planet) {
//    echo "Set cached " . $planet->get_sid() . " " . $planet->get_position() . " = ";
//    print_r($planet);
//    echo "<br>\n";
    self::$cache[$planet->get_sid() . ":" . $planet->get_position()] = $planet;
    ProxyPlanet::get_cached($planet->get_sid(), $planet->get_position());
  }

  static protected function get_cached_or_new($sid, $position) {
    if (!isset(self::$cache)) {
      self::$cache = array();
    }
//    print "Get cached ($sid, $position)<br>\n";
//    print "++++++<br><pre>\n";
//    print_r(ProxyPlanet::$cache);
//    print "</pre>------<br>\n";
//    print "<br>\n";
    if (ProxyPlanet::is_cached($sid, $position)) {
      return ProxyPlanet::get_cached($sid, $position);
    } else {
      $planet = new RealPlanet($sid, $position);
      ProxyPlanet::set_cached($planet);
      return $planet;
    }
  }

  static protected function save_cache() {
    foreach (self::$cache as $planet) {
      $planet->save();
    }
  }

  static protected function flush_cache() {
    self::$cache = array();
  }

}

?>
