<?php

/**
 * Class to simulate and perform fights
 */
class Battle {

  protected $defender;
  protected $attacker;
  protected $defender_bonus;
  protected $attacker_bonus;
  protected $winner;
  protected $loser;
  protected $survivor;
  protected $loss_ratio;
  protected $winner_experience_points;
  protected $loser_experience_points;

  public function __construct(Fightable $def, Fightable $att) {
    $this->defender = $def;
    $this->attacker = $att;
    $this->defender_bonus = 1.0;
    $this->attacker_bonus = 1.0;
    $this->winner = null;
    $this->loser = null;
    $this->survivor = null;
    $this->loss_ratio = 0.0;
    $this->winner_experience_points = 0;
    $this->loser_experience_points = 0;
  }

  public function get_defender() {
    return $this->defender;
  }

  public function get_attacker() {
    return $this->attacker;
  }

  public function get_winner() {
    return $this->winner;
  }

  public function get_winner_experience_points() {
    return $this->winner_experience_points;
  }

  public function get_loser() {
    return $this->loser;
  }

  public function get_loser_experience_points() {
    return $this->loser_experience_points;
  }

  public function get_survivor() {
    return $this->survivor;
  }

  public function get_loss_ratio() {
    return $this->loss_ratio;
  }

  public function compute_defense_probability() {
    $def_value = $this->defender->get_defense_value();
    $off_value = $this->attacker->get_attack_value();
    $max_value = max($def_value, $off_value) / 2.0;
    $def_value = max(0, $def_value - $max_value);
    $off_value = max(0, $off_value - $max_value);
    echo "$def_value against $off_value\n";
    $prob = ($def_value / ($def_value + $off_value));
    $prob = 1.0 - exp(-$prob);
    return $prob;
  }

  public function compute_attack_probability() {
    return 1.0 - $this->compute_defense_probability();
  }

  public function compute_defense_surviving_ratio() {
    $prob = $this->compute_defense_probability();
//    if ($prob > 0) {
    $margin = $draw / $prob;
  }

  public function simulate() {
    $prob = $this->compute_defense_probability();
    $draw = rand(0, 1e6) / 1e6;
//    echo "Draw = $draw\n";
    // Defender wins
    if ($draw < $prob) {
//      echo "Defender wins\n";
      $margin = 1 - ($draw / $prob);
//      echo "-> margin is $margin\n";
      $loss = (1 - $margin) * 2 * $this->attacker->get_attack_value();
      $this->loss_ratio = max(0.0, min(1.0, $loss / $this->defender->get_defense_value()));
      $this->winner = $this->defender;
      $this->loser = $this->attacker;
    }
    // Attacker wins
    else {
//      echo "Attacker wins\n";
      $prob = 1 - $prob;
      $draw = 1 - $draw;
      $margin = 1 - ($draw / $prob);
//      echo "-> margin is $margin\n";
      $loss = (1 - $margin) * 2 * $this->defender->get_defense_value();
      $this->loss_ratio = max(0.0, min(1.0, $loss / $this->attacker->get_attack_value()));
      $this->winner = $this->attacker;
      $this->loser = $this->defender;
    }
    $this->winner_experience_points = $this->loser->get_combat_value();
    $this->loser_experience_points = intval(XP_LOSER_MALUS * min($this->winner_experience_points, $this->get_loss_ratio() * $this->winner->get_combat_value()));
    $this->survivor = clone $this->winner;
    $this->survivor->decrease_power(1.0 - $this->loss_ratio);
  }

  private function build_defeat_message($def_or_att) {
    $planet_html = $this->get_attacker()->get_arrival_planet()->to_html();
    $player_html = $this->get_winner()->get_owner()->to_html();
    $xp = $this->get_loser_experience_points();
    $loss = round($this->get_loss_ratio() * 100);
    $msg = "Your $def_or_att fleet was defeated at $planet_html against $player_html.<br>\n";
    $msg .= "You destroyed about <b>$loss%</b> of the enemy fleet and gained <b>$xp XPs</b>.<br>\n";
    $msg .= "For information, your strength was " . $this->get_loser()->to_html();
    return $msg;
  }

  private function build_victory_message($def_or_att) {
    $planet_html = $this->get_attacker()->get_arrival_planet()->to_html();
    $player_html = $this->get_loser()->get_owner()->to_html();
    $xp = $this->get_winner_experience_points();
    $loss = round($this->get_loss_ratio() * 100);
    $msg = "Congratulations! Your $def_or_att fleet won a battle at $planet_html against $player_html.<br>\n";
    $msg .= "You lost about <b>$loss%</b> of your troups and gained <b>$xp XPs</b>.<br>\n";
    $msg .= "For information, your strength was " . $this->get_winner()->to_html();
    return $msg;
  }

  public function apply_results() {
    // Reward players
    $winner_player = $this->get_winner()->get_owner();
    $winner_xp = $this->get_winner_experience_points();
    $loser_player = $this->get_loser()->get_owner();
    $loser_xp = $this->get_loser_experience_points();
    $winner_player->add_experience_points($winner_xp);
    $loser_player->add_experience_points($loser_xp);

    $resulting_fleet = null;
    if ($this->get_winner() == $this->get_attacker()) {
      // Announce the victory
      $winner_msg = $this->build_victory_message("attacking");
      $loser_msg = $this->build_defeat_message("defending");
      // Destroy all defenses of the loser
      if ($this->defender instanceof Planet) {
//        print_r($this->defender);
        if ($this->defender->has_owner_fleet()) {
          $this->defender->get_owner_fleet()->destroy();
        }
        $this->defender->set_building_points("starbase", 0);
      } elseif ($this->defender instanceof Fleet) {
//        print_r($this->defender->get_planet());
        $this->defender->destroy();
      }
      // Perform landing
      $resulting_fleet = $this->get_survivor()->perform_landing();

    } else {
      // Announce the victory
      $winner_msg = $this->build_victory_message("defending");
      $loser_msg = $this->build_defeat_message("attacking");
      // Apply losses on the initial planet or fleet
      if ($this->defender instanceof Planet) {
        // Apply the losses on the original planet
        $this->defender->replace($this->survivor);
      } elseif ($this->defender instanceof Fleet) {
        // Apply the lossers on the original fleet
        $this->defender->replace($this->survivor);
      }
    }

    // Announce the defeat
    $time = $this->get_attacker()->get_arrival_time();
    Event::create_and_save($winner_player->get_player_id(), "won_fight", "You won a battle", $winner_msg, $time);
    Event::create_and_save($loser_player->get_player_id(), "lost_fight", "You lost a battle", $loser_msg, $time);

    return $resulting_fleet;
  }

  public function to_string() {
    $str = "Fight:\n";
    $str .= "+ Attacker details: " . $this->attacker->to_string() . "\n";
    $str .= "+ Attacker attack value: " . $this->attacker->get_attack_value() . "\n";
//    $str .= "+ Attacker defense value: ".$this->attacker->get_defense_value()."\n";
    $str .= "+ Attack combat value: " . $this->attacker->get_combat_value() . "\n";
    $str .= "- Defender details: " . $this->defender->to_string();
    if ($this->defender instanceof Planet) {
      if ($this->defender->has_owner_fleet()) {
        $str .= " " . $this->defender->get_owner_fleet()->to_string() . " +";
      }
      $str .= " SB " . $this->defender->get_building_level("starbase");
    }
    $str .= "\n";
//    $str .= "- Starbase = ".$planet->get_starbase_defense_value()."\n";
    $str .= "- Defender defense value: " . $this->defender->get_defense_value() . "\n";
    $str .= "- Defender combat value: " . $this->defender->get_combat_value() . "\n";
    return $str;
  }

  public function print_results() {
    echo "Defense prob = " . $this->compute_defense_probability() . "\n";
    echo "Attack prob = " . $this->compute_attack_probability() . "\n";
    $this->simulate();

    echo "Winner is " . $this->get_winner()->to_string() . "\n";
    echo "Winner earned " . $this->get_winner_experience_points() . " XPs\n";
    echo "Loser earned " . $this->get_loser_experience_points() . " XPs\n";
    echo "Winner losses are about " . round(100 * $this->get_loss_ratio()) . "%\n";
    echo "Survivors are:\n";
    if ($this->get_winner() instanceof Planet) {
      if ($this->get_survivor()->has_owner_fleet()) {
        echo "\t- " . $this->get_survivor()->get_owner_fleet()->to_string() . "\n";
      }
      echo "\t- SB " . $this->get_survivor()->get_building_level('starbase') . "\n";
    } elseif ($this->get_winner() instanceof Fleet) {
      echo "\t- " . $this->get_survivor()->to_string() . "\n";
    }
  }

}
