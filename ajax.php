<?php

  include "session.php";

  if (isset($_GET['info_state']))
  {
    $infoState = $_GET['info_state'];
    $_SESSION['info_state'] = $infoState;
  }
?>
