<?php

/* * ******************************************************************
 * Generic fleet
 * ****************************************************************** */

class Fleet extends Fightable {

  static $ALL_SHIPS = array("colonyships", "transports", "destroyers", "cruisers", "battleships");
  static $SHIP_BASE_PRICES = array("colonyships" => 60, "transports" => 60, "destroyers" => 30, "cruisers" => 240, "battleships" => 600);
  static $SHIP_ATTACK_VALUES = array("colonyships" => 0, "transports" => 0, "destroyers" => 2, "cruisers" => 8, "battleships" => 36);
  static $SHIP_DEFENSE_VALUES = array("colonyships" => 0, "transports" => 0, "destroyers" => 1, "cruisers" => 16, "battleships" => 24);
  static $FIXED_PRICE_SHIPS = array("colonyships", "transports");
  static $SHIP_PRICE_RATES = array(0 => 30, 1 => 30, 2 => 30, 3 => 30,
      4 => 29, 5 => 29, 6 => 29,
      7 => 28, 8 => 28, 9 => 28,
      10 => 27, 11 => 27, 12 => 27, 13 => 27,
      14 => 26, 15 => 26, 16 => 26,
      17 => 25, 18 => 25, 19 => 25,
      20 => 24, 21 => 24, 22 => 24, 23 => 24,
      24 => 23, 25 => 23, 26 => 23,
      27 => 22, 28 => 22, 29 => 22,
      30 => 21, 31 => 21, 32 => 21, 33 => 21,
      34 => 20, 35 => 20, 36 => 20,
      37 => 19, 38 => 19, 39 => 19,
      40 => 18, 41 => 18, 42 => 18, 43 => 18,
      44 => 17, 45 => 17, 46 => 17,
      47 => 16, 48 => 16, 49 => 16,
      50 => 15, 51 => 15, 52 => 15, 53 => 15,
      54 => 14, 55 => 14, 56 => 14,
      57 => 13, 58 => 13, 59 => 13,
      60 => 12, 61 => 12, 62 => 12, 63 => 12,
      64 => 11, 65 => 11, 66 => 11,
      67 => 10, 68 => 10, 69 => 10,
      70 => 9, 71 => 9, 72 => 9, 73 => 9,
      74 => 8, 75 => 8, 76 => 8,
      77 => 7, 78 => 7, 79 => 7,
      80 => 6, 81 => 6, 82 => 6, 83 => 6,
      84 => 5, 85 => 5, 86 => 5,
      87 => 4, 88 => 4, 89 => 4,
      90 => 3, 91 => 3, 92 => 3, 93 => 3,
      94 => 2, 95 => 2, 96 => 2,
      97 => 1, 98 => 1, 99 => 1
  );

  static function get_ship_price($type, $eco_level) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No ship called $type.");
    }
    $eco_level = min($eco_level, max(array_keys(Fleet::$SHIP_PRICE_RATES)));
    $price = Fleet::$SHIP_BASE_PRICES[$type];
    if (array_search($type, Fleet::$FIXED_PRICE_SHIPS) === false) {
      $price *= (Fleet::$SHIP_PRICE_RATES[$eco_level] / Fleet::$SHIP_BASE_PRICES["destroyers"]);
    }
    return $price;
  }

  protected $fleet_id;
  protected $ships;
  // Ownable
  protected $owner_id;
  protected $owner;

  function load_owner() {
    if ($this->get_owner_id() !== null) {
      $this->set_owner(new Player($this->get_owner_id()));
    }
  }

  function set_owner_id($player_id) {
    $this->owner_id = $player_id;
    $this->owner = null;
  }

  public function get_owner_id() {
    return $this->owner_id;
  }

  public function has_owner() {
    return ($this->get_owner_id() !== null);
  }

  public function get_owner() {
    if ($this->has_owner() && $this->owner === null) {
      $this->owner = new Player($this->get_owner_id());
      $this->owner->load();
    }
    return $this->owner;
  }
  
  // Fleet

  function __construct($id = null) {
    parent::__construct();
    $this->owner_id = null;
    $this->owner = null;
    $this->fleet_id = $id;
    $this->ships = [];
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = 0;
    }
  }

  function replace(Fleet $fleet) {
    $this->set_owner_id($fleet->get_owner_id());
    if ($fleet->owner !== null) {
      $this->owner = $fleet->owner;
    }
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->set_ships($ship, $fleet->get_ships($ship));
    }
  }

  function load() {
    $result = db_query("SELECT * FROM Fleet WHERE fleet_id = " . $this->fleet_id);
    $row = db_fetch_assoc($result);
    $this->owner_id = $row['owner'];
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->ships[$ship] = $row[$ship];
    }
  }

  function save() {
    if ($this->fleet_id === null) {
      $id = "DEFAULT";
    } else {
      $id = $this->fleet_id;
    }
    if ($this->fleet_id !== null && $this->is_empty()) {
      $result = db_query("DELETE FROM Fleet WHERE fleet_id = $id");
    } else {
      $result = db_query("INSERT INTO Fleet VALUES($id, " . $this->owner_id . ", " . $this->ships['colonyships'] . ", " . $this->ships['transports'] . ", " . $this->ships['destroyers'] . ", " . $this->ships['cruisers'] . ", " . $this->ships['battleships'] . ") ON DUPLICATE KEY UPDATE owner = " . $this->owner_id . ", colonyships = " . $this->ships['colonyships'] . ", transports = " . $this->ships['transports'] . ", destroyers = " . $this->ships['destroyers'] . ", cruisers = " . $this->ships['cruisers'] . ", battleships = " . $this->ships['battleships']);
    }
    if ($this->fleet_id === null) {
      $this->fleet_id = db_last_insert_id();
    }
  }

  function destroy() {
    if ($this->fleet_id !== null) {
      db_query("DELETE FROM Fleet WHERE fleet_id = " . $this->fleet_id);
    }
  }

  public function set_owner(\Player $pl) {
    if ($this->get_owner_id() !== $pl->get_player_id()) {
      $this->owner_id = $pl->get_player_id();
      $this->owner = $pl;
      $pl->add_fleet($this);
    }
  }

  function get_fleet_id() {
    return $this->fleet_id;
  }

  function set_ships($type, $n) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No ship called $type.");
    }
    $this->ships[$type] = $n;
  }

  function get_ships($type) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No ship called $type.");
    }
    return $this->ships[$type];
  }

  function add_ships($type, $n) {
    if (array_search($type, Fleet::$ALL_SHIPS) === false) {
      die(__FILE__ . ": line " . __LINE__ . ": No ship called $type.");
    }
    $this->ships[$type] = $this->ships[$type] + $n;
  }

  function is_empty() {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($this->get_ships($ship) > 0) {
        return false;
      }
    }
    return true;
  }

  function get_attack_value() {
    $value = 0;
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $value += $this->get_ships($ship) * Fleet::$SHIP_ATTACK_VALUES[$ship];
    }
    return $value;
  }

  function get_defense_value() {
    $value = 0;
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $value += $this->get_ships($ship) * Fleet::$SHIP_DEFENSE_VALUES[$ship];
    }
    return $value;
  }

  function get_combat_value() {
    return $this->get_attack_value() + $this->get_defense_value();
  }

  function merge(Fleet $fleet) {
    foreach (Fleet::$ALL_SHIPS as $ship) {
      $this->set_ships($ship, $this->get_ships($ship) + $fleet->get_ships($ship));
      $fleet->set_ships($ship, 0);
    }
  }

  function to_string() {
    $str = "Fleet { ";
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($this->ships[$ship] > 0) {
        $str .= $ship . ":" . $this->ships[$ship] . " ";
      }
    }
    $str .= "}";
    return $str;
  }

  function to_html() {
    $str = "<span class='fleet'>\n<ul>\n";
    foreach (Fleet::$ALL_SHIPS as $ship) {
      if ($this->ships[$ship] > 0) {
        $str .= "<li>" . $this->ships[$ship] . " " . ($this->ships[$ship] > 1 ? $ship : substr($ship, 0, -1)) . "</li>\n";
      }
    }
    $str .= "</ul>\n</span>\n";
    return $str;
  }

}

?>
