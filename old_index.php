<html ng-app="login">

  <head>
    <title>HoR0.1</title>
    <link rel="stylesheet" type="text/css" href="css/hor.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <!-- Bootstrap core CSS -->
    <link href="css/cyborg/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <script type="text/javascript" src="js/angular-1.4.3/angular.min.js"></script>
    <script type="text/javascript" src="js/login.js"></script>
  <center>

    <table>
      <tr>
        <td background='img/logos/Veil_nebula_modified.png' width='520px' height='320px' valign='bottom' style='padding: 20px;'>
          <span style='letter-spacing: 0.1em; font-size: 4em; font-weight: bold;'>Heart of Rana</span><br>
          <span style='letter-spacing: 0.1em; font-size: 1em; font-weight: bold;'>Version 0.1</span>
        </td>

      </tr>
    </table>
          <div class="container" style="max-width: 520px;">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="form-signin-heading">Existing account</h2>
              </div>
              <div class="panel-body" ng-controller="formController">
                <form class="form-signin" method="post">

                  <label for="login" class="sr-only">Login</label>
                  <input id="login" class="form-control" placeholder="Login" required autofocus>
                  <label for="password" class="sr-only">Password</label>
                  <input type="password" id="password" class="form-control" placeholder="Password" required>
                  <button class="btn btn-lg btn-primary btn-block" ng-controller="login">Login</button>
                </form>
              </div>
            </div>
          </div> <!-- /container -->

          <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
          <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>

    <!--          <div class='box'>
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
        </table>-->
  </body>
</html>