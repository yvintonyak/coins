<?php

  $RootPath = $_SERVER['DOCUMENT_ROOT'];
  include $RootPath . '/session.php';

  if (!defined('CorrectAccess')) die('ERROR: ACCESS DENIED');

  $RootPath = $_SERVER['DOCUMENT_ROOT'];

  // structures for coin info
  class Set
  {
    public $Name = '';
    public $Subsets = array();
  }

  class Coin
  {
    public $ID = '';
    public $Set = '';
    public $Dynasty = '';

    public $Emperor = '';
    public $Denomination = '';
    
    public $ObverseLegend = '';
    public $ReverseLegend = '';
    public $ObverseDescription = '';
    public $ReverseDescription = '';

    public $MintedFrom = '';
    public $MintedTo = '';
    public $MintCity = '';
    public $MintRegion = '';
    
    public $ObverseImage = '';
    public $ReverseImage = '';
    public $PortraitImage = '';
    
    public $Diameter = '';
    public $Weight = '';
    
    public $Remark = '';
    
    public $ObverseLegendEx = array();
    public $ReverseLegendEx = array();
    public $Deity = '';
    public $Mintmark = '';
    
    public $Price = 0;
    public $Currency = '';
    public $Rate = 1;
    public $Fee = 0;
    public $Seller = '';
    public $Auction = '';
    public $Date = '';
    public $Lot = '';
    public $Invoice = '';
    
    public $index = 0;
    public $group = 0;
  }
  
  class Emperor
  {
    public $ID = '';
    public $Name = '';
    
    public $Born = '';
    public $Died = '';
    public $Aged = '';
    
    public $BirthCity = '';
    public $BirthRegion = '';
    
    public $ReignFrom = '';
    public $ReignTo = '';
    
    public $Dynasty = '';
    public $Description = '';
    
    public $PortraitImage = '';
    
    public $index = 0;
  }
  
  class Dictionary
  {
    public $Entries = array();
    public $Comments = array();
    
    public function Value($key)
    {
      if (array_key_exists($key, $this->Entries))
        return $this->Entries[$key];

      return $key;
    }

    public function Comment($key)
    {
      if (array_key_exists($key, $this->Comments))
        return $this->Comments[$key];

      return '';
    }
  }
  
  // list of coin info objects
  final class Collection
  {
    // public interface

    public static function Coins()
    {
      if (!isset($_SESSION['session_data']))
        static::Initialize();
      
      return $_SESSION['session_coins'];
    }

    public static function Sets()
    {
      if (!isset($_SESSION['session_data']))
        static::Initialize();

      return $_SESSION['session_sets'];
    }

    public static function Emperors()
    {
      if (!isset($_SESSION['session_data']))
        static::Initialize();

      return $_SESSION['session_emperors'];
    }

    public static function Dictionary()
    {
      if (!isset($_SESSION['session_data']))
        static::Initialize();

      return $_SESSION['session_dictionary'];
    }

    public static function Mintmarks()
    {
      if (!isset($_SESSION['session_data']) || !isset($_SESSION['session_mintmarks']))
        static::Initialize();

      return $_SESSION['session_mintmarks'];
    }

    public static function Deities()
    {
      if (!isset($_SESSION['session_data']))
        static::Initialize();

      return $_SESSION['session_deities'];
    }
    
    private static $CoinsDir = '/collection/coins';
    private static $ImageDir = '/collection/image';
    private static $PortraitDir = '/collection/portraits';
    private static $DataDir = '/collection/data';
    
    private static $SetsFile = 'Sets.xml';
    private static $EmperorsFile = 'Emperors.xml';
    private static $DictionaryFile = 'Dictionary.xml';
    private static $MintmarksFile = 'Mintmarks.xml';
    private static $DeitiesFile = 'Deities.xml';

    private static $Coins = array();
    private static $Sets = array();
    private static $Emperors = array();
    private static $Dictionary = null;
    private static $Mintmarks = null;
    private static $Deities = array();
    
    private static $CoinFiles = array();
    private static $CoinGroups = array();
    
    private static function Initialize()
    {
      global $RootPath;
      
      static::LoadSets($RootPath . static::$DataDir . '/' . static::$SetsFile);
      static::LoadEmperors($RootPath . static::$DataDir . '/' . static::$EmperorsFile);
      static::$Dictionary = static::LoadDictionaryFile($RootPath . static::$DataDir . '/' . static::$DictionaryFile);
      static::$Mintmarks = static::LoadDictionaryFile($RootPath . static::$DataDir . '/' . static::$MintmarksFile);
      static::LoadDeities($RootPath . static::$DataDir . '/' . static::$DeitiesFile);
      
      static::FindCoinFiles();
      
      // load info from coin description files
      foreach (static::$CoinFiles as $coinFile)
      {
        static::$Coins[] = static::LoadCoin($RootPath . static::$CoinsDir . '/' . $coinFile);
      }
      
      // sort by year grouped by group and emperor
      usort(static::$Coins, function($a, $b)
                            {
                              // by groups
                              if ($a->group < $b->group) return -1;
                              if ($a->group > $b->group) return 1;
                              
                              // by emperor
                              if ($a->Emperor != '' && $b->Emperor != '')
                              {
                                if (array_key_exists($a->Emperor, static::$Emperors) && 
                                    array_key_exists($b->Emperor, static::$Emperors))
                                {
                                  $emperorA = static::$Emperors[$a->Emperor];
                                  $emperorB = static::$Emperors[$b->Emperor];
                                  
                                  if ($emperorA->index < $emperorB->index) return -1;
                                  if ($emperorA->index > $emperorB->index) return 1;
                                }
                              }
                              
                              // by years
                              $yearA = _year($a->MintedFrom);
                              $yearB = _year($b->MintedFrom);
                              
                              if ($yearA == $yearB) return 0;
                              return ($yearA < $yearB ? -1 : 1);
                            });

      // define coin indices
      $index = 0;
      foreach(static::$Coins as $coin)
      {
        static::AddSet($coin);
        $coin->index = $index++;
      }
      
      $_SESSION['session_data'] = true;
      $_SESSION['session_coins'] = static::$Coins;
      $_SESSION['session_sets'] = static::$Sets;
      $_SESSION['session_emperors'] = static::$Emperors;
      $_SESSION['session_dictionary'] = static::$Dictionary;
      $_SESSION['session_mintmarks'] = static::$Mintmarks;
      $_SESSION['session_deities'] = static::$Deities;
    }
    
    private static function FindCoinFiles()
    {
      global $RootPath;
      
      // read list of coin description files
      foreach (new DirectoryIterator($RootPath . static::$CoinsDir) as $fileInfo)
      {
        if ($fileInfo->isDot()) continue;
        if ($fileInfo->isFile())
        {
          static::$CoinFiles[] = $fileInfo->getFilename();
        }
      }
    }

    // reads coin info from coin description files
    private static function LoadCoin($coinFile)
    {
      if (!file_exists($coinFile))
        return new Coin();

      $coinDoc = simplexml_load_file($coinFile);
      
      $coin = new Coin();
      $coin->ID = (string)$coinDoc['id'];

      $coin->Emperor = (string)$coinDoc->emperor['name'];
      $coin->Denomination = (string)$coinDoc->denomination['name'];
      
      $coin->ObverseLegend = (string)$coinDoc->obverse->legend;
      $coin->ReverseLegend = (string)$coinDoc->reverse->legend;
      $coin->ObverseDescription = (string)$coinDoc->obverse->description;
      $coin->ReverseDescription = (string)$coinDoc->reverse->description;

      $coin->MintedFrom = (string)$coinDoc->mint->date['from'];
      $coin->MintedTo = (string)$coinDoc->mint->date['to'];
      $coin->MintCity = (string)$coinDoc->mint->place['city'];
      $coin->MintRegion = (string)$coinDoc->mint->place['region'];
      
      $coin->ObverseImage = static::$ImageDir . '/' . (string)$coinDoc->image->obverse['file'];
      $coin->ReverseImage = static::$ImageDir . '/' . (string)$coinDoc->image->reverse['file'];
      
      $coin->PortraitImage = static::$PortraitDir . '/' . $coin->Emperor . '.jpg';
      
      $coin->Diameter = (string)$coinDoc->measurement['diameter'];
      $coin->Weight = (string)$coinDoc->measurement['weight'];
      
      $coin->Set = (string)$coinDoc->identity['set'];
      $coin->Dynasty = (string)$coinDoc->identity['dynasty'];
      
      $coin->Remark = (string)$coinDoc->remark;
      
      $emperor = null; 
      foreach (static::$Emperors as $emp)
      {
        if ($emp->ID == $coin->Emperor)
          $emperor = $emp;
      }
      if ($emperor != null)
      {
        $dynasty = $emperor->Dynasty;
        if ($dynasty != '')
          $coin->Dynasty = $dynasty;
      }
      
      if (isset($coinDoc->legend->obverse))
      {
        foreach($coinDoc->legend->obverse->children() as $entry)
          $coin->ObverseLegendEx[] = (string)$entry['key'];
      }
      
      if (isset($coinDoc->legend->reverse))
      {
        foreach($coinDoc->legend->reverse->children() as $entry)
          $coin->ReverseLegendEx[] = (string)$entry['key'];
      }
      
      $coin->Deity = (string)$coinDoc->deity['name'];

      if(isset($coinDoc->legend->mintmark))
        $coin->Mintmark = (string)$coinDoc->legend->mintmark['mint'];
      
      $coin->Price = floatval((string)$coinDoc->origin['price']);
      $coin->Rate = floatval((string)$coinDoc->origin['rate']);
      $coin->Fee = floatval((string)$coinDoc->origin['fee']);
      $coin->Seller = (string)$coinDoc->origin['seller'];
      $coin->Date = (string)$coinDoc->origin['date'];
      $coin->Auction = (string)$coinDoc->origin['auction'];
      $coin->Lot = (string)$coinDoc->origin['lot'];
      $coin->Currency = (string)$coinDoc->origin['currency'];
      $coin->Invoice = (string)$coinDoc->origin['invoice'];

      static::AddSet($coin);
      
      return $coin;
    }
    
    private static function LoadSets($setsFile)
    {
      if (!file_exists($setsFile))
        return;

      $setsDoc = simplexml_load_file($setsFile);
      
      $coin = new Coin();
      foreach($setsDoc->children() as $set)
      {
        $coin->Set = (string)$set['name'];
        $coin->Dynasty = '';
        static::AddSet($coin);
        foreach($set->children() as $subset)
        {
          $coin->Dynasty = (string)$subset['name'];
          static::AddSet($coin);
        }
      }
    }
    
    private static function AddSet($coin)
    {
      // define group
      $group = $coin->Set . $coin->Dynasty;
      $index = array_keys(static::$CoinGroups, $group);
      if (count($index) == 0)
      {
        // new group
        static::$CoinGroups[] = $group;
        $coin->group = count(static::$CoinGroups) - 1;
      }
      else
      {
        // existing group
        $coin->group = $index[0];
      }
      
      if ($coin->Set == '') return;
      
      // find set
      $coinSet = null;
      foreach (static::$Sets as $set)
      {
        if ($coin->Set == $set->Name)
        {
          $coinSet = $set;
          break;
        }
      }

      // add set if not found
      if (is_null($coinSet))
      {
        $coinSet = new Set();
        $coinSet->Name = $coin->Set;

        static::$Sets[] = $coinSet;
      }
      
      // add sebset, if available
      if ($coin->Dynasty != '')
      {
        if (!in_array($coin->Dynasty, $coinSet->Subsets))
        $coinSet->Subsets[] = $coin->Dynasty;
      }
    }
    
    private static function LoadEmperors($emperorsFile)
    {
      if (!file_exists($emperorsFile))
        return;

      $emperorsDoc = simplexml_load_file($emperorsFile);
      
      $index = 1;
      foreach($emperorsDoc->children() as $emperorInfo)
      {
        $emperor = new Emperor();
        $emperor->ID = (string)$emperorInfo['id'];
        $emperor->Name = (string)$emperorInfo->name;
        $emperor->Dynasty = (string)$emperorInfo->dynasty;
        
        $emperor->Born = (string)$emperorInfo->dates['born'];
        $emperor->Died = (string)$emperorInfo->dates['died'];
        $emperor->Aged = (string)$emperorInfo->dates['aged'];
        
        $emperor->BirthCity = (string)$emperorInfo->place['city'];
        $emperor->BirthRegion = (string)$emperorInfo->place['region'];


        $emperor->ReignFrom = (string)$emperorInfo->reign['from'];
        $emperor->ReignTo = (string)$emperorInfo->reign['to'];
        
        $emperor->Description = (string)$emperorInfo->description;
        
        $emperor->PortraitImage = static::$PortraitDir . '/' . $emperor->ID . '.jpg';
        
        $emperor->index = $index++;
        static::$Emperors[$emperor->ID] = $emperor;
      }
    }

    private static function LoadDictionaryFile($dictionaryFile)
    {
      $dict = new Dictionary();
      if (!file_exists($dictionaryFile))
        return $dict;

      $dictionaryDoc = simplexml_load_file($dictionaryFile);
      
      foreach($dictionaryDoc->children() as $entry)
      {
        $dict->Entries[(string)$entry['key']] = (string)$entry['value'];
        $dict->Comments[(string)$entry['key']] = (string)$entry['comment'];
      }

      return $dict;
    }

    private static function LoadDeities($deitiesFile)
    {
      if (!file_exists($deitiesFile))
        return;

      $deitiesDoc = simplexml_load_file($deitiesFile);
      
      $index = 1;
      foreach($deitiesDoc->children() as $deity)
      {
        static::$Deities[(string)$deity['name']] = (string)$deity['description'];
      }
    }

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}
  }

?>
