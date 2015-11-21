<?php

/**
 * Alliances are groups of players that can do things together, eg, share information, defend each other, etc.
 */
class Alliance {

  protected $tag;
  protected $name;
  protected $members;

  /**
   * Constructor to load a given player based on his name
   */
  public function __construct($tag, $name = null) {
    $this->tag = $tag;
    $this->name = $name;
    $this->members = null;

    if ($name === null) {
      $this->load();
    } else {
      die("Unable to create the new alliance \"$tag\" without an associated name.\n");
    }
  }

  protected function save_members() {
    foreach ($this->members as $member) {
      $member->save();
    }
  }

  public function save() {
    $result = db_query("INSERT INTO Alliance VALUES ('" . $this->tag . "', '" . $this->name . "')  ON DUPLICATE KEY UPDATE tag = '" . $this->tag . "', name = '" . $this->name . "'");
    $this->save_members();
  }

  public function load() {
    $result = db_query("SELECT * FROM Alliance WHERE tag = '" . $this->tag . "'");
    $row = db_fetch_assoc($result);
    $this->name = $row['name'];
  }

  protected function load_members() {
    $result = db_query("SELECT player_id FROM Player WHERE tag = '" . $this->tag . "'");
    $n_results = db_num_rows($result);
    $this->members = array();
    for ($i = 0; $i < $n_results; $i++) {
      $row = db_fetch_assoc($result, $i);
      array_push($this->members, $this->$row['player_id']);
    }
  }
  
  public function get_tag() {
    return $this->tag;
  }
  
  public function get_name() {
    return $this->name;
  }

  public function get_members() {
    if ($this->members === null) {
      $this->load_members();
    }
    return $this->members;
  }

  public function add_member(Player $player) {
    if (!$player->has_alliance()) {
      $player->set_alliance($this);
    } else {
      die("Unable to leave an alliance.");
    }
  }

  public function remove_member(Player $player) {
    if ($player->has_alliance() && $player->get_tag() === $this->tag) {
      $player->unset_alliance();
      $n = $this->size();
      for ($i = 0; $i < $n; $i++) {
        if ($this->get_members()[$i]->get_player_id() === $player->get_player_id()) {
          unset($this->get_members()[$i]);
        }
      }
    }
  }

  public function size() {
    return count($this->get_members());
  }

}
