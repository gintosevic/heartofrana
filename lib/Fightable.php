<?php

/**
 * Abstract class for all objects with fight abilities
 */
abstract class Fightable extends Owned {

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
}
