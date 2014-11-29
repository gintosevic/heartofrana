<?php

/**
 * Abstract class for all objects with fight abilities
 */
abstract class Fightable extends Ownable {

    public function __construct() {
      parent::__construct();
    }
    
    public function get_attack_value() {
        return 0;
    }

    public function get_defense_value() {
        return 0;
    }

    public function get_combat_value() {
        return $this->get_attack_value() + $this->get_defense_value();
    }

    public function get_effective_attack_value() {
        return $owner->get_attack_multiplier() * $this->get_attack_value();
    }

    public function get_effective_defense_value() {
        return $owner->get_defense_multiplier() * $this->get_defense_value();
    }
    
    /**
     * 
     * @param type $ratio Ratio to be applied on the power (0.0 means "destroy all forces", 1.0 means "no decrease")
     */
    public function decrease_power($ratio) {
      $ratio = max(0.0, min(1.0, $ratio));
      $fleet = null;
      if ($this instanceof Fleet) {
        $fleet = $this;
      }
      elseif ($this instanceof Planet) {
        // Memorizing fleet if present (will be processed later)
        if ($this->has_owner_fleet()) {  
          $this->set_owner_fleet(clone $this->get_owner_fleet());
          $fleet = $this->get_owner_fleet();
        }
        // Decrease starbase
        $cur_def_val = $this->get_starbase_defense_value();
        $prev_def_val = $cur_def_val;
        $def_val_threshold = $ratio*$cur_def_val;
        $cur_sb_level = $this->get_building_level('starbase');
        while ($this->get_starbase_defense_value() > $def_val_threshold) {
          // Decrease starbase level and check defense value
          $cur_sb_level--;
          $this->set_building_level('starbase', $cur_sb_level);
          $prev_def_val = $cur_def_val;
          $cur_def_val = $this->get_defense_value();
        }
        // Adjust
        if ($prev_def_val != $cur_def_val) {
          $delta_def_val = $prev_def_val - $cur_def_val;
          $missing_def_val = $def_val_threshold - $cur_def_val;
          $ratio_def_val = $missing_def_val/$delta_def_val;
          $next_lvl_pps = building_level_to_points($cur_sb_level+1) - building_level_to_points($cur_sb_level);
          $this->add_building_points('starbase', round($next_lvl_pps*$ratio_def_val));
        }
      }
      else {
        die("Unknown type of Fightable object in method Fight::decrease_power.\n");
      }

      if ($fleet != null) {
        foreach (Fleet::$ALL_SHIPS as $type) {
          $n_ships = $fleet->get_ships($type);
          $fleet->set_ships($type, round($n_ships*$ratio));
        }
      }
    }
}
