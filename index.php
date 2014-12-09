<?php
require "lib/common.php";
build_header('basic/basic.css');
?>
<table>
<tr>
<td background='basic/img/logos/Veil_nebula_modified.png' width='520px' height='320px' valign='bottom' style='padding: 20px;'>
  <span style='letter-spacing: 0.1em; font-size: 4em; font-weight: bold;'>Heart of Rana</span><br>
  <span style='letter-spacing: 0.1em; font-size: 1em; font-weight: bold;'>Version 0.1</span>
</td>

</tr>
<tr><td>
    
    <div class='box'>
      <h2>Existing account</h2>
      <form action="basic/news.php" method="post">
      Login: <input name="login" size='18'>
      Password: <input type="password" name="password" size='18'>
      <input type="submit" value="Login">
      </form>
    </div>
    
</td></tr>
<tr><td>
    
    <div class='box'>
      <h2>New account</h2>
      <form action="basic/news.php" method="post">
        Email address: <input name="login" size='30'><br>
      Login: <input name="login" size='18'>
      Password: <input type="password" name="password" size='18'>
      <input type="submit" value="Login">
      </form>
    </div>
    
</td></tr>
</table>
</body>
</html>