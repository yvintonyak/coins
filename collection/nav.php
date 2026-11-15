<?php

  $RootPath = $_SERVER['DOCUMENT_ROOT'];
  include $RootPath . '/session.php';
  
  if (!defined('CorrectAccess')) die('ERROR: ACCESS DENIED');

  // html code
  $output = '';
  
  $output .= '<script type="text/javascript">';
  $output .= 'window.onload = function () { var element = document.getElementById("scroll"); if (element) element.scrollIntoView(); }';
  $output .= '</script>';

  $view = '';
  if (isset($_SESSION['session_view']))
    $view = $_SESSION['session_view'];
    
  if ($view == '')
    $view = 'coins';
  
  switch ($view)
  {
    case 'coins':
      CoinsNavigation();
      break;
    
    case 'emperors':
      EmperorsNavigation();
      break;

    case 'deities':
      DeitiesNavigation();
      break;
  }
  
  function CoinsNavigation()
  {
    global $output;
    
    // get menu (filter) parameters
    $selectedSet = _get_param('set', '');
    $selectedSubset = _get_param('subset', '');
  
    $sets = Collection::Sets();

    // menu item 'All'
    $params = _params();
    $output .= _ref('?'. http_build_query($params), 'All', ($selectedSet == '' ? 'active' : ''));

    // menu items for groups
    foreach ($sets as $set)
    {
      $params['set'] = $set->Name;
      unset($params['subset']);
      
      $selectedBegin = '';
      $selectedEnd = '';
      $selectedClass = '';
      if ($selectedSet != '' && $selectedSubset == '' && $selectedSet == $set->Name)
      {
        $selectedBegin = '<div id="scroll">';
        $selectedEnd = '</div>';
        $selectedClass = 'active';
      }

      $output .= $selectedBegin . _ref('?'. http_build_query($params), $set->Name, $selectedClass) . $selectedEnd;

      foreach ($set->Subsets as $subset)
      {
        $params['subset'] = $subset;

        $selectedBegin = '';
        $selectedEnd = '';
        $selectedClass = 'subset';
        if ($selectedSet != '' && $selectedSubset != '' && $selectedSet == $set->Name && $selectedSubset == $subset)
        {
          $selectedBegin = '<div id="scroll">';
          $selectedEnd = '</div>';
          $selectedClass = 'active subset';
        }

        $output .= $selectedBegin . _ref('?'. http_build_query($params), $subset, $selectedClass) . $selectedEnd;
      }
    }
  }

  function EmperorsNavigation()
  {
    global $output;
    
    // get menu (filter) parameters
    $selectedDynasty = _get_param('dynasty', '');
  
    $emperors = Collection::Emperors();
    
    // make a list of all dynasties
    $dynasties = array();
    foreach($emperors as $emperor)
    {
      if (array_search($emperor->Dynasty, $dynasties) === false)
        array_push($dynasties, $emperor->Dynasty);
    }

    // menu item 'All'
    $params = _params();
    $output .= _ref('?'. http_build_query($params), 'All', ($selectedDynasty == '' ? 'active' : ''));

    // menu items for groups
    foreach ($dynasties as $dynasty)
    {
      $params['dynasty'] = $dynasty;
      
      $selectedBegin = '';
      $selectedEnd = '';
      $selectedClass = '';
      if ($selectedDynasty == $dynasty)
      {
        $selectedBegin = '<div id="scroll">';
        $selectedEnd = '</div>';
        $selectedClass = 'active';
      }
      
      $output .= $selectedBegin . _ref('?'. http_build_query($params), $dynasty, $selectedClass) . $selectedEnd;
    }
  }

  function DeitiesNavigation()
  {
    global $output;
    
    $deities = Collection::Deities();
    
    // get menu (filter) parameters
    $selectedDeity = _get_param('deity', '');
    if ($selectedDeity == '')
      $selectedDeity = reset($deities);

    // make a list of all dynasties
    $params = _params();
    while(current($deities))
    {
      $deity = key($deities);
      $params['deity'] = $deity;
      
      $selectedBegin = '';
      $selectedEnd = '';
      $selectedClass = '';
      if ($selectedDeity == $deity)
      {
        $selectedBegin = '<div id="scroll">';
        $selectedEnd = '</div>';
        $selectedClass = 'active';
      }
      
      $output .= $selectedBegin . _ref('?'. http_build_query($params), $deity, $selectedClass) . $selectedEnd;
                     
      next($deities);
    }
  }
  
  echo $output;

?>
