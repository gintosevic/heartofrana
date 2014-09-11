<?php

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

if (version_compare(PHP_VERSION, '5.5.0', '<')) {
	require_once "password_compat.php";
}
require_once "constants.php";
require_once "database.php";
define('__ROOT__', dirname(dirname(__FILE__))); 
require_once "galaxy.php";
require_once "system.php";
require_once "planet.php";
require_once "account.php";
require_once "fleet.php";
require_once "flight.php";
require_once "player.php";
require_once "event.php";

function print_login_form() {
  $url = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
  $url = strtok($url, '?');
  echo <<<EOL
<table>
<tr>
<td span=2>
<h1>Heart of Rana</h1>
Version 0.1
</td>

</tr>
<form action="http://$url" method="post">
<tr>
<td>
Login: <input name="login">
Password: <input type="password" name="password">
</td>
<td>
<input type="submit" value="Login">
</td>
</tr>
</form>
EOL;
}

function check_login() {
  session_start();
  if (isset($_GET['logout'])) {
    session_destroy();
    return false;
  }
  else {
    if (isset($_POST['login']) && isset($_POST['password'])) {
      try {
	session_destroy();
	session_start();
	$_SESSION['account'] = new Account($_POST['login'], $_POST['password']);
	$pl = new Player($_POST['login']);
	$pl->load();
	$pl->load_planets();
	$pl->load_fleets();
// 	foreach ($pl->get_planets() as $planet) {
// 	  $planet->load_owner_fleet();
// 	  $planet->load_sieging_fleet();
// 	}
	$_SESSION['player'] = $pl;
	$_SESSION['galaxy'] = new Galaxy();
	//       print_r($_SESSION);
// 	print_r($_SESSION['player']);
	return true;
      }
      catch(Exception $e) {
	return false;
      }
    }
    elseif (isset($_SESSION['account'])) {
      //     print_r($_SESSION['account']);
      //     echo "\n<br>\n";
//           print_r($_SESSION['player']);
      //     echo "<br>\n";
      return true;
    }
    else {
      return false;
    }
  }
}

function print_login_failed() {
  echo <<<EOHTML
<div>
You need to login with proper credentials.
</div>
EOHTML;
}

function build_header($css = '') {
  echo <<<EOL
<html>
<body>
<head>
<title>HoR0.1</title>
EOL;
if ($css != '') {
  echo "<link rel='stylesheet' type='text/css' href='$css'/>\n";
}
echo <<<EOL
</head>
<body>
<center>

EOL;
}

function build_menu() {
  echo <<<EOL
<div class="main_menu">
<ul>
<li> <a href="news.php">News</a> </li>
<li> <a href="map.php">Map</a> </li>
<li> <a href="planets.php">Planets</a> </li>
<li> <a href="science.php">Science</a> </li>
<li> <a href="trade.php">Trade</a> </li>
<li> <a href="alliance.php">Alliance</a> </li>
<li> <a href="fleets.php">Fleets</a> </li>
<li> <a href="?logout" id='logout'>Logout</a> </li>
</ul>
<br>
<ul>
<li> <a href="mailbox.php">Mailbox</a> </li>
<li> <a href="chat.php">Chat</a> </li>
<li> <a href="profile.php">Profile</a> </li>
<li> <a href="settings.php">Settings</a> </li>
<li> <a href="rankings.php">Rankings</a> </li>
<li> <a href="polls.php">Polls</a> </li>
<li> <a href="http://starfactionsdevelopers.freeforums.org/index.php" target="_blank">Forum</a> </li>
</ul>
</div>
<br>
EOL;
}

function build_footer() {
  db_dump_queries();
echo <<<EOL

</center>
</body>
</html>
EOL;
}

/**
 * Function by Stephen Watkins
 */
function generate_random_string($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}

function build_system_name() {
  $min = 4;
  $max = 9;
  return generate_random_string(rand($min, $max))." ".generate_random_string(rand($min, $max));
}

function build_player_name() {
  $min = 5;
  $max = 10;
  return generate_random_string(rand($min, $max));
}

/**
 * Function to decide whether a bonus planet should be added or not in a given system.
 */
function decide_bonus_planet($sid) {
  $mean = BONUS_PLANETS_PER_SYSTEM_MEAN/(PLAYERS_PER_SYSTEM-1);
  $stddev = BONUS_PLANETS_PER_SYSTEM_STDDEV/(PLAYERS_PER_SYSTEM-1);
  $x = rand(0,1000)/1000;
  //TODO: integrate standard deviation
  return ($x < $mean);
}

function science_points_to_level($points) {
  return round((pow($points, 1/3)/1.55)-1);
}

function science_level_to_points($level) {
  return round(pow((($level+1)*1.55), 3));
}

function biology_level_to_range($level) {
  if ($level < 10) { return intval($level / 2); }
  else { return $level; }
}


function experience_points_to_player_level($points) {
  return round(sqrt($points));
}

/**
 * Number of points need to reach level $level from level $level-1
 */
function population_next_level_points($level) {
  if ($level >= 1) {
    return pow($level*3-1.5, 2)+0.75;
  }
  else {
    return 0;
  }
}

/**
 * Level to cumulative points
 */
function population_level_to_points($level) {
  $cum_pps = 0;
  for ($i = 1; $i <= $level; $i++) {
    $cum_pps += population_next_level_points($level);
  }
  return $cum_pps;
}

/**
 * Cumulative points to corresponding level
 */
function population_points_to_level($points) {
  $level = 0;
  $next_lvl_pps = 0;
  while ($points >= $next_lvl_pps) {
    $points -= $next_lvl_pps;
    $next_lvl_pps = population_next_level_points($level+1);
    $level++;
  }
  return $level;
}

/**
 * Cumulative points to corresponding level
 */
function building_points_to_level($points) {
  $level = 0;
  $next_lvl_pps = PLANET_DEVELOPMENT_INITIAL_COST;
  while ($points >= $next_lvl_pps) {
    $points -= $next_lvl_pps;
    $next_lvl_pps *= PLANET_DEVELOPMENT_COMMON_RATIO;
    $level++;
  }
  return $level;
}

/**
 * Level to corresponding cumulative points
 */
function building_level_to_points($level) {
  $cum_pps = 0;
  $current_lvl_pps = PLANET_DEVELOPMENT_INITIAL_COST;
  for ($i = 1; $i <= $level; $i++) {
    $cum_pps += $current_lvl_pps;
    $current_lvl_pps = round($current_lvl_pps*PLANET_DEVELOPMENT_COMMON_RATIO);
  }
  return $cum_pps;
  //     return PLANET_DEVELOPMENT_INITIAL_COST*(1-pow(PLANET_DEVELOPMENT_COMMON_RATIO,$level))/(1-PLANET_DEVELOPMENT_COMMON_RATIO);
}

/**
 * Cumulative points to level
 */
function culture_level_to_points($level) {
  if ($level >= 2) {
    return round(pow($level*3.2+0.3, 3));
  }
  else {
    return 0;
  }
}

/**
 * Level to cumulative points
 */
function culture_points_to_level($points) {
    return max(0, intval((pow($points, 1/3)-0.3)/3.2));
}

function player_id_to_name($player_id) {
  $result = db_query("SELECT name FROM Player WHERE player_id = $player_id");
  if (!$result) { die(db_error()); }
  $row = db_fetch_assoc($result);
  return $row['name'];
}

function sid_to_name($sid) {
  $result = db_query("SELECT name FROM System WHERE sid = $sid");
  if (!$result) { die(db_error()); }
  $row = db_fetch_assoc($result);
  return $row['name'];
}

?>
