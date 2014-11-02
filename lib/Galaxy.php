<?php

class Galaxy {
  var $galaxy_id;
  var $spiral;
  var $science_start;
  var $production_start;
  
  static $DENSITY = GALAXY_DENSITY;
  static $BLOCK_SIZE = GALAXY_BLOCK_SIZE;
  
  /**
   * Default constructor (only one existing galaxy is supposed)
   */
  function __construct() {
    $result = db_query("SELECT * FROM Galaxy");
    if (!$result || db_num_rows($result) == 0) {
      db_query("INSERT INTO Galaxy VALUES (0, 0, 0, 0)");
      $result = db_query("SELECT * FROM Galaxy");
    }
    $row = db_fetch_assoc($result);
    $this->galaxy_id = $row['galaxy_id'];
    $this->spiral = $row['spiral'];
    $this->science_start = $row['science_start'];
    $this->production_start = $row['production_start'];
  }
  
  function add_player($l, $p, $e) {
    // Create an account and the corresponding player
    $ac = new Account($l, $p, $e);
    $pl = new Player();
    $pl->set_name($ac->login);
    $pl->save(); // Create the player in the DB and set the player ID
    Event::create_and_save($pl->get_player_id(), "normal", "Welcome to Heart of Rana", "This is a super game.<br>With super people playing it.<br>If you too wants to become awesome, please read the tutorial.");
    // Prepare a home planet + 2 free planet
    $this->new_home($pl);
    return $pl;
  }
  
  /**
   * Move the spiral tail and create a new system.
   */
  function move_spiral() {
    // Mutual exclusion on the spiral
    db_query("BEGIN; LOCK TABLE Galaxy IN EXCLUSIVE MODE;");
    $this->spiral++;
    if ($this->spiral == 1) {
      $system = System::get_cache_or_new($this->spiral);
      $system->set_x(0);
      $system->set_y(0);
      $system->set_name("Rana");
      $system->save();
      return $this->spiral;
    }
    $created = false;
    while (!$created) {
      //     $rho = $this->spiral/(2*pi());
      //     $theta = $this->spiral;
      // perimeter = 2*r*pi
      // 3 systems on the unity circle
      // system 1: r = 0
      // systems 2 to 4: r = 1, perimeter = 2pi
      // systems 5 to 10 : r = 2, perimeter = 4pi
      // SID = 1 sys + 3 sys + 6 sys + 9 sys... = 1 + sum_i 3*i = 1 + 3 (n*(n+1)/2)
      // => 2/3 (SID-1) = n*(n+1)
      // => n can be approximated as sqrt(2/3 (SID-1))
      $circle = sqrt(($this->spiral-1)*2/Galaxy::$DENSITY);
      $rho = round($circle);
      $theta = 2*pi()*($circle - $rho);
      //Jitter on rho
      $random = (0.95+0.1*(rand(0,100)/100));
      $rho *= $random;
      //Jitter on theta
      $random = (1-(1/Galaxy::$DENSITY)*(rand(0,100)/100));
      $theta *= $random;
      $new_x = $rho*cos($theta);
      $new_y = $rho*sin($theta);
      $new_x = round($new_x*Galaxy::$BLOCK_SIZE);
      $new_y = round($new_y*Galaxy::$BLOCK_SIZE);
      //Jitter on x and y
      $random = rand(0,100);
      if ($random <= 33) { $new_x -= intval((Galaxy::$BLOCK_SIZE+1)/3); }
      elseif ($random >= 67) { $new_x += intval(Galaxy::$BLOCK_SIZE/3); }
      $random = rand(0,100);
      if ($random <= 33) { $new_y -= intval((Galaxy::$BLOCK_SIZE+1)/3); }
      elseif ($random >= 67) { $new_y += intval(Galaxy::$BLOCK_SIZE/3); }
      $new_name = build_system_name();
      echo "Spiral moves<br>\n";
      try {
	$system = System::get_cache_or_new($this->spiral);
	$system->set_x($new_x);
	$system->set_y($new_y);
	$system->set_name($new_name);
	$system->save();
	$created = true;
      }
      catch (Exception $e) {
	$created = false;
      }
    }
    // Release the lock
    db_query("COMMIT;");
    return $this->spiral;
  }
  
  /**
   * Get a home system in the tail of the spiral. Potentially, a new system is created.
   */
  function get_home_system() {
    $sids = array();
    //Find the N first uncomplete systems
    $results = db_query("SELECT sid FROM System WHERE n_homes < ".PLAYERS_PER_SYSTEM." ORDER BY sid ASC LIMIT ".SPIRAL_WINDOW.";");
    $n = db_num_rows($results);
    for ($i = 0; $i < $n; $i++) {
      $row = db_fetch_assoc($results, $i);
      array_push($sids, $row['sid']);
    }
    //Add new systems in not enough free systems
    while ($n < SPIRAL_WINDOW) {
      array_push($sids, $this->move_spiral());
      $n++;
    }
    return $sids[rand(0, SPIRAL_WINDOW-1)];
  }
  
  /**
   * Add 3 new planets according to the spiral position
   */
  function new_home(Player $pl) {
    $sem = sem_get(NEW_HOME_SEM_KEY, 1);
    sem_acquire($sem);
    $sid = $this->get_home_system();
    $system = System::get_cache_or_load($sid);
    $system->load_planets();

    // Eventually add a bonus planet between 2 players
    if (count($system->get_planets()) > 0 && decide_bonus_planet($sid)) {
      echo "Add bonus planet<br>\n";
      $system->add_bonus_planet();
    }
    
    $planet = $system->add_planet();
    echo "Add free planet SID=".$planet->get_sid()." position=".$planet->get_position()."<br>\n";
    $planet = $system->add_planet();
    $planet->set_owner($pl);
    Event::create_and_save($pl->get_player_id(), "new_planet", "Your first planet", "People has elected you as the new leader of planet ".$planet->to_html().".");
    $system->increase_num_homes();
    echo "Add owned planet SID=".$planet->get_sid()." position=".$planet->get_position()."<br>\n";
    $system->add_planet();
    echo "Add free planet SID=".$planet->get_sid()." position=".$planet->get_position()."<br>\n";
    $system->save();
    $system->save_planets();
    sem_release($sem);
    sem_remove($sem);
  }
  
  function count_free_planets() {
    $result = db_query("SELECT sid, position FROM Planet WHERE owner = NULL;");
    return db_num_rows($result);
  }
  
}


?>