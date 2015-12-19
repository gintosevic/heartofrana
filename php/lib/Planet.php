<?php

/**
 * Abstract class to model planets
 */
abstract class Planet extends Fightable implements JsonSerializable {

  static $ALL_BUILDINGS = array("farm", "factory", "cybernet", "lab", "starbase");

  public abstract function replace(Planet $planet);

  public abstract function save();

  public abstract function load();

  public abstract function load_owner_fleet();

  public abstract function load_sieging_fleet();

  public abstract function get_owner_ships($type);

  public abstract function get_sieging_ships($type);

  public abstract function get_sid();

  public abstract function get_position();

  public abstract function set_bonus($bool);

  public abstract function is_bonus();

  public abstract function set_building_points($type, $n);

  public abstract function set_ship_points($type, $n);

  public abstract function add_building_points($type, $n);

  public abstract function add_ship_points($type, $n);

  public abstract function get_building_points($type);

  public abstract function get_ship_points($type);

  public abstract function get_building_level($type);

  public abstract function decrease_building_level($type);

  public abstract function set_building_level($type, $n);

  public function get_starbase_defense_value() {
    $level = $this->get_building_level('starbase');
    return starbase_level_to_defense_value($level);
  }

  public function get_defense_value() {
    $value = $this->get_starbase_defense_value();
    if ($this->has_owner_fleet()) {
      $value += $this->get_owner_fleet()->get_defense_value();
    }
    return $value;
  }

  public abstract function set_population_points($n);

  public abstract function set_population_level($n);

  public abstract function get_population_points();

  public abstract function get_population_level();

  public abstract function decrease_population_level($n_levels);

  public abstract function set_production_points($n);

  public abstract function decrease_production_points($n);

  public abstract function increase_production_points($n);

  public abstract function get_production_points();

  public abstract function upgrade_building($type, $amount);

  public abstract function upgrade_ship($type, $amount);

  public function set_owner(Player $pl) {
    if ($this->get_owner_id() !== $pl->get_player_id()) {
      parent::set_owner($pl);
      $pl->add_planet($this);
    }
  }

  public abstract function has_owner_fleet();

  public abstract function has_sieging_fleet();

  public abstract function get_owner_fleet_id();

  public abstract function get_sieging_fleet_id();

  public abstract function get_owner_fleet();

  public abstract function get_sieging_fleet();

  public abstract function set_owner_fleet_id($fleet_id);

  public abstract function set_owner_fleet(Fleet $fleet);

  public abstract function unset_owner_fleet();

  public abstract function set_sieging_fleet_id($fleet_id);

  public abstract function set_sieging_fleet(Fleet $fleet);

  public abstract function unset_sieging_fleet();

  public function to_string() {
    return "SID" . $this->get_sid() . " #" . $this->get_position();
  }

  public function to_html() {
    return "<a class='planet' href='view_planet.php?sid=" . $this->get_sid() . "&position=" . $this->get_position() . "'>" . $this->to_string() . "</a>";
  }

  public function jsonSerialize() {
    $jsonObject = array("systemId" => $this->get_sid(),
        "position" => intval($this->get_position()),
        "isBonus" => $this->is_bonus(),
        "productionPoints" => $this->get_production_points()
    );

    $current_points = $this->get_population_points();
    $current_level = $this->get_population_level();
    $next_level_points = population_level_to_points($current_level + 1);
    $next_level_step = $next_level_points - population_level_to_points($current_level);
    $remaining_points = $next_level_points - $current_points;
    $progress = round((1 - ($remaining_points / $next_level_step)) * 100);
    $jsonObject["population"]["level"] = $current_level;
    $jsonObject["population"]["currentPoints"] = $current_points;
    $jsonObject["population"]["nextLevelPoints"] = $next_level_points;
    $jsonObject["population"]["nextLevelStep"] = $next_level_step;
    $jsonObject["population"]["remainingPoints"] = $remaining_points;
    $jsonObject["population"]["progress"] = $progress;

    foreach (Planet::$ALL_BUILDINGS as $building_type) {
      $current_points = $this->get_building_points($building_type);
      $current_level = $this->get_building_level($building_type);
      $next_level_points = building_level_to_points($current_level + 1);
      $next_level_step = $next_level_points - building_level_to_points($current_level);
      $remaining_points = $next_level_points - $current_points;
      $progress = floor((1 - ($remaining_points / $next_level_step)) * 100);
      $jsonObject["buildings"][$building_type]["level"] = $current_level;
      $jsonObject["buildings"][$building_type]["currentPoints"] = $current_points;
      $jsonObject["buildings"][$building_type]["nextLevelPoints"] = $next_level_points;
      $jsonObject["buildings"][$building_type]["nextLevelStep"] = $next_level_step;
      $jsonObject["buildings"][$building_type]["remainingPoints"] = $remaining_points;
      $jsonObject["buildings"][$building_type]["progress"] = $progress;
    }

    $player = $_SESSION['player'];
    $this->load_owner_fleet();
    foreach (Fleet::$ALL_SHIPS as $ship_type) {
      if ($player->has_enabled($ship_type)) {
        $quantity = $this->get_ship_points($ship_type);
        $price = Fleet::get_ship_price($ship_type, $player->get_science_level("economy"));
        $remaining_points = $price - $quantity;
        $progress = floor(($quantity / $price) * 100);
        $jsonObject["ships"][$ship_type]["quantity"] = $quantity;
        $jsonObject["ships"][$ship_type]["price"] = $price;
        $jsonObject["ships"][$ship_type]["remainingPoints"] = $remaining_points;
        $jsonObject["ships"][$ship_type]["progress"] = $progress;
      }
    }

    return $jsonObject;
  }

}

?>
