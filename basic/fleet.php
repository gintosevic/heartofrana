<?php
require("../lib/common.php");


function build_fleet() {
  echo <<<EOL
<div class="tab" id="fleet_tab">
<h1>Fleet</h1>
<div class="todo">Not implemented yet.</div>
</div>
EOL;
}

build_header("basic.css");
if (!check_login()) {
  print_login_form();
}
else {
  build_menu();
  build_fleet();
}
build_footer();

?> 
