<?php

  $RootPath = $_SERVER['DOCUMENT_ROOT'];
  include $RootPath . '/session.php';

  if (!defined('CorrectAccess')) die('ERROR: ACCESS DENIED');
  
  $view = _get_param('view', '');
  if ($view == '')
  {
    if (isset($_SESSION['session_view']))
      $view = $_SESSION['session_view'];
  }
  
  if ($view == '')
    $view = 'coins';

  $_SESSION['session_view'] = $view;

  // html code
  $output = '';
  
  $params = array('view' => 'coins');
  $output .= _ref('?' . http_build_query($params), 'Coins', ($view == 'coins' ? 'active' : ''));
  
  $params['view'] = 'emperors';
  $output .= _ref('?' . http_build_query($params), 'Emperors', ($view == 'emperors' ? 'active' : ''));
  
  $params['view'] = 'deities';
  $output .= _ref('?' . http_build_query($params), 'Deities', ($view == 'deities' ? 'active' : ''));
  
  $params = $_GET;
  
  $params['print'] = 'prices';
  $output .= '<span style="float:right;">' . _ref('collection/print.php?' . http_build_query($params), '<img src="' . 'coins.png' . '" width="28" height="28"/>', 'print', '_blank') . '</span>';

  if ($view == 'emperors')
  {
    if (!empty($params['emperor']))
      $params['print'] = 'coins';
    else
      $params['print'] = 'emperors';
    $output .= '<span style="float:right;">' . _ref('collection/print.php?' . http_build_query($params), '<img src="' . 'printer.png' . '" width="28" height="28"/>', 'print', '_blank') . '</span>';
  }
  else // 'coins'
  {
    $params['print'] = 'coins';
    $output .= '<span style="float:right;">' . _ref('collection/print.php?' . http_build_query($params), '<img src="' . 'printer.png' . '" width="28" height="28"/>', 'print', '_blank') . '</span>';
  }
  
  echo $output;

?>
