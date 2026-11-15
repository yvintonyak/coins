<html>

  <head>
    <title>Imperium Romanum</title>
    <link rel="icon" type="image/vnd.microsoft.icon" href="icon.ico">
  </head>

  <body>
  
  <div>
  
<?php

  // html code
  $output = '';

  // get parameters
  $city = '';
  if (isset($_GET['city']))
    $city = $_GET['city'];

  $region = '';
  if (isset($_GET['region']))
    $region = $_GET['region'];

  switch ($region)
  {
    case 'Lydia':
    case 'Ionia':
        $region = 'Asia';
    break;

    case 'Phrygia':
        $region = 'Galatia';
    break;
  }

  $map = simplexml_load_file('data/Map.svg');

  // $map['width'] = '100%';
  // $map['height'] = 'auto';
  $map['viewBox'] = '0 0 2185 1817';

  // select region
  foreach ($map->g as $g)
  {
    if ((string)$g['id'] == 'layer15')
    {
      foreach($g->path as $path)
      {
        if ((string)$path->title == $region)
        {
          $path['style'] = str_replace('#fefee9', '#3070cc', (string)$path['style']);
          break;
        }
      }
      break;
    }
  }
  
  // change region colour
  foreach ($map->text as $text)
  {
    if ((string)$text['title'] == $region)
      $text['style'] = 'stroke: #ffffff;';
  }

  // display city pin
  foreach ($map->g as $g)
  {
    if ((string)$g['id'] == 'city')
    {
      foreach($g->use as $use)
      {
        if ((string)$use['title'] === $city)
        {
          $use['href'] = '#pin';
          break;
        }
      }
      break;
    }
  }

  $output .= $map->asXML();
  
  echo $output;

?>

  </div>

  </body>
</html>
