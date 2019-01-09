<?php
  require "class.Backup.php";
  /* You can also use an autoloader. */

  $restore = Backup::create("./example");
  /* This function creates a ''.backup' file and returns filename */

  Backup::restore($restore);
  /* This function restores file. */
?>
