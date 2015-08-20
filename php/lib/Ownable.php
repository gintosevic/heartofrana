<?php

/**
 * Abstract class for all object which are owner by a player
 */
abstract class Ownable {

  abstract function load_owner();

  abstract function set_owner_id($player_id);

  abstract public function set_owner(Player $pl);

  abstract public function get_owner_id();

  abstract public function has_owner();

  abstract public function get_owner();

}
