<?php
require "lib/common.php";
build_header('basic/basic.css');
?>
<table>
<tr>
<td span=2>
<h1>Heart of Rana</h1>
Version 0.1
</td>

</tr>
<form action="basic/news.php" method="post">
<tr>
<td>
Login: <input name="login">
Password: <input type="password" name="password">
</td>
<td>
<input type="submit" value="Login">
</td>
</tr>
</form>
</table>
</body>
</html>