<?php


class Player {
  static $ALL_SCIENCES = array("biology", "economy", "energy", "mathematics", "physics", "social");
  static $ALL_RACES = array("growth", "science", "culture", "production", "speed", "attack", "defense");
  
  private $player_id;
  private $name;
  private $race_id;
  private $points;
  private $rank;
  private $experience_points;
  private $current_science;
  private $science_points;
  private $culture_points;
  private $tag;
  private $planets;
  
  /**
   * Constructor to load a given player based on his name
   */
  function __construct($name) {
    $this->name = $name;
    $this->player_id = null;
    $this->race_id = 0;
    $this->points = 0;
    $this->rank = 0;
    $this->experience_points = 0;
    $this->culture_points = 0;
    $this->current_science = PLAYER_DEFAULT_SCIENCE;
    
    $this->race = array();
    foreach (Player::$ALL_RACES as $trait) {
      $this->race[$trait] = 0;
    }
    $this->science_points = array();
    foreach (Player::$ALL_SCIENCES as $science) {
      $this->science_points[$science] = 0;
    }
    
    $this->tag = null;
    $this->planets = array();
  }
  
  function save_race() {
    $q = "SELECT race_id FROM Race WHERE ";
    $q_attr = array();
    foreach (Player::$ALL_RACES as $trait) {
      array_push($q_attr, "$trait = ".$this->race[$trait]);
    }
    $q .= join(" AND ", $q_attr);
    $result = db_query($q);
    // New race
    if (db_num_rows($result) == 0) {
      $result = db_query("INSERT INTO Race VALUES (DEFAULT, ".join(", ", array_values($this->race)).")");
      $this->race_id = db_last_insert_id();
    }
    // Or existing one
    else {
      $row = db_fetch_assoc($result);
      $this->race_id = $row['race_id'];
    }
  }
  
  function save_science() {
    $result = db_query("REPLACE INTO Science (player_id, ".join(", ", Player::$ALL_SCIENCES).") VALUES (".$this->player_id.", ".join(", ", array_values($this->science_points)).")");
  }
  
  function save() {
    $this->save_race();
    
    // New player
    if ($this->player_id == null) {
      $result = db_query("INSERT INTO Player VALUES (DEFAULT, '".$this->name."', ".$this->race_id.", ".$this->points.", ".$this->rank.", ".$this->experience_points.", '".$this->culture_points."', '".$this->current_science."', ".($this->tag == null?"NULL":$this->tag).")");
      $this->player_id = db_last_insert_id();
      
    }
    // Existing player
    else {
      $result = db_query("UPDATE Player SET race_id = ".$this->race_id.", points = ".$this->points.", rank = ".$this->rank.", experience = ".$this->experience_points.", culture = ".$this->culture_points.", current_science = '".$this->current_science."', tag = ".($this->tag == null?"NULL":$this->tag)." WHERE player_id = ".$this->get_player_id());
    }
    
    $this->save_science();
  }
  
  function load() {
    $name = $this->name;
    $result = db_query("SELECT * FROM Player WHERE name = '$name';");
    $row = db_fetch_assoc($result);
    $this->player_id = $row['player_id'];
    $this->name = $row['name'];
    $this->race_id = $row['race_id'];
    $this->points = $row['points'];
    $this->rank = $row['rank'];
    $this->experience_points = $row['experience'];
    $this->culture_points = $row['culture'];
    $this->current_science = $row['current_science'];
    $this->tag = $row['tag'];
    
    $result = db_query("SELECT * FROM Race WHERE race_id = ".$this->race_id);
    $this->race = db_fetch_assoc($result);
    
    $result = db_query("SELECT * FROM Science WHERE player_id = ".$this->player_id);
    $this->science_points = db_fetch_assoc($result);
    
    $this->planets = $this->list_planets();
  }
  
  function get_player_id() { return $this->player_id; }
  
  function get_name() { return $this->name; }
  
  function get_points() { return $this->points; }
  
  function get_rank() { return $this->rank; }
  
  function set_race($field, $n) {
    if (array_search($field, Player::$ALL_RACES) === false) {
      die(__FILE__ . ": line " . __LINE__.": No race trait called $field.");
    }
    $this->race[$field] = $n;
  }
  
  // EXPERIENCE AND PLAYER LEVEL
  
  function set_experience_points($n) {
//     db_query("UPDATE Player SET experience = $n WHERE player_id = ".$this->player_id);
    $this->experience_points = $n;
  }
  
  function get_experience_points() {
    return $this->experience_points;
  }
  
  function get_player_level() {
    return experience_points_to_player_level($this->get_experience_points);
  }
  
  // SCIENCES
  
  function get_science_points($field) {
    if (array_search($field, Player::$ALL_SCIENCES) === false) { die(__FILE__ . ": line " . __LINE__.": No science called $field."); }
    return $this->science_points[$field];
  }
  
  function get_science_level($field) {
    return science_points_to_level($this->get_science_points($field));
  }
  
  function set_science_points($field, $n) {
    if (array_search($field, Player::$ALL_SCIENCES) === false) { die(__FILE__ . ": line " . __LINE__.": No science called $field."); }
//     db_query("UPDATE Science SET ${field} = $n WHERE player_id = ".$this->player_id);
    $this->science_points[$field] = $n;
  }
  
  function get_current_science() {
    return $this->current_science;
  }

  function set_current_science($science) {
    if (array_search($science, Player::$ALL_SCIENCES) === false) { die(__FILE__ . ": line " . __LINE__.": No science called $science."); }
//     $result = db_query("UPDATE Player SET current_science = '$science' WHERE player_id = ".$this->player_id);
    $this->current_science = $science;
    return true;
  }
  
  // CULTURE
  
  function get_culture_points() {
    return $this->culture_points;
  }
  
  function get_culture_level() {
    return culture_points_to_level($this->get_culture_points());
  }
  
  function set_culture_points($n) {
//     db_query("UPDATE Player SET culture = $n WHERE player_id = ".$this->player_id);
    $this->culture_points = $n;
  }
  
  // ALLIANCE
  
  function get_alliance_tag() {
    return $this->tag;
  }
  
  function get_alliance() {
    // TODO
  }
  
  // PLANETS
  
  function get_planets() {
    return $this->planets;
  }
  
  function add_planet(Planet $planet) {
    $planet->set_owner($this);
    array_push($this->planets, $planet);
  }
  
  function list_planets() {
    $results = db_query("SELECT sid, position FROM Planet WHERE owner = '".$this->player_id."'");
    $list = array();
    $n = db_num_rows($results);
    for ($i =0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $p = new Planet($row['sid'], $row['position']);
      $p->load();
      array_push($list, $p);
    }
    return $list;
  }
  
  function list_home_systems() {
    $list = array();
    foreach ($this->planets as $p) {
      $s = System::get_cache_or_load($p->get_sid());
      $list[$p->get_sid()] = $s;
    }
    return $list;
  }
  
  function list_visible_systems() {
    $bio_level = science_points_to_level($this->get_science_points('biology'));
    $bio_range = biology_level_to_range($bio_level);
    $list = array();
    $results = db_query("SELECT DISTINCT other.sid FROM Planet p, System mine, System other WHERE p.owner = ".$this->player_id." AND p.sid = mine.sid AND other.x >= mine.x-$bio_range AND other.x <= mine.x+$bio_range AND other.y >= mine.y-$bio_range AND other.y <= mine.y+$bio_range");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $s = System::get_cache_or_load($row['sid']);
      $list[$row['sid']] = $s;
    }
    return $list;
  }

}

?>