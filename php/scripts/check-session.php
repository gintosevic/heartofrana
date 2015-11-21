<?php

session_start();
if (isset($_SESSION['account'])) {
  //     print_r($_SESSION['account']);
  //     echo "\n<br>\n";
//           print_r($_SESSION['player']);
  //     echo "<br>\n";
  echo json_encode(array('success' => true));
} else {
  echo json_encode(array('success' => false, 'message' => "No active session"));
}