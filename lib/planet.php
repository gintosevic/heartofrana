<?php

class Planet {
  
  static $ALL_BUILDINGS = array("farm", "factory", "cybernet", "lab", "starbase");
  
  private $sid;
  private $position;
  private $bonus;
  private $population_points;
  private $building_points;
  private $production_points;
  private $owner_id;
  private $owner;
  private $owner_fleet_id;
  private $owner_fleet;
  private $sieging_fleet_id;
  private $sieging_fleet;


  function __construct($s, $p) {
    $this->sid = $s;
    $this->position = $p;
    $this->bonus = false;
    $this->population_points = 0;
    $this->production_points = 0;
    $this->building_points = array();
    foreach (Planet::$ALL_BUILDINGS as $b) {
      $this->building_points[$b] = 0;
    }
    $this->owner_id = null;
    $this->owner = null;
    $this->owner_fleet_id = null;
    $this->owner_fleet = null;
    $this->sieging_fleet_id = null;
    $this->sieging_fleet = null;
//     echo "Planet sid=".$this->sid.", position=".$this->position."<br>\n";
  }
  
  function save() {
    $building_update_attr = array();
    foreach (Planet::$ALL_BUILDINGS as $b) {
      array_push($building_update_attr, "$b = ".$this->get_building_points($b));
    }
    $result = db_query("INSERT INTO Planet VALUES(".$this->sid.", ".$this->position.", ".($this->bonus?1:0).", ".$this->population_points.", ".join(", ", $this->building_points).", ".$this->production_points.", ".($this->owner_id === null?"NULL":$this->owner_id) .", ".($this->owner_fleet_id === null?"NULL":$this->owner_fleet_id).", ".($this->sieging_fleet_id === null?"NULL":$this->sieging_fleet_id).")
    ON DUPLICATE KEY UPDATE sid = ".$this->sid.", position = ".$this->position.", bonus = ".($this->bonus?1:0).", population = ".$this->population_points.", ".join(", ", $building_update_attr).", production = ".$this->production_points.", owner = ".($this->owner_id === null?"NULL":$this->owner_id) .", owner_fleet = ".($this->owner_fleet_id === null?"NULL":$this->owner_fleet_id).", sieging_fleet = ".($this->sieging_fleet_id === null?"NULL":$this->sieging_fleet_id));
  }
  
  function load() {
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
    $this->production_points = $row['production'];
    $this->owner_id = $row['owner'];
    $this->owner_fleet_id = $row['owner_fleet'];
    $this->owner_fleet = null;
    $this->sieging_fleet_id = $row['sieging_fleet'];
    $this->sieging_fleet = null;
  }
  
  function load_owner() {
    if ($this->owner_id !== null) {
      $this->owner = new Player($this->owner_id);
      $this->owner->load();
    }
  }
  
  function load_owner_fleet() {
    if ($this->owner_fleet_id !== null) {
      $this->owner_fleet = new Fleet($this->owner_fleet_id);
      $this->owner_fleet->load();
    }
  }
  
  function load_sieging_fleet() {
    if ($this->sieging_fleet_id !== null) {
      $this->sieging_fleet = new Fleet($this->sieging_fleet_id);
      $this->sieging_fleet->load();
    }
  }
  
  function get_owner_ships($type) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    if ($this->owner_fleet === null) { return 0; }
    else { return $this->owner_fleet->get_ships($type); }
  }
  
  function get_sieging_ships($type) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) { die(__FILE__ . ": line " . __LINE__.": No ship called $type."); }
    if ($this->sieging_fleet === null) { return 0; }
    else { return $this->sieging_fleet->get_ships($type); }
  }
  
  function get_sid() { return $this->sid; }
  
  function get_position() { return $this->position; }
  
  function set_owner(Player $pl) {
    if ($this->owner_id !== $pl->get_player_id()) {
      $this->owner_id = $pl->get_player_id();
      $this->owner = $pl;
      $pl->add_planet($this);
    }
  }
  
  function get_owner_id() {
      return $this->owner_id;
  }
  
  function has_owner() {
    return ($this->owner_id !== null);
  }
  
  function get_owner() {
    if ($this->has_owner() && $this->owner === null) {
      $this->owner = new Player($this->owner_id);
      $this->owner->load();
    }
    return false;
  }
  
  function set_bonus($bool) {
    $this->bonus = $bool;
  }
  
  function is_bonus() {
    return $this->bonus;
  }
  
  function set_building_points($field, $n) {
    if (array_search($field, Planet::$ALL_BUILDINGS) === false) { die(__FILE__ . ": line " . __LINE__.": No building called $field."); }
    $this->building_points[$field] = $n;
  }
  
  function get_building_points($field) {
    if (array_search($field, Planet::$ALL_BUILDINGS) === false) { die(__FILE__ . ": line " . __LINE__.": No building called $field."); }
    return $this->building_points[$field];
  }
  
  function get_building_level($field) {
    return building_points_to_level($this->get_building_points($field));
  }
  
  function set_population_points($n) {
    $this->population_points = $n;
  }
  
  function get_population_points() {
    return $this->population_points;
  }
  
  function get_population_level() {
    return population_points_to_level($this->get_population_points());
  }
  
  function set_production_points($n) {
    $this->production_points = $n;
  }
  
  function get_production_points() {
    return $this->production_points;
  }
  
  function has_owner_fleet() {
    return ($this->owner_fleet_id !== null);
  }
  
  function has_sieging_fleet() {
    return ($this->sieging_fleet_id !== null);
  }
  
  function get_owner_fleet_id() {
    return $this->owner_fleet_id;
  }
  
  function get_sieging_fleet_id() {
    return $this->sieging_fleet_id;
  }
  
  function get_owner_fleet() {
    if ($this->owner_fleet_id !== null && $this->owner_fleet === null) {
      $this->load_owner_fleet();
    }
    return $this->owner_fleet;
  }
  
  function get_sieging_fleet() {
    if ($this->sieging_fleet_id !== null && $this->sieging_fleet === null) {
      $this->load_sieging_fleet();
    }
    return $this->sieging_fleet;
  }
  
  function set_owner_fleet_id($fleet_id) {
    $this->owner_fleet_id = $fleet_id;
  }
  
  function set_owner_fleet(Fleet $fleet) {
    $this->owner_fleet = $fleet;
    $this->owner_fleet_id = $fleet->get_fleet_id();
  }
  
  function unset_owner_fleet() {
    $this->owner_fleet = null;
    $this->owner_fleet_id = null;
  }
  
  function set_sieging_fleet_id($fleet_id) {
    $this->sieging_fleet_id = $fleet_id;
  }
  
  function set_sieging_fleet(Fleet $fleet) {
    $this->sieging_fleet = $fleet;
    $this->sieging_fleet_id = $fleet->get_fleet_id();
  }
  
  function unset_sieging_fleet() {
    $this->sieging_fleet = null;
    $this->sieging_fleet_id = null;
  }
  
  function to_string() {
    return "SID".$this->sid." #".$this->position;
  }
  
  function to_html() {
    return "<a class='planet' href='view_planet.php?sid=".$this->get_sid()."&position=".$this->get_position()."'>".$this->to_string()."</a>";
  }
  
}

?>
