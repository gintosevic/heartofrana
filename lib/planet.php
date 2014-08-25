<?php

class Planet {
  
  static $ALL_BUILDINGS = array("farm", "factory", "cybernet", "lab", "starbase");
  
  private $sid;
  private $position;
  private $bonus;
  private $population_points;
  private $building_points;
  private $production_points;
  private $owner;
  var $owner_fleet;
  var $sieging_fleet;


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
    $this->owner = null;
    $this->owner_fleet = null;
    $this->sieging_fleet = null;
//     echo "Planet sid=".$this->sid.", position=".$this->position."<br>\n";
  }
  
  function save() {
    $building_update_attr = array();
    foreach (Planet::$ALL_BUILDINGS as $b) {
      array_push($building_update_attr, "$b = ".$this->get_building_points($b));
    }
    $result = db_query("INSERT INTO Planet VALUES(".$this->sid.", ".$this->position.", ".($this->bonus?1:0).", ".$this->population_points.", ".join(", ", $this->building_points).", ".$this->production_points.", ".($this->owner == null?"NULL":$this->owner) .", ".($this->owner_fleet == null?"NULL":$this->owner_fleet).", ".($this->sieging_fleet == null?"NULL":$this->sieging_fleet).")
    ON DUPLICATE KEY UPDATE sid = ".$this->sid.", position = ".$this->position.", bonus = ".($this->bonus?1:0).", population = ".$this->population_points.", ".join(", ", $building_update_attr).", production = ".$this->production_points.", owner = ".($this->owner == null?"NULL":$this->owner) .", owner_fleet = ".($this->owner_fleet == null?"NULL":$this->owner_fleet).", sieging_fleet = ".($this->sieging_fleet == null?"NULL":$this->sieging_fleet));
  }
  
  function load() {
    $s = $this->sid;
    $p = $this->position;
    $result = db_query("SELECT * FROM Planet WHERE sid = '$s' AND position = '$p';");
    if (!$result || db_num_rows($result) == 0) {
      die("No planet SID=$s position=$p.<br>\n");
    }
    $row = db_fetch_assoc($result);
    $this->bonus = ($row['bonus'] == 1);
    $this->population_points = $row['population'];
    $this->population_level = population_points_to_level($row['population']);
    foreach (Planet::$ALL_BUILDINGS as $b) {
      $this->building_points[$b] = $row[$b];
    }
    $this->production_points = $row['production'];
    $this->owner = $row['owner'];
    $this->owner_fleet = $row['owner_fleet'];
  }
  
  function get_sid() { return $this->sid; }
  
  function get_position() { return $this->position; }
  
  function set_owner(Player $pl) {
    if (!($this->owner === $pl->get_player_id())) {
//       $result = db_query("UPDATE Planet SET owner = ".$pl->get_player_id()." WHERE sid = ".$this->sid." AND position = ".$this->position.";");
      $this->owner = $pl->get_player_id();
      $pl->add_planet($this);
    }
  }
  
  function get_owner() {
    if (isset($this->owner)) {
      return $this->owner;
    }
    else {
      return false;
    }
  }
  
  function set_bonus($bool) {
//     $result = db_query("UPDATE Planet SET bonus = ".($bool?1:0)." WHERE sid = '".$this->sid."' AND position = '".$this->position."';");
    $this->bonus = $bool;
  }
  
  function is_bonus() {
    return $this->bonus;
  }
  
  function set_building_points($field, $n) {
    if (array_search($field, Planet::$ALL_BUILDINGS) === false) { die(__FILE__ . ": line " . __LINE__.": No building called $field."); }
    $this->building_points[$field] = $n;
//     db_query("UPDATE Planet SET ${field} = $n WHERE sid = ".$this->sid." AND position = ".$this->position);
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
//     db_query("UPDATE Planet SET population = $n WHERE sid = ".$this->sid." AND position = ".$this->position);
  }
  
  function get_population_points() {
    return $this->population_points;
  }
  
  function get_population_level() {
    return population_points_to_level($this->get_population_points());
  }
  
  function set_production_points($n) {
    $this->production_points = $n;
//     db_query("UPDATE Planet SET production = $n WHERE sid = ".$this->sid." AND position = ".$this->position);
  }
  
  function get_production_points() {
    return $this->production_points;
  }
  
}

?>
