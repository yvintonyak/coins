<?php include "session.php"; ?>

<html>
  <head>
    <title>My Coin Collection</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=<?php echo filemtime('styles.css'); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="icon.ico">
  </head>
  <body>
  
    <?php define('CorrectAccess', true); ?>
  
    <?php include 'collection/coins.php'; ?>
    <?php include 'collection/utils.php'; ?>
    
    <div class="head">

    <div class="menu">
      <?php include 'collection/menu.php'; ?>
    </div>

    <div class="body">

      <div class="nav">
        <?php include 'collection/nav.php'; ?>
      </div>

      <div class="data">
        <?php include 'collection/data.php'; ?>
      </div>
      
    </div>
    </div>
    
  </body>
</html>
