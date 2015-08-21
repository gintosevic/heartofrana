<?php

try {
  session_start();
  session_destroy();
  echo json_encode(array('success' => true));
} catch (Exception $e) {
  echo json_encode(array('success' => false, 'message' => "An error occurred while logging out"));
}