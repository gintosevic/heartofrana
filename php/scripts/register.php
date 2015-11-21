<?php

require '../lib/common.php';

function isValid($code, $ip = null) {
  if (empty($code)) {
    return false; // Si aucun code n'est entré, on ne cherche pas plus loin
  }
  $params = [
      'secret' => '6LeuiAsTAAAAAGP55akxfVE2juKb3OSgrEZ7bFlR',
      'response' => $code
  ];
  if ($ip) {
    $params['remoteip'] = $ip;
  }
  $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
  if (function_exists('curl_version')) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Evite les problèmes, si le ser
    $response = curl_exec($curl);
  } else {
    // Si curl n'est pas dispo, un bon vieux file_get_contents
    $response = file_get_contents($url);
  }

  if (empty($response) || is_null($response)) {
    return false;
  }

  $json = json_decode($response);
  return $json->success;
}

$data = json_decode(file_get_contents('php://input'), true);
error_log(($data));
if (isset($data['username'])
  && isset($data['password'])
  && isset($data['email'])
  && isValid($data['g-recaptcha-response'])) {
  try {
    $galaxy = new Galaxy();
    $galaxy->add_player($data['username'], $data['password'], $data['email']);
echo json_encode(array('success' => true));
  } catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
  }
}
else {
  echo json_encode(array('success' => false, 'message' => "Username or password is missing"));
}

