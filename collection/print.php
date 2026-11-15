<?php

  require_once('../tcpdf/tcpdf.php');
  
  set_time_limit(0);
  ini_set('memory_limit', '-1');
  
  define('CorrectAccess', true);
  define('SEPARATOR', ' • ');

  $CreatePDF = true;
  
  $RootPath = $_SERVER['DOCUMENT_ROOT'];
   
  include $RootPath . '/collection/utils.php';
  include $RootPath . '/collection/coins.php';
  
  $output = '';
  $counter = 0;
  
  $selectedView = _get_param('view', 'coins');
  $selectedSet = _get_param('set', '');
  $selectedSubset = _get_param('subset', '');
  $selectedDynasty = _get_param('dynasty', '');
  $selectedEmperor = _get_param('emperor', '');
  $selectedCoin = _get_param('coin', '');
  $selectedMode = _get_param('print', '');
  
  $coins = Collection::Coins();
  $emperors = Collection::Emperors();
  
  $selectedCoins = array();
  
  $currentSet = '';
  $pageCounter = 0;
  
  function isCoinSelected($coin)
  {
    global $selectedView;
    global $selectedSet;
    global $selectedSubset;
    global $selectedDynasty;
    global $selectedEmperor;
    global $selectedCoin;
    
    if ($selectedCoin != '')
      return ($selectedCoin == strval($coin->index));
    
    $result = true;
    switch ($selectedView)
    {
      case 'coins':
        if ($selectedSet != '')
          $result = $result && ($selectedSet == $coin->Set);
        if ($selectedSubset != '')
          $result = $result && ($selectedSubset == $coin->Dynasty);
      break;

      case 'emperors':
        if ($selectedDynasty != '')
          $result = $result && ($selectedDynasty == $coin->Dynasty);
        if ($selectedEmperor != '')
          $result = $result && ($selectedEmperor == $coin->Emperor);
      break;
    }
    
    return $result;
  }
  
  function separatorTr($size)
  {
    return '<tr style="font-size: ' . $size . 'px;"><td> </td></tr>';
  }
   
  function makeCoinInfo($coin)
  {
    global $selectedCoins;
    global $emperors;
    
    global $output;
    global $counter;
    
    global $RootPath;
    global $selectedCoin;
    
    global $currentSet;
    global $pageCounter;
    
    $counter++;
    
    if ($currentSet == '')
      $currentSet = $coin->Set;
    
    if ($currentSet != $coin->Set)
    {
      $currentSet = $coin->Set;
      
      if ($pageCounter > 0)
      {
        $output .= '<br pagebreak="true"/>';
        $pageCounter = 0;
      }
    }

    $output .= '<table style="width: 100%; table-layout: fixed;">';
    $output .= separatorTr(4);

      $output .= '<tr style="font-size: 11px;">';
      
        $output .= '<td style="width: 58%; vertical-align: top;">';

          $output .= '<table style="width: 100%; table-layout: fixed;">';

            $output .= '<tr><td style="font-family: sans-serif; font-weight: bold; font-size: 12px;">' . $coin->ID . '</td></tr>';
            
            $output .= separatorTr(8);
            
            $info = $coin->Denomination;
            $info .= ($coin->MintCity != '' ? SEPARATOR . $coin->MintCity : '');
            $info .= ($coin->MintRegion != '' ? ($coin->MintCity != '' ? ', ' : SEPARATOR) . $coin->MintRegion : '' );
            
            $from = _year($coin->MintedFrom);
            $to = ($coin->MintedTo != '' ? _year($coin->MintedTo) : $from);
            $info .= SEPARATOR . _year_to_str($from) . ($from != $to ? ' - ' . _year_to_str($to) : '');

            $output .= '<tr><td>' . $info .  '</td></tr>';

            if ($coin->Emperor != '' && array_key_exists($coin->Emperor, $emperors))
            {
              $emperor = $emperors[$coin->Emperor];
              $info = $coin->Emperor;
              $info .= ' (' . $emperor->ReignFrom . ' - ' . $emperor->ReignTo . ')';
              $output .= '<tr><td>' . $info . '</td></tr>';
            }
          
          $output .= '</table>';
        
          $output .= '<table style="width: 100%; table-layout: fixed;">';
          
            $output .= separatorTr(8);
            $output .= '<tr>';
            $output .= '<td style="font-weight: bold; width: 56px; vertical-align: top;">Obverse:</td>';
            $output .= '<td>' . $coin->ObverseLegend . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td style="width: 56px;"> </td>';
            $output .= '<td>' . $coin->ObverseDescription . '</td>';
            $output .= '</tr>';

            $output .= separatorTr(8);
            $output .= '<tr>';
            $output .= '<td style="font-weight: bold; width: 56px;">Reverse:</td>';
            $output .= '<td>' . $coin->ReverseLegend . '</td>';
            $output .= '</tr>';
            $output .= '<tr>';
            $output .= '<td style="width: 56px;"> </td>';
            $output .= '<td>' . $coin->ReverseDescription . '</td>';
            $output .= '</tr>';

          $output .= '</table>';

        $output .= '</td>';
        
        $output .= '<td style="width: 42%; vertical-align: top;">';
          $output .= '<table style="width: 100%; table-layout: fixed;">';
            $output .= '<tr><td>';
              $output .= '<img width="130" height="130" src="' . $coin->ObverseImage . '"/img>';
              $output .= '<img width="130" height="130" src="' . $coin->ReverseImage . '"/img>';
            $output .= '</td></tr>';
          $output .= '<tr><td style="font-size: 10px; vertical-align: top; width: 42%;">' . $coin->Diameter . SEPARATOR . $coin->Weight . '</td></tr>';
          $output .= '</table>';
        $output .= '</td>';
      
      $output .= '</tr>';
      $output .= separatorTr(8);

    $output .= '</table>';
    
    if ($counter < count($selectedCoins))
    {
      if ($pageCounter < 4)
      {
        $output .= '<hr>';
        $pageCounter++;
      }
      else
      {
        $output .= '<br pagebreak="true"/>';
        $pageCounter = 0;
      }
    }
  }
  
  function beginPriceInfo()
  {
    global $output;

    $output .= '<table style="width: 100%; table-layout: fixed;">';
    
    $output .= '<thead>';

      $output .= '<tr style="font-size: 10px; fixed; background-color: #ccc;">';
        $output .= '<th style="border: 1px solid #444; width: 15%;">' . 'Name' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 6%;">' . 'Hammer' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 5%;">' . 'Fee' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 6%;">' . 'Price' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 20%;">' . 'Seller' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 20%;">' . 'Auction' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 13%;">' . 'Lot' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 15%;">' . 'Date' . '</th>';
      $output .= '</tr>';

    $output .= '</thead>';

    $output .= '<tbody>';
  }
  
  function endPriceInfo($count, $total)
  {
    global $output;

    $output .= '</tbody>';
    
    $output .= '<tfoot>';

      $output .= '<tr style="font-size: 10px; fixed; background-color: #ccc; border: 1px solid #444;">';
        $output .= '<th style="width: 15%;">' . $count . ' coin(s)' . '</th>';
        $output .= '<th style="width: 6%;">' . '' . '</th>';
        $output .= '<th style="width: 5%;">' . '' . '</th>';
        $output .= '<th style="width: 6%;">' . '€ ' . number_format($total, 0) . '</th>';
        $output .= '<th style="width: 20%;">' . '' . '</th>';
        $output .= '<th style="width: 20%;">' . '' . '</th>';
        $output .= '<th style="width: 13%;">' . '' . '</th>';
        $output .= '<th style="width: 15%;">' . '' . '</th>';
      $output .= '</tr>';

    $output .= '</tfoot>';

    
    $output .= '</table>';
  }

  function makePriceInfo($coin, $index)
  {
    global $output;

    if($index % 2 == 0)
      $output .= '<tr style="font-size: 9px; fixed; background-color: #EEE;">';
    else
      $output .= '<tr style="font-size: 9px; fixed;">';
    
      $output .= '<td style="width: 15%;">' . $coin->ID . '</td>';
      
      $output .= '<td style="width: 6%;">' . number_format($coin->Price, 0) . ' ' . $coin->Currency . '</td>';
      
      $output .= '<td style="width: 5%;">' . number_format($coin->Fee, 1) . '%' . '</td>';

      $priceEuro = round($coin->Price * $coin->Rate, 0);
      $finalPrice = round($priceEuro + $priceEuro * $coin->Fee / 100);
      $output .= '<td style="width: 6%;">' . '€ ' . number_format($finalPrice, 0) . '</td>';

      $output .= '<td style="width: 20%;">' . $coin->Seller . '</td>';
      $output .= '<td style="width: 20%;">' . $coin->Auction . '</td>';
      $output .= '<td style="width: 13%;">' . $coin->Lot . '</td>';
      $output .= '<td style="width: 15%;">' . $coin->Date . '</td>';
    
    $output .= '</tr>';
  }
  
  class EmperorInfo
  {
    public $Name = '';
    public $Augustus = '-';
    public $Caesar = '-';
    public $Divus = '-';
  }

  function beginEmperorsInfo()
  {
    global $output;

    $output .= '<table style="width: 100%; table-layout: fixed;">';
    
    $output .= '<thead>';

      $output .= '<tr style="font-size: 12px; fixed; background-color: #ccc;">';
        $output .= '<th style="border: 1px solid #444; width: 25%;">' . 'Name' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 25%;">' . 'Augustus/Augusta' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 25%;">' . 'Caesar/Ruler' . '</th>';
        $output .= '<th style="border: 1px solid #444; width: 25%;">' . 'Divus/Diva' . '</th>';
      $output .= '</tr>';

    $output .= '</thead>';

    $output .= '<tbody>';
  }
  
  function getEmperorName($id)
  {
    $result = str_replace('Caesar', '', $id);
    $result = str_replace('Divus', '', $result);
    $result = str_replace('Diva', '', $result);
    $result = str_replace('Octavianus', 'Augustus', $result);
    return trim($result);
  }
  
  function IsCaesar($id)
  {
    if (strpos($id, 'Caesar') !== false) return true;
    if (strpos($id, 'Octavianus') !== false) return true;
    if (strpos($id, 'Agrippa') !== false) return true;
    if (strpos($id, 'Drusus') !== false) return true;
    if (strpos($id, 'Vabalathus') !== false) return true;
    
    // else
    return false;
  }
  
  function IsDivus($id)
  {
    $pos1 = strpos($id, 'Divus');
    $pos2 = strpos($id, 'Diva');
    return ($pos1 !== false) || ($pos2 !== false);
  }
  
  function makeEmperorsInfo(&$count, &$countAugusti, &$countCaesaris, &$countDivi)
  {
    global $emperors;
    global $selectedDynasty;
    global $output;
    
    $emperorList = array();
    
    foreach ($emperors as $emperor)
    {
      if ($selectedDynasty != '' && $emperor->Dynasty != $selectedDynasty)
        continue;
        
      $name = getEmperorName($emperor->ID);
      $key = array_search($name, array_column($emperorList, 'Name'));
      
      $emperorInfo = null;
      if ($key !== false)
      {
        $emperorInfo = $emperorList[$key];
      }
      else
      {
        $emperorInfo = new EmperorInfo();
        array_push($emperorList, $emperorInfo);
      }
      
      $emperorInfo->Name = $name;
      
      $from = new DateTime(_normalize_date($emperor->ReignFrom));
      $from = $from->sub(DateInterval::createFromDateString('2000 years'));
      $fromStr = /* $from->format('d.m') . '.' . */ _year_to_str(intval($from->format('Y')));

      $toStr = '';
      if ($emperor->ReignTo != '')
      {
        $to = new DateTime(_normalize_date($emperor->ReignTo));
        $to = $to->sub(DateInterval::createFromDateString('2000 years'));
        $toStr = /* $to->format('d.m') . '.' . */ _year_to_str(intval($to->format('Y')));
      }

      if ($toStr == $fromStr) $toStr = '';
      
      if (IsCaesar($emperor->ID))
      {
        $emperorInfo->Caesar = $fromStr . ($toStr != '' ? ' - ' . $toStr : '');
        $countCaesaris++;
      }
      else if (IsDivus($emperor->ID))
      {
        $emperorInfo->Divus = ($toStr != '' ? $toStr : $fromStr);
        $countDivi++;
      }
      else // Augustus
      {
        $emperorInfo->Augustus = $fromStr . ($toStr != '' ? ' - ' . $toStr : '');
        $countAugusti++;
      }
    }
    
    $count = 0;
    
    foreach ($emperorList as $emperor)
    {
      if($count % 2 == 0)
        $output .= '<tr style="font-size: 10px; fixed; background-color: #EEE;">';
      else
        $output .= '<tr style="font-size: 10px; fixed;">';
      
        $output .= '<td style="width: 25%;">' . $emperor->Name . '</td>';
        $output .= '<td style="width: 25%;">' . $emperor->Augustus . '</td>';
        $output .= '<td style="width: 25%;">' . $emperor->Caesar . '</td>';
        $output .= '<td style="width: 25%;">' . $emperor->Divus . '</td>';
     
      $output .= '</tr>';
      
      $count++;
    }
  }
  
  function endEmperorsInfo($count, $countAugusti, $countCaesaris, $countDivi)
  {
    global $output;

    $output .= '</tbody>';
    
    $output .= '<tfoot>';

      $output .= '<tr style="font-size: 11px; fixed; background-color: #ccc; border: 1px solid #444;">';
        $output .= '<th style="width: 25%;">' . $count . ' Principes et Domini' . '</th>';
        $output .= '<th style="width: 25%;">' . $countAugusti . ' Augusti' . '</th>';
        $output .= '<th style="width: 25%;">' . $countCaesaris . ' Caesaris' . '</th>';
        $output .= '<th style="width: 25%;">' . $countDivi . ' Divi' . '</th>';
      $output .= '</tr>';

    $output .= '</tfoot>';

    
    $output .= '</table>';
  }
  
  function makePDF()
  {
    global $CreatePDF;
    
    global $selectedCoins;
    global $selectedMode;

    global $output;
    global $coins;

    global $RootPath;
    
    $orientation = 'P';
    
    foreach ($coins as $coin)
    {
      if (isCoinSelected($coin))
        $selectedCoins[] = $coin;
    }
    
    if ($selectedMode == 'prices')
    {
      beginPriceInfo();

      $count = 0;
      $total = 0;
      foreach ($selectedCoins as $coin)
      {
        $count++;
        
        $price = round($coin->Price * $coin->Rate, 0);
        $finalPrice = round($price + $price * $coin->Fee / 100);
        
        $total += $finalPrice;
        
        makePriceInfo($coin, $count);
      }
        
      endPriceInfo($count, $total);
      
      $orientation = 'L';
    }
    else if ($selectedMode == 'emperors')
    {
      beginEmperorsInfo();
      
      $count = 0;
      $countAugusti = 0;
      $countCaesares = 0;
      $countDivi = 0;
      makeEmperorsInfo($count, $countAugusti, $countCaesaris, $countDivi);
      
      endEmperorsInfo($count, $countAugusti, $countCaesaris, $countDivi);
    }
    else // 'coins'
    {  
      foreach ($selectedCoins as $coin)
          makeCoinInfo($coin);
    }

    if ($CreatePDF)
    {
      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Yaroslav Vintonyak');
      $pdf->SetTitle('My Coin Collection');

      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
       
      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
       
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

      $fontname = TCPDF_FONTS::addTTFfont($RootPath . '/arial/arial.ttf');
      $pdf->SetFont('Arial', '', 10, '', 'false');
       
      $pdf->AddPage($orientation, 'A4');
       
      $pdf->writeHTML($output, true, false, true, false, '');
       
      $pdf->Output('coins.pdf', 'I');
      // $pdf->Output(dirname(__FILE__).'/coins.pdf', 'F');
    }
    else
    {
      echo '<html><head /><body>' . $output . '</body></html>';
    }
  }
  
  makePDF();

?>
