<?php

/**
 * Alliances are groups of players that can do things together, eg, share information, defend each other, etc.
 */

class Alliance {
  protected $alliance_id;
  protected $tag;
  protected $name;
  protected $members;
  
  /**
   * Constructor to load a given player based on his name
   */
  function __construct($alliance_id = null) {
    $this->alliance_id = $alliance_id;
    $this->tag = null;
    $this->name = null;
    $this->members = array();
  }

  function save_members() {
    foreach ($this->members as $member) {
      $member->save();
    }
  }

  function save() {
    $this->save_race();

    // New alliance
    if ($this->alliance_id == null) {
      $result = db_query("INSERT INTO Alliance VALUES (DEFAULT, '" . $this->tag . "', " . $this->name . ")");
      $this->alliance_id = db_last_insert_id();
    }
    // Existing player
    else {
      $result = db_query("UPDATE Player SET race_id = " . $this->race_id . ", points = " . $this->points . ", rank = " . $this->rank . ", experience = " . $this->experience_points . ", culture = " . $this->culture_points . ", current_science = '" . $this->current_science . "', tag = " . ($this->tag == null ? "NULL" : $this->tag) . " WHERE player_id = " . $this->get_player_id());
    }

    $this->save_science();
  }

  function load() {
    $filter = "";
    if ($this->player_id !== null) {
      $filter = "player_id = " . $this->player_id;
    } elseif ($this->name !== null) {
      $filter = "name = '" . $this->name . "'";
    } else {
      throw new Exception("Impossible to load requested player.");
    }
    $result = db_query("SELECT * FROM Player WHERE $filter");
    $row = db_fetch_assoc($result);
    $this->player_id = $row['player_id'];
    $this->name = $row['name'];
    $this->race_id = $row['race_id'];
    $this->points = $row['points'];
    $this->rank = $row['rank'];
    $this->experience_points = $row['experience'];
    $this->culture_points = $row['culture'];
    $this->current_science = $row['current_science'];
    $this->tag = $row['tag'];

    $result = db_query("SELECT * FROM Race WHERE race_id = " . $this->race_id);
    $this->race = db_fetch_assoc($result);

    $result = db_query("SELECT * FROM Science WHERE player_id = " . $this->player_id);
    $row = db_fetch_assoc($result);
    $this->science_points = array();
    foreach (Player::$ALL_SCIENCES as $science) {
      $this->science_points[$science] = $row[$science];
    }
  }
  
}
