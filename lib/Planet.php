<?php

/**
 * Class to model planets
 */
class Planet extends Fightable {
  
  static $ALL_BUILDINGS = array("farm", "factory", "cybernet", "lab", "starbase");
  
  protected $sid;
  protected $position;
  protected $bonus;
  protected $population_points;
  protected $building_points;
  protected $ship_points;
  protected $production_points;
  protected $owner_fleet_id;
  protected $owner_fleet;
  protected $sieging_fleet_id;
  protected $sieging_fleet;


  public function __construct($s, $p) {
    parent::__construct();
    $this->sid = $s;
    $this->position = $p;
    $this->bonus = false;
    $this->population_points = 0;
    $this->production_points = 0;
    $this->building_points = array();
    foreach (Planet::$ALL_BUILDINGS as $b) {
      $this->building_points[$b] = 0;
    }
    $this->ship_points = array();
    foreach (AbstractFleet::$ALL_SHIPS as $s) {
      $this->ship_points[$s] = 0;
    }
    $this->owner_fleet_id = null;
    $this->owner_fleet = null;
    $this->sieging_fleet_id = null;
    $this->sieging_fleet = null;
//     echo "Planet sid=".$this->sid.", position=".$this->position."<br>\n";
  }
  
  public function save() {
    $building_update_attr = array();
    foreach (Planet::$ALL_BUILDINGS as $b) {
      array_push($building_update_attr, "$b = ".$this->get_building_points($b));
    }
    $ship_update_attr = array();
    foreach (AbstractFleet::$ALL_SHIPS as $s) {
      array_push($ship_update_attr, "$s = ".$this->get_ship_points($s));
    }
    $result = db_query("INSERT INTO Planet VALUES(".$this->sid.", ".$this->position.", ".($this->bonus?1:0).", ".$this->population_points.", ".join(", ", $this->building_points).", ".join(", ", $this->ship_points).", ".$this->production_points.", ".($this->owner_id === null?"NULL":$this->owner_id) .", ".($this->owner_fleet_id === null?"NULL":$this->owner_fleet_id).", ".($this->sieging_fleet_id === null?"NULL":$this->sieging_fleet_id).")
    ON DUPLICATE KEY UPDATE bonus = ".($this->bonus?1:0).", population = ".$this->population_points.", ".join(", ", $building_update_attr).", ".join(", ", $ship_update_attr).", production = ".$this->production_points.", owner = ".($this->owner_id === null?"NULL":$this->owner_id) .", owner_fleet = ".($this->owner_fleet_id === null?"NULL":$this->owner_fleet_id).", sieging_fleet = ".($this->sieging_fleet_id === null?"NULL":$this->sieging_fleet_id));
  }
  
  public function load() {
    $s = $this->sid;
    $p = $this->position;
    $result = db_query("SELECT * FROM Planet WHERE sid = '$s' AND position = '$p';");
    if (!$result || db_num_rows($result) == 0) {
      throw new Exception("No planet SID=$s position=$p.");
    }
    $row = db_fetch_assoc($result);
    $this->bonus = ($row['bonus'] == 1);
    $this->population_points = $row['population'];
    $this->population_level = population_points_to_level($row['population']);
    foreach (Planet::$ALL_BUILDINGS as $b) {
      $this->building_points[$b] = $row[$b];
    }
    foreach (AbstractFleet::$ALL_SHIPS as $s) {
      $this->ship_points[$s] = $row[$s];
    }
    $this->production_points = $row['production'];
    $this->owner_id = $row['owner'];
    $this->owner_fleet_id = $row['owner_fleet'];
    $this->owner_fleet = null;
    $this->sieging_fleet_id = $row['sieging_fleet'];
    $this->sieging_fleet = null;
  }
  
  public function load_owner_fleet() {
    if ($this->owner_fleet_id !== null) {
      $this->owner_fleet = new RestingFleet($this->sid, $this->position, $this->owner_fleet_id);
      $this->owner_fleet->load();
    }
  }
  
  public function load_sieging_fleet() {
    if ($this->sieging_fleet_id !== null) {
      $this->sieging_fleet = new SiegingFleet($this->sid, $this->position, $this->sieging_fleet_id);
      $this->sieging_fleet->load();
    }
  }
  
  public function get_owner_ships($type) {
    if (array_search($type, AbstractFleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    if ($this->owner_fleet === null) { return 0; }
    else { return $this->owner_fleet->get_ships($type); }
  }
  
  public function get_sieging_ships($type) {
    if (array_search($type, AbstractFleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    if ($this->sieging_fleet === null) { return 0; }
    else { return $this->sieging_fleet->get_ships($type); }
  }
  
  public function get_sid() { return $this->sid; }
  
  public function get_position() { return $this->position; }
  
  public function set_bonus($bool) {
    $this->bonus = $bool;
  }
  
  public function is_bonus() {
    return $this->bonus;
  }
  
  public function set_building_points($type, $n) {
    if (array_search($type, Planet::$ALL_BUILDINGS) === false) { die(__FILE__ . ": line " . __LINE__.": No building called $type."); }
    $this->building_points[$type] = $n;
  }
  
  public function set_ship_points($type, $n) {
    if (array_search($type, AbstractFleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    if ($n <= 0) { return; }
    if (!$this->has_owner_fleet()) {
      $fleet = new RestingFleet($this->sid, $this->position);
      $fleet->set_owner_id($this->get_owner_id());
      $fleet->set_planet($this);
      $fleet->save();
      $this->set_owner_fleet($fleet);
      $this->get_owner()->add_fleet($fleet);
    }
    $fleet = $this->owner_fleet;
    $price = AbstractFleet::get_ship_price($type, $this->get_owner()->get_science_level("economy"));
    $n_ships = intval($n / $price);
    $remaining_points = $n % $price;
    $this->ship_points[$type] = $remaining_points;
    $fleet->add_ships($type, $n_ships);
    $this->ship_points[$type] = $remaining_points;
  }
  
  public function add_building_points($type, $n) {
    $this->set_building_points($type, $this->get_building_points($type) + $n);
  }
  
  public function add_ship_points($type, $n) {
    $this->set_ship_points($type, $this->get_ship_points($type) + $n);
  }
  
  public function get_building_points($type) {
    if (array_search($type, Planet::$ALL_BUILDINGS) === false) { die(__FILE__ . ": line " . __LINE__.": No building called $type."); }
    return $this->building_points[$type];
  }
  
  public function get_ship_points($type) {
    if (array_search($type, AbstractFleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    return $this->ship_points[$type];
  }
  
  public function get_building_level($type) {
    return building_points_to_level($this->get_building_points($type));
  }
  
  public function get_starbase_defense_value() {
    $level = $this->get_building_level('starbase');
    return starbase_level_to_defense_value($level);
  }
  
  public function get_defense_value() {
    $value = $this->get_starbase_defense_value();
    if ($this->has_owner_fleet()) {
      $value += $this->get_owner_fleet()->get_defense_value();
    }
    return $value;
  }
  
  public function set_population_points($n) {
    $this->population_points = $n;
  }
  
  public function get_population_points() {
    return $this->population_points;
  }
  
  public function get_population_level() {
    return population_points_to_level($this->get_population_points());
  }
  
  public function set_production_points($n) {
    $this->production_points = $n;
  }
  
  protected function substract_production_points($n) {
    $this->production_points -= $n;
  }
  
  public function get_production_points() {
    return $this->production_points;
  }
  
  public function upgrade_building($type, $amount) {
    if ($amount <= 0 || $this->has_sieging_fleet()) { return; }
    if ($amount <= $this->get_production_points()) {
      $this->add_building_points($type, $amount);
      $this->substract_production_points($amount);
    }
  }
  
  public function upgrade_ship($type, $amount) {
    if ($amount <= 0 || $this->has_sieging_fleet()) { return; }
    if ($amount <= $this->get_production_points()){
      $this->add_ship_points($type, $amount);
      $this->substract_production_points($amount);
    }
  }
  
  public function has_owner_fleet() {
    return ($this->owner_fleet_id !== null);
  }
  
  public function has_sieging_fleet() {
    return ($this->sieging_fleet_id !== null);
  }
  
  public function get_owner_fleet_id() {
    return $this->owner_fleet_id;
  }
  
  public function get_sieging_fleet_id() {
    return $this->sieging_fleet_id;
  }
  
  public function get_owner_fleet() {
    if ($this->owner_fleet_id !== null && $this->owner_fleet === null) {
      $this->load_owner_fleet();
    }
    return $this->owner_fleet;
  }
  
  public function get_sieging_fleet() {
    if ($this->sieging_fleet_id !== null && $this->sieging_fleet === null) {
      $this->load_sieging_fleet();
    }
    return $this->sieging_fleet;
  }
  
  public function set_owner_fleet_id($fleet_id) {
    $this->owner_fleet_id = $fleet_id;
  }
  
  public function set_owner_fleet(AbstractFleet $fleet) {
    $this->owner_fleet = $fleet;
    $this->owner_fleet_id = $fleet->get_fleet_id();
  }
  
  public function unset_owner_fleet() {
    $this->owner_fleet = null;
    $this->owner_fleet_id = null;
  }
  
  public function set_sieging_fleet_id($fleet_id) {
    $this->sieging_fleet_id = $fleet_id;
  }
  
  public function set_sieging_fleet(AbstractFleet $fleet) {
    $this->sieging_fleet = $fleet;
    $this->sieging_fleet_id = $fleet->get_fleet_id();
  }
  
  public function unset_sieging_fleet() {
    $this->sieging_fleet = null;
    $this->sieging_fleet_id = null;
  }
  
  public function to_string() {
    return "SID".$this->sid." #".$this->position;
  }
  
  public function to_html() {
    return "<a class='planet' href='view_planet.php?sid=".$this->get_sid()."&position=".$this->get_position()."'>".$this->to_string()."</a>";
  }
  
}

?>
