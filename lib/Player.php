<?php

/**
 * Class to model in-game players
 */
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
  private $fleets;

  /**
   * Constructor to load a given player based on his name
   */
  function __construct($player_id = null) {
    $this->name = null;
    $this->player_id = $player_id;
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
    $this->planets = null;
    $this->fleets = null;
  }

  function save_race() {
    $q = "SELECT race_id FROM Race WHERE ";
    $q_attr = array();
    foreach (Player::$ALL_RACES as $trait) {
      array_push($q_attr, "$trait = " . $this->race[$trait]);
    }
    $q .= join(" AND ", $q_attr);
    $result = db_query($q);
    // New race
    if (db_num_rows($result) == 0) {
      $result = db_query("INSERT INTO Race VALUES (DEFAULT, " . join(", ", array_values($this->race)) . ")");
      $this->race_id = db_last_insert_id();
    }
    // Or existing one
    else {
      $row = db_fetch_assoc($result);
      $this->race_id = $row['race_id'];
    }
  }

  function save_science() {
    $result = db_query("REPLACE INTO Science (player_id, " . join(", ", Player::$ALL_SCIENCES) . ") VALUES (" . $this->player_id . ", " . join(", ", array_values($this->science_points)) . ")");
  }

  function save() {
    $this->save_race();

    // New player
    if ($this->player_id == null) {
      $result = db_query("INSERT INTO Player VALUES (DEFAULT, '" . $this->name . "', " . $this->race_id . ", " . $this->points . ", " . $this->rank . ", " . $this->experience_points . ", '" . $this->culture_points . "', '" . $this->current_science . "', " . ($this->tag == null ? "NULL" : $this->tag) . ")");
      $this->player_id = db_last_insert_id();
    }
    // Existing player
    else {
      $result = db_query("UPDATE Player SET race_id = " . $this->race_id . ", points = " . $this->points . ", rank = " . $this->rank . ", experience = " . $this->experience_points . ", culture = " . $this->culture_points . ", current_science = '" . $this->current_science . "', tag = " . ($this->tag == null ? "NULL" : $this->tag) . " WHERE player_id = " . $this->get_player_id());
    }

    $this->save_science();
  }

  function load() {
    $filter = "";
    if ($this->player_id !== null) {
      $filter = "player_id = " . $this->player_id;
    } elseif ($this->name !== null) {
      $filter = "name = '" . $this->name . "'";
    } else {
      throw new Exception("Impossible to load requested player.");
    }
    $result = db_query("SELECT * FROM Player WHERE $filter");
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

    $result = db_query("SELECT * FROM Race WHERE race_id = " . $this->race_id);
    $this->race = db_fetch_assoc($result);

    $result = db_query("SELECT * FROM Science WHERE player_id = " . $this->player_id);
    $row = db_fetch_assoc($result);
    $this->science_points = array();
    foreach (Player::$ALL_SCIENCES as $science) {
      $this->science_points[$science] = $row[$science];
    }
  }

  function get_player_id() {
    return $this->player_id;
  }

  function get_name() {
    return $this->name;
  }

  function set_name($name) {
    $this->name = $name;
  }

  function get_points() {
    return $this->points;
  }

  function get_rank() {
    return $this->rank;
  }

  function set_race($field, $n) {
    if (array_search($field, Player::$ALL_RACES) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No race trait called $field.");
    }
    $this->race[$field] = $n;
  }

  // EXPERIENCE AND PLAYER LEVEL

  function set_experience_points($n) {
    $this->experience_points = $n;
  }

  function get_experience_points() {
    return $this->experience_points;
  }

  function add_experience_points($n) {
    $this->set_experience_points($this->get_experience_points() + $n);
  }

  function get_player_level() {
    return experience_points_to_player_level($this->get_experience_points());
  }

  // SCIENCES

  function get_science_points($field) {
    if (array_search($field, Player::$ALL_SCIENCES) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No science called $field.");
    }
    return $this->science_points[$field];
  }

  function get_science_level($field) {
    return science_points_to_level($this->get_science_points($field));
  }

  public function get_visibility_range() {
    return biology_level_to_range($this->get_science_level('biology'));
  }
  
  public function get_population_limit() {
    return social_level_to_population_limit($this->get_science_level('biology'));
  }

  function set_science_points($field, $n) {
    if (array_search($field, Player::$ALL_SCIENCES) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No science called $field.");
    }
    $this->science_points[$field] = $n;
  }

  function increment_points() {
    if ($this->planets === null) {
      $this->load_planets();
    }
    $science_new_points = 0;
    $culture_new_points = 0;
    foreach ($this->planets as $p) {
      $science_new_points += $p->get_building_level("lab");
      $culture_new_points += $p->get_building_level("cybernet");
      $production_new_points = $p->get_building_level("farm") + $p->get_building_level("factory");
      $p->set_production_points($p->get_production_points() + $production_new_points);
//       $production_multiplier = $this->get_production_multiplier();
      print "Increment production by " . $production_new_points . " in " . $p->to_string() . "<br>\n";
      $population_new_points = $p->get_building_level("farm");
//       $population_multiplier = $this->get_population_multiplier();
      if ($this->get_population_limit() <= population_points_to_level($p->get_population_points() + $population_new_points)) {
        $p->set_population_points($p->get_population_points() + $population_new_points);
        print "Increment population by " . $population_new_points . " in " . $p->to_string() . "<br>\n";
      }
      $p->save();
    }
    $cur_science = $this->get_current_science();
    $science_cur_points = $this->get_science_points($cur_science);
    $culture_cur_points = $this->get_culture_points();
    $this->set_science_points($cur_science, $science_cur_points + $science_new_points);
    print "Increment " . $this->get_current_science() . " by " . $science_new_points . "<br>\n";
    $this->set_culture_points($culture_cur_points + $culture_new_points);
    print "Increment culture by " . $culture_new_points . "<br>\n";
  }

  function get_current_science() {
    return $this->current_science;
  }

  function set_current_science($science) {
    if (array_search($science, Player::$ALL_SCIENCES) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No science called $science.");
    }
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
    if ($this->planets === null) {
      $this->load_planets();
    }
    return $this->planets;
  }

  function add_planet(Planet $planet) {
    $planet->set_owner($this);
    if ($this->planets === null) {
      $this->load_planets();
    }
    array_push($this->planets, $planet);
  }

  function load_planets() {
    $results = db_query("SELECT sid, position FROM Planet WHERE owner = " . $this->player_id);
    $list = array();
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $p = new Planet($row['sid'], $row['position']);
      $p->load();
      array_push($list, $p);
    }
    $this->planets = $list;
  }

  function list_home_systems() {
    $list = array();
    if ($this->planets === null) {
      $this->load_planets();
    }
    foreach ($this->planets as $p) {
      $s = System::get_cache_or_load($p->get_sid());
      $list[$p->get_sid()] = $s;
    }
    return $list;
  }

  public function list_visible_systems() {
    $bio_level = science_points_to_level($this->get_science_points('biology'));
    $bio_range = biology_level_to_range($bio_level);
    $list = array();
    $results = db_query("SELECT DISTINCT other.sid FROM Planet p, System mine, System other WHERE p.owner = " . $this->player_id . " AND p.sid = mine.sid AND other.x >= mine.x-$bio_range AND other.x <= mine.x+$bio_range AND other.y >= mine.y-$bio_range AND other.y <= mine.y+$bio_range");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $s = System::get_cache_or_load($row['sid']);
      $list[$row['sid']] = $s;
    }
    return $list;
  }

  public function can_see_planet(Planet $planet) {
    $vis_sys = $this->list_visible_systems();
    foreach ($vis_sys as $sys) {
      if ($planet->get_sid() == $sys->get_sid()) {
        return true;
      }
    }
    return false;
  }

  public function can_see_player(Player $player) {
    $vis_sys = array();
    foreach ($this->list_visible_systems() as $s) {
      array_push($vis_sys, $s->get_sid());
    }
    $pl_home_sys = array();
    foreach ($player->list_home_systems() as $s) {
      array_push($pl_home_sys, $s->get_sid());
    }
    $inter = array_intersect($vis_sys, $pl_home_sys);
    return (count($inter) > 0);
  }

  function is_enabled($type) {
    switch ($type) {
      case "cruisers":
        return ($this->get_science_level(CRUISER_TRIGGER_SCIENCE) >= CRUISER_TRIGGER_LEVEL);
        break;
      case "battleships":
        return ($this->get_science_level(BATTLESHIP_TRIGGER_SCIENCE) >= BATTLESHIP_TRIGGER_LEVEL);
        break;
      default:
        return true;
        break;
    }
  }

  function get_fleets() {
    if ($this->fleets === null) {
      $this->load_fleets();
    }
    return $this->fleets;
  }

  function load_fleets() {
    $this->fleets = array();

    // Resting fleets
    $results = db_query("SELECT f.fleet_id, p.sid, p.position FROM Fleet f, Planet p WHERE f.owner = " . $this->get_player_id() . " AND p.owner_fleet = f.fleet_id");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $fleet = new RestingFleet($row['sid'], $row['position'], $row['fleet_id']);
      $fleet->load();
      array_push($this->fleets, $fleet);
    }

    // Sieging fleets
    $results = db_query("SELECT f.fleet_id, p.sid, p.position FROM Fleet f, Planet p WHERE f.owner = " . $this->get_player_id() . " AND p.sieging_fleet = f.fleet_id");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $fleet = new SiegingFleet($row['sid'], $row['position'], $row['fleet_id']);
      $fleet->load();
      array_push($this->fleets, $fleet);
    }

    // Flying fleets
    $results = db_query("SELECT f.fleet_id FROM Fleet f, Flight g WHERE f.owner = " . $this->get_player_id() . " AND f.fleet_id = g.fleet_id");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $fleet = new FlyingFleet($row['fleet_id']);
      $fleet->load();
      array_push($this->fleets, $fleet);
    }
  }

  function add_fleet(Fleet $fleet) {
    if ($this->fleets === null) {
      $this->load_fleets();
    }
    array_push($this->fleets, $fleet);
  }

  function update_fleet(Fleet $fleet) {
    if ($this->fleets === null) {
      $this->load_fleets();
    }
    $n = count($this->fleets);
    for ($i = 0; $i < $n; $i++) {
      if ($this->fleets[$i]->get_fleet_id() === $fleet->get_fleet_id()) {
        $this->fleets[$i] = $fleet;
      }
    }
  }

  public function remove_fleet(Fleet $fleet) {
    $n = count($this->fleets);
    for ($i = 0; $i < $n; $i++) {
      if ($this->fleets[$i]->get_fleet_id() === $fleet->get_fleet_id()) {
        unset($this->fleets[$i]);
        $this->fleets = array_values($this->fleets);
        return;
      }
    }
  }

  public function count_flying_fleets() {
    $n = 0;
    foreach ($this->get_fleets() as $f) {
      if (get_class($f) == "FlyingFleet") {
        $n++;
      }
    }
    return $n;
  }

  function to_html() {
    return "<a href='profile.php?player_id=" . $this->get_player_id() . "'>" . $this->get_name() . "</a>";
  }

}

?>