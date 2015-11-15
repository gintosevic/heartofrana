<?php

require("../lib/common.php");

session_start();

error_log(json_encode($_SESSION['player']->list_visible_systems()));
print json_encode($_SESSION['player']->list_visible_systems());

