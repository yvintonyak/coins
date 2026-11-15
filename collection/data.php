<?php

  $RootPath = $_SERVER['DOCUMENT_ROOT'];
  include $RootPath . '/session.php';

  if (!defined('CorrectAccess')) die('ERROR: ACCESS DENIED');
  
  // html code
  $output = '';

  // get menu (filter) parameters
  $selectedView = _get_param('view', 'coins');
  $selectedSet = _get_param('set', '');
  $selectedSubset = _get_param('subset', '');
  $selectedDynasty = _get_param('dynasty', '');
  $selectedEmperor = _get_param('emperor', '');
  $selectedCoin = _get_param('coin', '');
  $selectedDeity = _get_param('deity', '');
  
  $coins = Collection::Coins();
  $emperors = Collection::Emperors();
  $dictionary = Collection::Dictionary();
  $mintmarks = Collection::Mintmarks();
  $deities = Collection::Deities();

  switch ($selectedView)
  {
    case 'coins':
    case 'deities':
      if ($selectedCoin == '')
      {
        // show coin info list
        showCoinList();
      }
      else
      {
        // show coin info details
        showCoinDetails();
      }
    break;

    case 'emperors':
      if ($selectedCoin != '')
      {
        // show coin info details
        showCoinDetails();
        break;
      }
    
      if ($selectedEmperor == '')
      {
        // show emperor list
        showEmperorList();
      }
      else
      {
        // show emperor details
        showEmperorDetails();
      }
    break;
  }
    
  function showCoin($coin)
  {
    global $selectedView;
    global $selectedSet;
    global $selectedSubset;
    global $selectedDynasty;
    global $selectedEmperor;
    global $selectedDeity;
    
    $show = false;
    
    switch ($selectedView)
    {
      case 'coins':
        // selection by set and subset
        if ($selectedSet == '')
          $show = true;
        else if ($selectedSubset == '')
          $show = ($coin->Set == $selectedSet);
        else
          $show = ($coin->Set == $selectedSet && $coin->Dynasty == $selectedSubset);
        break;

      case 'emperors':
        // selection by dynasty and emperor
        if ($selectedDynasty == '')
        {
          if ($selectedEmperor != '')
            $show = ($coin->Emperor == $selectedEmperor);
          else
            $show = ($coin->Dynasty != '');
        }
        else if ($selectedEmperor == '')
          $show = ($coin->Dynasty == $selectedDynasty);
        else
          $show = ($coin->Dynasty == $selectedDynasty) && 
                  ($coin->Emperor == $selectedEmperor);
        break;
        
      case 'deities':
        // selection by deity
        if ($selectedDeity == '')
          $show = ($coin->Deity != '');
        else 
          $show = ($coin->Deity == $selectedDeity);
        break;
    }
    
    return $show;
  }
  
  function showCoinList()
  {
    global $output;
    global $coins;
    global $selectedView;
    
    $output .= '<table id="coins">';

    $output .= '<thead>';
    $output .= _table_header(['', 'Coin', 'Obverse | Reverse', 'Minted']);
    $output .= '</thead>';

    $output .= '<tbody>';
    $coinCounter = 0;
    $emperorCounter = array();
    $yearLo = PHP_INT_MAX;
    $yearHi = PHP_INT_MIN;
    
    foreach ($coins as $coin)
    {
      if (showCoin($coin))
      {
        showCoinItem($coin);

        $coinCounter++;

        if ($coin->Emperor != '')
          $emperorCounter[$coin->Emperor] = true;
        
        $from = _year($coin->MintedFrom);
        $to = ($coin->MintedTo != '' ? _year($coin->MintedTo) : $from);
        
        if($yearLo > $from) $yearLo = $from;
        if($yearHi < $to) $yearHi = $to;
      }
    }
    if ($yearLo == PHP_INT_MAX) $yearLo = 0;
    if ($yearHi == PHP_INT_MIN) $yearHi = 0;
    
    $emperorCounter = count($emperorCounter);
    $output .= '</tbody>';
    
    if ($selectedView == 'emperors')
      $emperorCounter = 0; // don't show emperor count

    $output .= '<tfoot>';
    $output .= _tr(_td(strval($coinCounter) . ($coinCounter != 1 ? ' coins' : ' coin'), 'footer') . 
                   _td(($emperorCounter > 0 ? strval($emperorCounter) . ($emperorCounter != 1 ? ' emperors' : ' emperor') : ''), 'footer') . 
                   _td('', 'footer') . 
                   _td('Years ' . _year_to_str($yearLo) . ' - ' . _year_to_str($yearHi), 'footer'));
    $output .= '</tfoot>';
    
    $output .= '</table>';
  }
  
  function showCoinItem($coin)
  {
    global $output;
    
    // add or replace coin parameter
    $params = $_GET;
    $params['coin'] = $coin->index;
    
    $output .= '<tr>';

    $output .= _td(_ref('?' . http_build_query($params), _img($coin->ObverseImage, $coin->ReverseImage)), 'image');
    $output .= _td(_p($coin->ID) . _p($coin->Denomination . ($coin->Emperor != '' ? ', ' . $coin->Emperor : '')));
    $output .= _td(_p($coin->ObverseLegend) . _p($coin->ReverseLegend));

    $output .= _td(_p($coin->MintedFrom . ($coin->MintedTo != '' && $coin->MintedTo != $coin->MintedFrom ? ' - ' : '') . $coin->MintedTo) . 
                   _p($coin->MintCity . (($coin->MintCity != '' && $coin->MintRegion != '') ? ', ' : '') . $coin->MintRegion));

    $output .= '</tr>';
  }
  
  function showCoinDetails()
  {
    global $output;
    global $coins;
    global $selectedCoin;
    
    $coin = $coins[$selectedCoin];
    
    // top-level table [begin]
    $output .= '<table id="coin">';

    // coin details header
    $invoice = '<span style="float:right;">' . _ref('collection\invoices\\' . $coin->Invoice . '.pdf' , '<img src="' . 'invoice.png' . '" width="32" height="24"/>', 'print', '_blank') . '</span>';
    $output .= '<thead>';
    $output .= _table_header([$coin->ID . $invoice]);
    $output .= '</thead>';

    $output .= '<tbody>';
    
    // previous and next navigation
    showNextPrevNavigation();

    // main table: data | images [begin]
    $output .= '<tr><td>';
    $output .= '<table><tr>';
    
    // data table [begin]
    $output .= '<td><table class="info">';

    // description section
    showCoinDescription($coin);

    // authority section
    showCoinAuthority($coin);
    
    // legend section
    showCoinLegend($coin);

    // data table [end]
    $output .= '</table></td>';
    
    // images
    $output .= _td(_p(_img($coin->ObverseImage, $coin->ReverseImage)), 'image');
    
    // main table: data | images [end]
    $output .= '</tr></table>';
    $output .= '</td></tr>';
 
    $output .= '</tbody>';
    
    // top-level table [end]
    $output .= '</table>';
  }
  
  function showNextPrevNavigation()
  {
    global $output;
    global $coins;
    global $emperors;
    global $selectedCoin;
    global $selectedView;
    
    $output .= '<tr><td><table><tr>';
    
    $params = _params();
    if ($selectedCoin > 0)
    {
      $coin = $coins[$selectedCoin - 1];
      
      switch ($selectedView)
      {
        case 'coins':
          $params['set'] = $coin->Set;
          $params['subset'] = $coin->Dynasty;

          $params['coin'] = $coin->index;
          $output .= _td(_ref('?' . http_build_query($params), '&#x2BC7 Previous', 'prev_next'), 'prev');
        break;
         
        case 'emperors':
        {
          if (_get_param('emperor') == $coin->Emperor)
          {
            $emperor = $emperors[$coin->Emperor];
            if ($emperor !== null)
            {
              $params['dynasty'] = $emperor->Dynasty;
              $params['emperor'] = $emperor-> ID;

              $params['coin'] = $coin->index;
              $output .= _td(_ref('?' . http_build_query($params), '&#x2BC7 Previous', 'prev_next'), 'prev');
            }
          }
        }
        break;

        case 'deities':
        {
        }
        break;
      }
    }
    
    if ($selectedCoin < count($coins) - 1)
    {
      $coin = $coins[$selectedCoin + 1];
      
      switch ($selectedView)
      {
        case 'coins':
        {
          $params['set'] = $coin->Set;
          $params['subset'] = $coin->Dynasty;
          
          $params['coin'] = $coin->index;
          $output .= _td(_ref('?' . http_build_query($params), 'Next &#x2BC8', 'prev_next'), 'next');
        }
        break;

        case 'emperors':
        {
          if (_get_param('emperor') == $coin->Emperor)
          {
            $emperor = $emperors[$coin->Emperor];
            if ($emperor !== null)
            {
              $params['dynasty'] = $emperor->Dynasty;
              $params['emperor'] = $emperor-> ID;

              $params['coin'] = $coin->index;
              $output .= _td(_ref('?' . http_build_query($params), 'Next &#x2BC8', 'prev_next'), 'next');
            }
          }
        }
        break;

        case 'deities':
        {
        }
        break;
      }
    }
    
    $output .= '</tr></table></td></tr>';
  }

  function showCoinDescription($coin)
  {
    global $output;
    global $emperors;
    
    $output .= _tr(_td('Description', 'section') . _td('', 'value'));
    
    $params = array();
    $params['view'] = 'emperors';
    $params['emperor'] = $coin->Emperor;
    
    if (array_key_exists($coin->Emperor, $emperors))
      $output .= _tr(_td('Authority', 'param') . _td(_ref('?' . http_build_query($params), $coin->Emperor, 'emperor'), 'value'));
    else
      $output .= _tr(_td('Authority', 'param') . _td($coin->Emperor, 'value'));

    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Date', 'param') . 
                   _td($coin->MintedFrom . ($coin->MintedTo != '' && $coin->MintedTo != $coin->MintedFrom ? ' - ' : '') . $coin->MintedTo, 'value'));

    $params = array();
    $cityLink = '';
    $regionLink = '';
    
    if ($coin->MintRegion != '')
      $params['region'] = $coin->MintRegion;

    if ($coin->MintRegion != '' && strpos($coin->Set, 'Roman') !== false)
      $regionLink = _ref('collection/map.php?' . http_build_query($params), $coin->MintRegion, 'mint', '_blank');
    else
      $regionLink = $coin->MintRegion;

    if ($coin->MintCity != '')
      $params['city'] = $coin->MintCity;

    if ($coin->MintCity != '' && strpos($coin->Set, 'Roman') !== false)
      $cityLink = _ref('collection/map.php?' . http_build_query($params), $coin->MintCity, 'mint', '_blank');
    else
      $cityLink = $coin->MintCity;

    $output .= _tr(_td('Mint', 'param') . 
                   _td($cityLink . ($cityLink != '' && $regionLink != '' ? ', ' : '') . $regionLink, 'value'));

    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Denomination', 'param') . _td($coin->Denomination, 'value'));
    $output .= _tr(_td('Diameter', 'param') . _td($coin->Diameter, 'value'));
    $output .= _tr(_td('Weight', 'param') . _td($coin->Weight, 'value'));


    $value = $coin->ObverseLegend;
    $value = str_replace('[', '<span class="legend_adds">', $value);
    $value = str_replace(']', '</span>', $value);

    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Obverse', 'param') . _td($value, 'value'));
    $output .= _tr(_td('<br/>', 'param') . _td($coin->ObverseDescription, 'value'));

    $value = $coin->ReverseLegend;
    $value = str_replace('[', '<span class="legend_adds">', $value);
    $value = str_replace(']', '</span>', $value);

    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Reverse', 'param') . _td($value, 'value'));
    $output .= _tr(_td('<br/>', 'param') . _td($coin->ReverseDescription, 'value'));
    
    if ($coin->Remark != '')
    {
      $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
      $output .= _tr(_td('*', 'param') . _td($coin->Remark, 'value_desc'));
    }
  }

  function showCoinAuthority($coin)
  {
    global $output;
    global $emperors;
   
    if ($coin->Emperor == '')
      return;
    
    if (!array_key_exists($coin->Emperor, $emperors))
      return;
    
    $emperor = $emperors[$coin->Emperor];
    
    $output .= _tr(_td('Authority', 'section') . _td('', 'value'));

    // imperator portrait, use 'Name' instead
    // $output .= _tr(_td(_img($coin->PortraitImage), 'param') . _td($emperor->Name, 'value_name'));
    $output .= _tr(_td('Name', 'param') . _td($emperor->Name, 'value_name'));
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    
    $birthPlace = ($emperor->BirthCity != '' ? $emperor->BirthCity : '' ) . 
                  ($emperor->BirthCity != '' && $emperor->BirthRegion != '' ? ', ' : '') .
                  ($emperor->BirthRegion != '' ? $emperor->BirthRegion : '');
    
    $output .= _tr(_td('Born', 'param') . _td($emperor->Born . 
                      ($birthPlace != '' ? ' (' . $birthPlace . ')' : ''), 'value'));
    
    $output .= _tr(_td('Died', 'param') . _td($emperor->Died . 
                      ($emperor->Aged != '' ? ' (aged ' . $emperor->Aged . ')' : ''), 'value'));
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Reign', 'param') . _td($emperor->ReignFrom . 
                                              ($emperor->ReignTo != '' ? ' - ' . $emperor->ReignTo : ''), 'value'));

    if ($emperor->Description != '')
    {
      $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

      $infoState = 0;
      if (isset($_SESSION['info_state']))
        $infoState = intval($_SESSION['info_state']);

      $divStyle = ($infoState == 1 ? 'style="display:block;"' : '');
      $infoArrow = ($infoState == 1 ? '&#x2BC6' : '&#x2BC8');

      // additional information on emperor
      // implemented as expandable text block
      $output .= _tr(_td('<a href="javascript:void(0)" class="value_desc"' . 
                         ' onclick="expand(this, \'Info\', \'AI\')">Info ' . $infoArrow . '</a>', 'param') . 
                     _td('', 'value'));

      $output .= _tr(_td('', 'param') . _td('<div id="AI" class="value_desc" ' . $divStyle . '>' . $emperor->Description . '</div>'));
      $output .= _js_expand();
    }
  }
  
  function formatLegendPopup($valueHtml, $comment)
  {
    $wrapped = _span($valueHtml, 'legend_main');
    if ($comment != '')
      return '<a href="javascript:void(0)" class="legend" data-legend="' . htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') . '">' . $wrapped . '</a>';

    return $wrapped;
  }

  function getLegendValue($key)
  {
    global $dictionary;

    $value = $dictionary->Value($key);

    $value = str_replace('{', '<span class="legend_adds">', $value);
    $value = str_replace('}', '</span>', $value);
    $value = str_replace('(', '<span class="legend_adds">(', $value);
    $value = str_replace(')', ')</span>', $value);
    
    $comment = $dictionary->Comment($key);
    if ($comment != '')
      return formatLegendPopup($value, $comment);
    
    return $value;
  }
  
  function showCoinLegend($coin)
  {
    global $output;
    global $dictionary;
    global $mintmarks;
    global $deities;
    
    if (count($coin->ObverseLegendEx) == 0 && 
        count($coin->ReverseLegendEx) == 0 && 
        $coin->Deity == '' && 
        $coin->Mintmark == '') 
      return;

    $output .= _tr(_td('Legend', 'section') . _td('', 'value'));
    
    $obverseLegend = false;
    if (count($coin->ObverseLegendEx) > 0)
    {
      $legend = '';
      foreach($coin->ObverseLegendEx as $key)
      {
        $legend .= ($legend != '' ? '<br/>' : '') . getLegendValue($key);
      }

      $output .= _tr(_td('Obverse', 'param') . _td($legend, 'value'));
      $obverseLegend = true;
    }
    
    if (count($coin->ReverseLegendEx) > 0)
    {
      if ($obverseLegend)
        $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

      $legend = '';
      foreach($coin->ReverseLegendEx as $key)
      {
        $legend .= ($legend != '' ? '<br/>' : '') . getLegendValue($key);
      }

      $output .= _tr(_td('Reverse', 'param') . _td($legend, 'value'));
    }
    
    if ($coin->Mintmark != '')
    {
      if (count($coin->ReverseLegendEx) > 0 || count($coin->ObverseLegendEx) > 0)
        $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

      $mintmarkDesc = $mintmarks->Comment($coin->Mintmark);
      $mintmarkValue = formatLegendPopup(htmlspecialchars($coin->Mintmark, ENT_QUOTES, 'UTF-8'), $mintmarkDesc);

      $output .= _tr(_td('Mintmark', 'param') . _td($mintmarkValue, 'value'));
    }

    if ($coin->Deity != '')
    {
      if (count($coin->ReverseLegendEx) > 0 || count($coin->ObverseLegendEx) > 0 || $coin->Mintmark != '')
        $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
      
      $list = explode(',', $coin->Deity);
      $index = 0;
      foreach ($list as $item)
      {
        $deity = trim($item);
        $deityDesc = array_key_exists($deity, $deities) ? $deities[$deity] : '';
        $value = formatLegendPopup(htmlspecialchars($deity, ENT_QUOTES, 'UTF-8'), $deityDesc);
        
        $output .= _tr(_td(($index == 0 ? 'Deity' : ''), 'param') . _td($value, 'value'));
        $index++;
      }
    }
  }
  
  function showEmperorList()
  {
    global $output;
    global $emperors;
    
    $output .= '<table id="emperors">';

    $output .= '<thead>';
    $output .= _table_header(['', 'Emperor | Empress', 'Reign']);
    $output .= '</thead>';

    $output .= '<tbody>';
    
    $emperorCounter = 0;
    $firstEmperor = null;
    $lastEmperor = null;
    
    $reignFrom = '';
    $reignTo = '';

    foreach ($emperors as $emperor)
    {
      if (showEmperor($emperor))
      {
        showEmperorItem($emperor);
        $emperorCounter++;
        
        if ($reignFrom != '')
        {
          $dt1 = new DateTime(_normalize_date($reignFrom));
          $dt2 = new DateTime(_normalize_date($emperor->ReignFrom));
          if ($dt1 > $dt2)
            $reignFrom = $emperor->ReignFrom;
        }
        else
          $reignFrom = $emperor->ReignFrom;
        
        if ($reignTo != '')
        {
          $dt1 = new DateTime(_normalize_date($reignTo));
          $dt2 = new DateTime(_normalize_date($emperor->ReignTo));
          if ($dt1 < $dt2)
            $reignTo = $emperor->ReignTo;
        }
        else
          $reignTo = $emperor->ReignTo;

        if ($firstEmperor == null)
          $firstEmperor = $emperor;
        
        $lastEmperor = $emperor;
      }
    }
    
    $output .= '</tbody>';
    
    $output .= '<tfoot>';
    $output .= _tr(_td('', 'footer') . 
                   _td(strval($emperorCounter) . ($emperorCounter != 1 ? ' emperors' : ' emperor'), 'footer') . 
                   _td($reignFrom . ' - ' . $reignTo , 'footer'));
    $output .= '</tfoot>';
    
    $output .= '</table>';
  }
  
  function showEmperor($emperor)
  {
    global $selectedDynasty;
    
    if ($selectedDynasty != '')
      return ($emperor->Dynasty == $selectedDynasty);
    
    return true;
  }
  
  function showEmperorItem($emperor)
  {
    global $output;

    $output .= '<tr>';
    
    $params = $_GET;
    $params['emperor'] = $emperor->ID;
    
    $output .= _td(_ref('?' . http_build_query($params), _img($emperor->PortraitImage)), 'image');
    $output .= _td(_p($emperor->ID) . _p($emperor->Name));
    $output .= _td(_p($emperor->ReignFrom . ($emperor->ReignTo != '' ? ' - ' : '') . $emperor->ReignTo));

    $output .= '</tr>';
  }
  
  function showEmperorDetails()
  {
    global $output;
    global $emperors;
    global $selectedEmperor;
    
    $emperor = $emperors[$selectedEmperor];
    
    // top-level table [begin]
    $output .= '<table id="coin">';

    // coin details header
    $output .= '<thead>';
    $output .= _table_header([$emperor->ID]);
    $output .= '</thead>';

    $output .= '<tbody>';
    
    // main table: data | images [begin]
    $output .= '<tr><td>';
    $output .= '<table><tr>';
    
    // data table [begin]
    $output .= '<td><table class="info">';
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Name', 'param') . _td($emperor->Name, 'value_name'));
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

    $birthPlace = ($emperor->BirthCity != '' ? $emperor->BirthCity : '' ) . 
                  ($emperor->BirthCity != '' && $emperor->BirthRegion != '' ? ', ' : '') .
                  ($emperor->BirthRegion != '' ? $emperor->BirthRegion : '');
    
    $output .= _tr(_td('Born', 'param') . _td($emperor->Born . 
                      ($birthPlace != '' ? ' (' . $birthPlace . ')' : ''), 'value'));
    
    $output .= _tr(_td('Died', 'param') . _td($emperor->Died . 
                      ($emperor->Aged != '' ? ' (aged ' . $emperor->Aged . ')' : ''), 'value'));
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));
    $output .= _tr(_td('Reign', 'param') . _td($emperor->ReignFrom . 
                                              ($emperor->ReignTo != '' ? ' - ' . $emperor->ReignTo : ''), 'value'));

    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

    $output .= _tr(_td('Info', 'param') . _td($emperor->Description, 'value'));
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

    // data table [end]
    $output .= '</table></td>';
    
    // images
    $output .= _td(_p(_img($emperor->PortraitImage)), 'image');
    
    $output .= _tr(_td('<br/>', 'param') . _td('', 'value'));

    // main table: data | images [end]
    $output .= '</tr></table>';
    $output .= '</td></tr>';
 
    showCoinList();

    $output .= '</tbody>';
    
    // top-level table [end]
    $output .= '</table>';
  }
  
  echo $output;

?>
