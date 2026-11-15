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
  $searchQuery = trim(_get_param('search', ''));
  $searchOpenClass = ($searchQuery != '' ? ' open' : '');

  $output .= '<span class="menu_search' . $searchOpenClass . '" id="menu_search">';
  $output .= '<form id="coin_search_form" method="get" action="?">';
  $output .= '<input type="hidden" name="view" value="coins" />';
  $output .= '<input type="text" id="coin_search_input" name="search" class="menu_search_input" placeholder="Search coins..." value="' . htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') . '" />';
  if ($searchQuery != '')
    $output .= '<a href="?view=coins" class="menu_search_clear" title="Clear search">&times;</a>';
  $output .= '<button type="button" id="coin_search_btn" class="menu_search_btn" title="Search">';
  $output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16.2" y2="16.2"/></svg>';
  $output .= '</button>';
  $output .= '</form>';
  $output .= '</span>';

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
<script type="text/javascript">
  (function () {
    var box = document.getElementById('menu_search');
    var form = document.getElementById('coin_search_form');
    var input = document.getElementById('coin_search_input');
    var btn = document.getElementById('coin_search_btn');
    if (!box || !form || !input || !btn) return;

    btn.addEventListener('click', function () {
      if (!box.classList.contains('open')) {
        box.classList.add('open');
        input.focus();
      } else if (input.value.trim() !== '') {
        form.submit();
      } else {
        box.classList.remove('open');
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && input.value.trim() === '') {
        box.classList.remove('open');
      }
    });
  })();
</script>
