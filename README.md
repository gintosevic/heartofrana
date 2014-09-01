heartofrana
===========

Installation
------------
- Install a PHP5 server
- Install a MySQL server
- Run the script init.sh in the root directory
- Edit lib/credentials.php
- Run the web page populate.php
- Run the web page index.php and play

Main pages
----------
- populate.php: Script to randomly fill the database (all logins are "PlayerN" where N ranging from 1 to the number you have defined and passwords are all set to "toto").
- index.php: Main page, use any login and password as defined above.
- basic/: All in-game pages are here, created an alternative directory and link index.php to it if you want to try an other interface design.
- lib/: Libraries are here, PHP is object-oriented. So please try to encapsulate properly.
- lib/constants.php: All constants are defined and described here.

