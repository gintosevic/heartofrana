<?php

/********************************************************************
 * Sieging fleet
 ********************************************************************/

class SiegingFleet extends RestingFleet {
  function set_planet(Planet $planet) {
    if ($planet->get_owner_id() !== $this->get_owner_id()) {
      $this->planet = $planet;
      $planet->set_sieging_fleet($this);
    }
    else {
      throw new Exception("Owner of the sieging fleet and of the planet must be different.");
    }
  }
}
