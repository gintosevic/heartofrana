<?php

class Account {
  var $login;
  var $password_hash;
  var $email;
  
  /**
   * Constructor to create a new account
   */
  function __construct($l, $p, $e = '') {
    // Creation of a new account
    if ($e != '') {
      $ph = password_hash($p, PASSWORD_DEFAULT);
      $result = db_query("INSERT INTO Account VALUES ('$l', '$ph', '$e')");
      if (db_num_rows($result) == 1) {
	$this->login = $l;
	$this->password_hash = $ph;
	$this->email = $e;
      }
      else {
	throw Exception("Impossible to create new account");
      }
    }
    // Connection attempt
    else {
      $result = db_query("SELECT * FROM Account WHERE login = '$l';");
      if (db_num_rows($result) > 0) {
	$row = db_fetch_assoc($result);
	if (password_verify($p, $row['password_hash'])) {
	  $this->login = $row['login'];
	  $this->password_hash = $row['password_hash'];
	  $this->email = $row['email'];
	}
	else {
	  throw new Exception("Invalid credentials");
	}
      }
      else {
	throw new Exception("Invalid credentials");
      }
    }
  }
  
}

?>