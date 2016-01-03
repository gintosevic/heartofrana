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

function get_artefact_prices() {
  $prices = array(
      "Production Point" => 0.9,
      "Supply Unit" => 700,
      "Basalt Monolith 1" => 2000,
      "Astrolabe 1" => 1000,
      "Celestrial 1" => 2000,
      "Crystal Rod 1" => 4000,
      "Charcoal Diamond 1" => 3000,
      "Memory Jar 1" => 4000,
      "Heart of Rana 1" => 10000,
      "Basalt Monolith 2" => 6000,
      "Astrolabe 2" => 3000,
      "Celestrial 2" => 6000
  );
  return $prices;
}

session_start();

print json_encode(get_artefact_prices());

?> 

