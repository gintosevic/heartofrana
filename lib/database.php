<?php

require_once "credentials.php";
define("DATABASE_DEBUG_MODE", 1);


$all_queries = array();

function db_dump_queries() {
  global $all_queries;
  echo "<br><h2>History of queries</h2>\n";
  foreach ($all_queries as $q) {
    echo "<div class='query'>$q</div>\n";
  }
}

function db_query($q) {
  if (DATABASE_DEBUG_MODE == 1) {
    global $all_queries;
    array_push($all_queries, $q);
  }
  elseif (DATABASE_DEBUG_MODE == 2) {
    echo "<div class='query'>$q</div>\n";
  }
  
  $result = Database::get_connection()->query($q);
  if (!$result) { echo "<a class='error'><b>Error</b> ".db_error()."</a>"; var_dump(debug_backtrace()); die(); }
  return $result;
}

function db_num_rows($result) {
  return $result->rowCount();
}

function db_fetch_row($result, $i='') {
  if ($i != '') {
    return $result->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_ABS, $i);
  }
  else {
    return $result->fetch(PDO::FETCH_LAZY);
  }
}

function db_fetch_assoc($result, $i='') {
  if ($i != '') {
    return $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $i);
  }
  else {
    return $result->fetch(PDO::FETCH_ASSOC);
  }
}

function db_fetch_object($result, $i='') {
  if ($i != '') {
    return $result->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_ABS, $i);
  }
  else {
    return $result->fetch(PDO::FETCH_OBJ);
  }
}

function db_last_insert_id() {
  return Database::get_connection()->lastInsertId();
}

function db_error() {
  $error = Database::get_connection()->errorInfo();
  return $error[0]." - ".$error[1]." - ".$error[2];
}

class Database {
  
  private static $connection;
  
  private function __construct() {
    self::$connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASSWORD);
  }
  
  public static function get_connection() {
    if (!isset(self::$connection) || self::$connection == null) {
      new Database();
    }
    return self::$connection;
  }
  
}

?>