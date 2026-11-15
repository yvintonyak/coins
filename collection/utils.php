<?php

  $RootPath = $_SERVER['DOCUMENT_ROOT'];
  include $RootPath . '/session.php';

  if (!defined('CorrectAccess')) die('ERROR: ACCESS DENIED');
 
  function _get_param($param, $default = '')
  {
    $result = $default;
    if (isset($_GET[$param]))
    {
      $result = $_GET[$param];
    }
    return $result;
  }

  function _params()
  {
    $view = '';
    if (isset($_SESSION['session_view']))
      $view = $_SESSION['session_view'];
  
    $params = array();
    if ($view !== '')
      $params['view'] = $view;
    
    return $params;
  }

  function _ref($ref, $text, $class = '', $target = '')
  {
    $result = '<a href="' . $ref . '"';
    if ($class != '')
      $result .= ' class="' . $class . '"';

    if ($target != '')
      $result .= ' target="' . $target . '"';

    $result .= '>' . $text . '</a>';
    return $result;
  }

  function _table_header($header)
  {
    $result = '<tr>';
    $count = 0;
    foreach($header as $col)
    {
      $result .= '<th>' . $col . '</th>';
    }
    $result .= '</tr>';
    return $result;
  }

  function _p($p)
  {
    $result = '';
    $result .= '<p>' . $p . '</p>';
    return $result;
  }
  
  function _tr($tr)
  {
    $result = '';
    $result .= '<tr>' . $tr . '</tr>';
    return $result;
  }
  
  function _td($td, $class = '')
  {
    $result = '';
    if ($class == '')
      $result .= '<td>' . $td . '</td>';
    else
      $result .= '<td class="' . $class . '">' . $td . '</td>';
    return $result;
  }
  
  function _span($text, $class = '')
  {
    $result = '';
    if ($class == '')
      $result .= '<span>' . $text . '</span>';
    else
      $result .= '<span class="' . $class . '">' . $text . '</span>';
    return $result;
  }

  function _div($text, $class = '')
  {
    $result = '';
    if ($class == '')
      $result .= '<div>' . $text . '</div>';
    else
      $result .= '<div class="' . $class . '">' . $text . '</div>';
    return $result;
  }

  function _img($img1, $img2 = '')
  {
    $file1 = $_SERVER['DOCUMENT_ROOT'] . '/' . $img1;
    $file2 = $_SERVER['DOCUMENT_ROOT'] . '/' . $img2;
    
    if (!file_exists($file1))
      return;
    
    $result = '<img src="' . $img1 . '"/>' . 
              ($img2 != '' && file_exists($file2) ? '<img src="' . $img2 . '"/>' : '');

    return $result;
  }
  
  function _year($str)
  {
    $sign = 1;

    $pos = stripos($str, "BC");
    if ($pos !== false) $sign = -1;
    
    return intval($str) * $sign;
  }
  
  function _year_to_str($year)
  {
    $result = strval(abs($year));
    if ($year < 0)
      $result .= " BC";

    return $result;
  }
  
  function _normalize_date($date)
  {
    $sign = 1;
    $bc = strpos($date, 'BC');
    if ($bc !== false)
      $sign = -1;
    
    $d = str_replace(' BC', '', $date);
    $d = str_replace(' AD', '', $d);

    $parts = explode(' ', $d);
    
    if (count($parts) == 1)
    {
      // format 'year'
      $year = strval(intval($parts[0]) * $sign + 2000);
      return '1 January ' . $year;
    }
    
    if (count($parts) == 2)
    {
      // format 'month year' or 'season year'
      $year = strval(intval($parts[1]) * $sign + 2000);
      switch($parts[0])
      {
        case 'Spring': return '1 March ' . $year;
        case 'Summer': return '1 June ' . $year;
        case 'Autumn': return '1 September ' . $year;
        case 'Winter': return '1 December ' . $year;
        default: // 'month'
          return '1 ' . $parts[0] . ' ' . $year;
      }
    }
    
    if (count($parts) == 3)
    {
      // format 'day month year'
      $year = strval(intval($parts[2]) * $sign + 2000);
      return $parts[0] . ' ' . $parts[1] . ' ' . $year;
    }
    
    return '';
  }

  function _js_expand()
  {
    return '<script>
              function expand(ref, text, id)
              {
                var content = document.getElementById(id);
                var state = 0;
                if (content.style.display === "block")
                {
                  ref.text = text + " \u2BC8";
                  content.style.display = "none";
                  state = 0;
                }
                else
                {
                  ref.text = text + " \u2BC6";
                  content.style.display = "block";
                  state = 1;
                }
                
                var xhttp = new XMLHttpRequest();
                xhttp.open("GET", "ajax.php?info_state=" + state, true);
                xhttp.send();
              }
            </script>';
  }

?>
