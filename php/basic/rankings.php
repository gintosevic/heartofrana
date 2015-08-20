<?php
require("../lib/common.php");


function build_alliance() {
  echo <<<EOL
<div class="tab" id="alliance_tab">
<h1>Rankings</h1>
<div class="todo">Not implemented yet.</div>
</div>
EOL;
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  check_fleet_landing($_SESSION['player']);
  build_menu();
  build_alliance();
}
build_footer();

?> 
