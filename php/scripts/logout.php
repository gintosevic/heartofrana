<?php

try {
  session_start();
  session_destroy();
  echo json_encode(true);
} catch (Exception $e) {
  echo json_encode(false);
}