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

function list_team_mates() {
  $mates = array();
  for ($i = 2; $i < 20; $i*=3) {
    $mate = new Player($i);
    $mate->load();
    array_push($mates, $mate);
  }
  return array(
      "id" => 1,
      "tag" => "TUGA",
      "name" => "The United Galaxies",
      "url" => "http://tugaearthmoon.phpbb.so/forum",
      "score" => 10,
      "rank" => 1,
      "members" => $mates
    );
}

session_start();

print json_encode(list_team_mates());
?> 

