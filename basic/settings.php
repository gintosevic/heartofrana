<?php
require("../lib/common.php");


function build_alliance() {
  echo <<<EOL
<div class="tab" id="alliance_tab">
<h1>Settings</h1>
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
  build_alliance();
}
build_footer();

?> 
