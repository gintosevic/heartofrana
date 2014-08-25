<?php
require("../lib/common.php");


function build_trade() {
  echo <<<EOL
<div class="tab" id="trade_tab">
<h1>Trade</h1>
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
  build_trade();
}
build_footer();

?> 
