<?php
require("lib/common.php");


function set_random_player_stats($player) {
  $player->set_experience_points(rand(70, 7000));
  $player->set_culture_points(rand(70, 7000));
  foreach (Player::$ALL_SCIENCES as $s) {
    $player->set_science_points($s, rand(70, 7000));
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
  while ($culture >= $n_planets && $continue < 80 && $n_vis > 0) {
    $random = rand(0, 10000);
    $home = rand(0,100);
    if ($home < 80 && $n_homes > 0) {
      $planet = $home_free_planets[$random % $n_homes];
      $planet->set_owner($player);
      echo "Set owner = ".$player->get_name()." for planet ".$planet->get_sid()." #".$planet->get_position()."<br>\n";
    }
    else {
      $planet = $vis_free_planets[$random % $n_vis];
      $planet->set_owner($player);
      echo "Set owner = ".$player->get_name()." for planet ".$planet->get_sid()." #".$planet->get_position()."<br>\n";
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
  
}

function populate($num_players) {
  echo "<h2>Prepare empty database</h2>\n";
  $c = Database::get_connection();
  $all_commands = "SET FOREIGN_KEY_CHECKS = 0;";
  // Flush the database
  $result = db_query("SELECT concat('DROP TABLE IF EXISTS ', table_name, ';') FROM information_schema.tables WHERE table_schema = 'heartofrana';");
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

  $galaxy = new Galaxy();

  $players = array();

  echo "<h2>Create players</h2>\n";
  for ($i = 1; $i <= $num_players; $i++) {
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
      echo "<h3>Give planets to player $i</h3>\n";
      give_random_planets($pl);
      echo "<h3>Set buildings in planets of player $i</h3>\n";
      foreach ($pl->get_planets() as $p) {
	set_random_planet_stats($p);
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
  if (isset($_GET['galaxy_density'])) {
    define(GALAXY_DENSITY, $_GET['galaxy_density']);
  }
  if (isset($_GET['galaxy_blocks'])) {
    define(GALAXY_BLOCK_SIZE, $_GET['galaxy_blocks']);
  }
  populate($_GET['n_players']);
}
else {
  $density = GALAXY_DENSITY;
  $blocks = GALAXY_BLOCK_SIZE;
  echo <<<EOHTML
<form action='./populate.php' method='GET'>
<h2>Galaxy</h2>
<ul>
<li>Galaxy density: <input type='text' name='galaxy_density' size='1' value='$density'></li>
<li>Galaxy blocks: <input type='text' name='galaxy_blocks' size='1' value='$blocks'></li>
</li>
</ul>
<h2>Players</h2>
<ul>
<li>Number of players: <input type='text' name='n_players' size='5' value='10'></li>
<li>Random races: <input type='checkbox' name='random_races' checked></li>
<li>Random points: <input type='checkbox' name='random_points' checked></li>
<li>Random colonization: <input type='checkbox' name='random_colonization' checked></li>
</ul>
<input type='submit' value='Populate!'>
</form>
EOHTML;
}
build_footer();
?>