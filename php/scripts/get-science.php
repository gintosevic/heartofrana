<?php

/*
 * Copyright (C) 2015 gint
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


require("../lib/common.php");

function screen_science($player) {
  $result["sciences"] = array();
  foreach (Player::$ALL_SCIENCES as $type) {
    $current_points = $player->get_science_points($type);
    $current_level = $player->get_science_level($type);
    $next_level_points = science_level_to_points($current_level + 1);
    $remaining_points = $next_level_points - $current_points;
    $progress = round((1-($remaining_points/$next_level_points))*100);
    $active = false;
    if ($type == $player->get_current_science()) {
      $active = true;
    }
    $science_array = array(
        "currentPoints" => $current_points,
        "currentLevel" => $current_level,
        "nextLevelPoints" => $next_level_points,
        "remainingPoints" => $remaining_points,
        "progress" => $progress,
        "isActive" => $active
    );
    $result["sciences"][$type] = $science_array;
  }

  $current_points = $player->get_culture_points();
  $current_level = $player->get_culture_level();
  $next_level_points = culture_level_to_points($current_level + 1);
  $remaining_points = $next_level_points - $current_points;
  $progress = round((1 - ($remaining_points / $next_level_points)) * 100);
  $science_array = array(
      "currentPoints" => $current_points,
      "currentLevel" => $current_level,
      "nextLevelPoints" => $next_level_points,
      "remainingPoints" => $remaining_points,
      "progress" => $progress
  );
  $result["culture"] = $science_array;
  return $result;
}

session_start();

print json_encode(screen_science($_SESSION['player']));

?> 

