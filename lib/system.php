<?php

class System {
  private static $cache;
  
  private $sid;
  private $x;
  private $y;
  private $name;
  private $n_homes;
  private $planets;
  
  static function get_cache_or_new($s) {
    if (!isset(self::$cache)) {
      self::$cache = array();
    }
    
    if (array_key_exists($s, self::$cache)) {
      return self::$cache[$s];
    }
    else {
      $system = new System($s);
      self::$cache[$s] = $system;
      return $system;
    }
  }
  
  static function get_cache_or_load($s) {
    if (!isset(self::$cache)) {
      self::$cache = array();
    }
    
    if (array_key_exists($s, self::$cache)) {
      return self::$cache[$s];
    }
    else {
      $system = new System($s);
      $system->load();
      self::$cache[$s] = $system;
      return $system;
    }
  }
  
  static function save_cache() {
    foreach (self::$cache as $system) {
      $system->save();
    }
  }
  
  static function flush_cache() {
    self::$cache = array();
  }
  
  /**
   * Constructor. Try to load an existing system is only sid is provided.
   */
  function __construct($s) {
    $this->sid = $s;
    $this->x = 0;
    $this->y = 0;
    $this->name = 0;
    $this->n_homes = 0;
    $this->planets = array();
  }
  
  function save() {
    $s = $this->sid;
    $x = $this->x;
    $y = $this->y;
    $name = $this->name;
    $n_homes = $this->n_homes;
    db_query("BEGIN ; LOCK TABLE System IN SHARE ROW EXCLUSIVE MODE ;");
    $result = db_query("INSERT INTO System VALUES ($s, $x, $y, '$name', $n_homes)
    ON DUPLICATE KEY UPDATE name = '$name', n_homes = $n_homes");
    db_query("COMMIT;");
  }
  
  function save_planets() {
    foreach ($this->planets as $planet) {
      $planet->save();
    }
  }
  
  function load() {
    $s = $this->sid;
    $result = db_query("SELECT * FROM System WHERE sid = $s;");
    $row = db_fetch_assoc($result);
    $this->sid = $row['sid'];
    $this->x = $row['x'];
    $this->y = $row['y'];
    $this->name = $row['name'];
    $this->n_homes = $row['n_homes'];
    $this->planets = array();
//     $this->load_planets();
  }
  
  /**
   * Load the list of planets in the current system
   */
  function load_planets() {
    $results = db_query("SELECT position FROM Planet WHERE sid = ".$this->sid." ORDER BY position ASC;\n");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      $planet = new Planet($this->sid, $row['position']);
      $planet->load();
      array_push($this->planets, $planet);
    }
  }
  
  function get_sid() {
    return $this->sid;
  }
  
  function get_x() {
    return $this->x;
  }
  
  function set_x($x) {
    $this->x = $x;
  }
  
  function get_y() {
    return $this->y;
  }
  
  function set_y($y) {
    $this->y = $y;
  }
  
  function get_name() {
    return $this->name;
  }
  
  function set_name($name) {
    $this->name = $name;
  }
  
  function get_planets() {
    return $this->planets;
  }
  
  function get_num_homes() {
    return $this->n_homes;
  }
  
  function increase_num_homes() {
    $this->n_homes++;
//     db_query("UPDATE System SET n_homes = ".$this->n_homes." WHERE sid = ".$this->sid.";");
  }
  
  /**
   * Give the list of planets without any owner (even unknown)
   */
  function list_free_planets() {
    $list = array();
    foreach ($this->planets as $p) {
      if (!$p->get_owner()) {
	array_push($list, $p);
      }
    }
    return $list;
  }
  
  function add_planet() {
    $planet = new Planet($this->sid, count($this->planets)+1);
    array_push($this->planets, $planet);
    return $planet;
  }
  
  function add_bonus_planet() {
    $planet = $this->add_planet();
    $planet->set_bonus(true);
    return $planet;
  }

 
}

?>
