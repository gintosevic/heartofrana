<?php

require '../lib/common.php';

session_start();
if (isset($_POST['username']) && isset($_POST['password'])) {
  try {
    session_destroy();
    session_start();
    $_SESSION['account'] = new Account($_POST['username'], $_POST['password']);
    $pl = new Player();
    $pl->set_name($_POST['username']);
    $pl->load();
    $pl->load_planets();
    $pl->load_fleets();
// 	foreach ($pl->get_planets() as $planet) {
// 	  $planet->load_owner_fleet();
// 	  $planet->load_sieging_fleet();
// 	}
    $_SESSION['player'] = $pl;
    $_SESSION['galaxy'] = new Galaxy();
    //       print_r($_SESSION);
// 	print_r($_SESSION['player']);
    echo json_encode(array('success' => true));
  } catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => "Username or password is incorrect"));
  }
}
else {
  echo json_encode(array('success' => false, 'message' => "Username or password is missing"));
}

