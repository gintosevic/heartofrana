<?php

define("SPIRAL_WINDOW", 3); // Number of simultaneous non-full systems in the spiral tail
define("GALAXY_BLOCK_SIZE", 3); // Block of NxN where only 1 system is built
define("GALAXY_DENSITY", 8); // 4 means that there will be 4 systems encircling Rana
define("PLAYERS_PER_SYSTEM", 4); // Initial number of players per system
define("BONUS_PLANETS_PER_SYSTEM_MEAN", 2); // Average number of bonus planets per system
define("BONUS_PLANETS_PER_SYSTEM_STDDEV", 0.5); // Variance of the number of bonus planets per system
define("NEW_HOME_SEM_KEY", 0); // Key for the semaphore on new home creation
define("PLANET_DEVELOPMENT_INITIAL_COST", 5); // Cost to reach level 1 of a building
define("PLANET_DEVELOPMENT_COMMON_RATIO", 1.5); // Geometric progression ratio of required productions points from level i to level i+1
define("PLAYER_DEFAULT_SCIENCE", "biology"); // Initial science under study for all players
define("CRUISER_TRIGGER_SCIENCE", "mathematics"); // Science to develop to build cruisers
define("CRUISER_TRIGGER_LEVEL", 15); // Science level to reach to build cruisers
define("BATTLESHIP_TRIGGER_SCIENCE", "physics"); // Science to develop to build battleships
define("BATTLESHIP_TRIGGER_LEVEL", 15); // Science level to reach to build battleships

?>