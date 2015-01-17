<?php
require("lib/common.php");



function set_random_player_stats($player) {
  $player->set_experience_points(rand(20, 20000));
  $player->set_culture_points(rand(100, 15000));
  foreach (Player::$ALL_SCIENCES as $s) {
    $player->set_science_points($s, rand(2000, 20000));
  }
  $n = count(Player::$ALL_SCIENCES);
  $player->set_current_science(Player::$ALL_SCIENCES[rand(0, $n-1) % $n]);
}



function set_random_race($player) {
  foreach (Player::$ALL_RACES as $trait) {
    $player->set_race($trait, rand(-4, 4));
  }
}



function list_home_free_planets($player) {
  $home_list = $player->list_home_systems();
  $home_free_planets = array();
  foreach ($home_list as $s) { $home_free_planets = array_merge($home_free_planets, $s->list_free_planets()); }
  return $home_free_planets;
}

function list_visible_free_planets($player) {
  $vis_list = $player->list_visible_systems();
  $vis_free_planets = array();
  foreach ($vis_list as $s) { $vis_free_planets = array_merge($vis_free_planets, $s->list_free_planets()); }
  return $vis_free_planets;
}

function give_random_planets($player) {
  $home_free_planets = list_home_free_planets($player);
  $n_homes = count($home_free_planets);
  $vis_free_planets = list_visible_free_planets($player);
  $n_vis = count($vis_free_planets);
  
  $continue = 0;
  $n_planets = 1;
  $culture = culture_points_to_level($player->get_culture_points());
  while ($culture >= $n_planets && $continue < 40 && $n_vis > 0) {
    $random = rand(0, 10000);
    $home = rand(0,100);
    if ($home < 80 && $n_homes > 0) {
      $planet = $home_free_planets[$random % $n_homes];
//      echo "<font color='red'>AVANT</font><br><pre>";
//      print_r($planet);
//      echo "</pre><br>\n";
      $planet->set_owner($player);
//      $planet->set_owner_id($player->get_player_id());
      echo "Set owner = ".$player->get_name()." in home system for planet ".$planet->get_sid()." #".$planet->get_position()."<br>\n";
    }
    else {
      $planet = $vis_free_planets[$random % $n_vis];
      $planet->set_owner($player);
      echo "Set owner = ".$player->get_name()." in visible system for planet ".$planet->get_sid()." #".$planet->get_position()."<br>\n";
    }
    $home_free_planets = list_home_free_planets($player);
    $n_homes = count($home_free_planets);
    $vis_free_planets = list_visible_free_planets($player);
    $n_vis = count($vis_free_planets);
    $continue = rand(0,100);
  }
}

function set_random_planet_stats($planet) {
  $stats = array("farm", "factory", "lab", "cybernet", "starbase");
  foreach ($stats as $s) {
    $planet->set_building_points($s, rand(50, 1000));
  }
  $planet->set_population_points(rand(50, 1000));
  $planet->set_production_points(rand(0, 1000));
}

function build_random_fleets(Player $player) {
  $planets = $player->get_planets();
  $n_planets = count($planets);
  for ($i = 0; $i < $n_planets; $i++) {
//    echo "Planet $i/$n_planets = <pre>".var_dump($planets[$i])."</pre><br>\n";
    $random = rand(0,100);
    if ($random >= 40) {
      $fleet = new RestingFleet($planets[$i]->get_sid(), $planets[$i]->get_position());
      $fleet->set_owner($player);
      if (rand(0,3) == 0) {
        $n = rand(0, 2*$n_planets);
        $fleet->set_ships("colonyships", rand(1, $n));
      }
      if (rand(0,3) == 0) {
        $n = rand(0, 2*$n_planets);
        $fleet->set_ships("transports", $n);
      }
      $n = rand(0, 100*$n_planets);
      $fleet->set_ships("destroyers", $n);
      if ($player->is_enabled("cruisers")) {
        $n = rand(0, 25*$n_planets);
        $fleet->set_ships("cruisers", $n);
      }
      if ($player->is_enabled("battleships")) {
        $n = rand(0, 5*$n_planets);
        $fleet->set_ships("battleships", $n);
      }
      $fleet->save(); // Fleet ID is assigned here -- Do NOT interchange with next line
      $fleet->set_planet($planets[$i]);
      $planets[$i]->save();
    }
  }
}


function launch_random_fleets(Player $player, Galaxy $galaxy) {
  $n_flights = 0;
  foreach ($player->get_planets() as $p) {
    $rand = rand(0,100);
    if ($rand >= 20 && $n_flights < 5 && $p->has_owner_fleet()) {
      $launched = false;
      while (!$launched) {
	try {
	  $sid = rand(1, $galaxy->spiral);
	  $position = rand(1, 15);
	  $target = new ProxyPlanet($sid, $position);
	  $target->load();
	  $flight = $p->get_owner_fleet()->launch($target);
	  $flight->save();
	  $launched = true;
	  $n_flights++;
	}
	catch (Exception $e) {
	  // Exception handling
	}
      }
      print "Launched fleet from ".$p->to_string()." to ".$target->to_string()."<br>\n";
    }
  }
}


function flush_database() {
  echo "<h2>Prepare empty database</h2>\n";
  $c = Database::get_connection();
  $all_commands = "SET FOREIGN_KEY_CHECKS = 0;";
  // Flush the database
  $result = db_query("SELECT concat('DROP TABLE IF EXISTS ', table_name, ';') FROM information_schema.tables WHERE table_schema = '".DB_NAME."';");
  if (!$result) {
//     "DROP TABLE heartofrana; CREATE DATABASE heartofrana CHARACTER SET utf8 COLLATE utf8_bin;")) {
    die("Unable to drop the tables: (" . $c->errno . ") " . $c->error);
  }
  $n = db_num_rows($result);
  for ($i = 0; $i < $n; $i++) {
    $row = db_fetch_row($result);
    $all_commands .= $row[0]."\n";
  }
  // Recreate empty databases
  $all_commands .= file_get_contents("db_creation.sql");
  $all_commands .= "SET FOREIGN_KEY_CHECKS = 1;";
  if (!db_query($all_commands)) {
    die("Failed in creating tables: (" . Database::get_connection()->errno . ") " . db_error());
  }
}

function populate($num_players) {
  $galaxy = new Galaxy();

  $players = array();
  
  $last_player_id = 0;
  if (!isset($_GET['flush_database'])) {
    $result = db_query("SELECT player_id FROM Player ORDER BY player_id DESC LIMIT 1");
    $row = db_fetch_assoc($result);
    $last_player_id = $row['player_id'];
  }
  echo "Last player ID = $last_player_id<br>\n";

  echo "<h2>Create players</h2>\n";
  for ($i = $last_player_id+1; $i <= $last_player_id+$num_players; $i++) {
    echo "<h3>Create player $i</h3>\n";
    $pl = $galaxy->add_player("Player$i", "toto", "tata");
    if (isset($_GET['random_races'])) {
      echo "<h3>Set race of player $i</h3>\n";
      set_random_race($pl);
    }
    if (isset($_GET['random_points'])) {
      echo "<h3>Set points of player $i</h3>\n";
      set_random_player_stats($pl);
    }
    $pl->save();
    array_push($players, $pl);
  }
  
  if (isset($_GET['random_colonization'])) {
    echo "<h2>Colonize planets</h2>\n";
    // Give planets to num_players
    for ($i = 0; $i < $num_players; $i++) {
      $pl = $players[$i];
      echo "<h3>Give planets to player ".$pl->get_player_id()."</h3>\n";
      give_random_planets($pl);
      echo "<h3>Set buildings in planets of player ".$pl->get_player_id()."</h3>\n";
      foreach ($pl->get_planets() as $p) {
	set_random_planet_stats($p);
      }
      $pl->save();
    }
  }
  
  if (isset($_GET['random_fleets'])) {
    echo "<h2>Build fleets</h2>\n";
    for ($i = 0; $i < $num_players; $i++) {
      $pl = $players[$i];
      echo "<h3>Build fleets for player ".$pl->get_player_id()."</h3>\n";
      build_random_fleets($pl);
      if (isset($_GET['random_flights'])) {
	echo "<h3>Launch fleets for player ".$pl->get_player_id()."</h3>\n";
	launch_random_fleets($pl, $galaxy);
      }
      $pl->save();
    }
  }
  
  echo "<h2>Save planets</h2>\n";
  for ($i = 0; $i < $num_players; $i++) {
      foreach ($players[$i]->get_planets() as $p) {
	$p->save();
      }
  }
  echo "--<br>\n";
}


build_header('basic/basic.css');
if (isset($_GET['n_players'])) {
  if (isset($_GET['flush_database'])) { flush_database(); }
  if (isset($_GET['galaxy_density'])) { Galaxy::$DENSITY = $_GET['galaxy_density']; }
  if (isset($_GET['galaxy_blocks'])) { Galaxy::$BLOCK_SIZE = $_GET['galaxy_blocks']; }
  populate($_GET['n_players']);
}
else {
  $density = GALAXY_DENSITY;
  $blocks = GALAXY_BLOCK_SIZE;
  echo <<<EOHTML
<form action='./populate.php' method='GET'>
<h2>Database</h2>
<li>Flush database? <input type='checkbox' name='flush_database' checked></li>
<h2>Galaxy</h2>
<ul>
<li>Galaxy density: <input type='text' name='galaxy_density' size='1' value='$density'></li>
<li>Galaxy blocks: <input type='text' name='galaxy_blocks' size='1' value='$blocks'></li>
</li>
</ul>
<h2>Players</h2>
<ul>
<li>Number of players: <input type='text' name='n_players' size='5' value='10'></li>
<li>Random races? <input type='checkbox' name='random_races' checked></li>
<li>Random points? <input type='checkbox' name='random_points' checked></li>
<li>Random colonization? <input type='checkbox' name='random_colonization' checked></li>
<li class="todo">Random artefacts? <input type='checkbox' name='random_artefacts' checked></li>
<li>Random fleets? <input type='checkbox' name='random_fleets' checked></li>
</ul>
<h2>Alliances/relationships</h2>
<ul>
<li class="todo">Number of alliances: <input type='text' name='n_alliances' size='5' value='4'></li>
<li class="todo">Random trade agreements? <input type='checkbox' name='random_tas' checked></li>
<li>Random flights? <input type='checkbox' name='random_flights' checked></li>
<li class="todo">Random messages? <input type='checkbox' name='random_messages' checked></li>

</ul>
<input type='submit' value='Populate!'>
</form>
EOHTML;
}
build_footer();
?>