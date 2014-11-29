<?php

/**
 * Abstract class for all object which are owner by a player
 */
class Ownable {

  protected $owner_id;
  protected $owner;

  public function __construct() {
    $this->owner_id = null;
    $this->owner = null;
  }

  function load_owner() {
    if ($this->owner_id !== null) {
      $this->owner = new Player($this->owner_id);
    }
  }

  function set_owner_id($player_id) {
    $this->owner_id = $player_id;
    $this->owner = null;
  }

  public function set_owner(Player $pl) {
    if ($this->owner_id !== $pl->get_player_id()) {
      $this->owner_id = $pl->get_player_id();
      $this->owner = $pl;
    }
  }

  public function get_owner_id() {
    return $this->owner_id;
  }

  public function has_owner() {
    return ($this->owner_id !== null);
  }

  public function get_owner() {
    if ($this->has_owner() && $this->owner === null) {
      $this->owner = new Player($this->owner_id);
      $this->owner->load();
    }
    return $this->owner;
  }

}
