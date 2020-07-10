<?php

// implement simple poll
class Poll
{

  // to store votes given to choices in the form
  private $csharp;
  private $go;
  private $java;
  private $kotlin;
  private $nodejs;
  private $php;
  private $python;
  private $ruby;

  public $errorStack;

  // Getters
  public function getCsharp()
  {
    return $this->csharp;
  }
  public function getGo()
  {
    return $this->go;
  }
  public function getJava()
  {
    return $this->java;
  }
  public function getKotlin()
  {
    return $this->kotlin;
  }
  public function getNodejs()
  {
    return $this->nodejs;
  }
  public function getPhp()
  {
    return $this->php;
  }
  public function getPython()
  {
    return $this->python;
  }
  public function getRuby()
  {
    return $this->ruby;
  }


  // Constructor
  function __construct()
  {
    $this->csharp = 0;
    $this->go = 0;
    $this->java = 0;
    $this->kotlin = 0;
    $this->nodejs = 0;
    $this->php = 0;
    $this->python = 0;
    $this->ruby = 0;
    $this->errorStack = array();
  }

  // Destructor
  function __destruct()
  {
  }

  // iterate throught all properties in loops
  private function iterateProps($i)
  {
    switch ($i) {
      case 0:
        return $this->getCsharp();
      case 1:
        return $this->getGo();
      case 2:
        return $this->getJava();
      case 3:
        return $this->getKotlin();
      case 4:
        return $this->getNodejs();
      case 5:
        return $this->getPhp();
      case 6:
        return $this->getPython();
      case 7:
        return $this->getRuby();
      default:
        return null;
    }
  }

  // return all property values as a comma separated values
  public function getAllPropsDelimiter($separator)
  {
    return $this->csharp . $separator .
      $this->go . $separator .
      $this->java . $separator .
      $this->kotlin . $separator .
      $this->nodejs . $separator .
      $this->php . $separator .
      $this->python . $separator .
      $this->ruby;
  }

  // clean input values from slashes html tags, prevent XSS attack
  private function sanitizeInput($str)
  {
    $str = trim($str);
    $str = stripslashes($str);
    $str = strip_tags($str);
    $str = htmlspecialchars($str);

    return $str;
  }

  // validate post request
  public function validatePoll()
  {
    $noError = true;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      if (isset($_POST['vote']) && !empty($_POST['vote'])) {

        $vote = $this->sanitizeInput($_POST['vote']);

        // store data values
        switch ($vote) {
          case 'csharp':
            $this->csharp = 1;
            break;
          case 'go':
            $this->go = 1;
            break;
          case 'java':
            $this->java = 1;
            break;
          case 'kotlin':
            $this->kotlin = 1;
            break;
          case 'nodejs':
            $this->nodejs = 1;
            break;
          case 'php':
            $this->php = 1;
            break;
          case 'python':
            $this->python = 1;
            break;
          case 'ruby':
            $this->ruby = 1;
            break;
          default:;
        }
      } else {
        $noError = false;
        array_push($this->errorStack, 'Nem választottál ki egy elemet sem az űrlapon.');
      }

      return $noError;
    }
  }

  // store results in CSV file
  // first case: csv file is nonexistent or just empty
  // second case: csv file exists and not empty
  public function storeResultInCSV($filename, $delimiter)
  {
    // add extension
    $filename = $filename . '.csv';

    // check if file is empty
    $emptyFile = file_get_contents($filename) ? false : true;

    // read file if empty
    if ($emptyFile) {
      try {
        if (($result = fopen($filename, 'w')) === false) {
          throw new Exception('Cannot write the file. Try again later.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }
      // The ISO-8859-2 encoding is specific to Hungarian language, change it
      // I think utf-8 would be sufficent here, no special chars here
      fwrite($result, iconv('utf-8', 'ISO-8859-2', "C#;Go;Java;Kotlin;Node.js;PHP;Python;Ruby\r\n"));
      
      $dataLine = $this->getAllPropsDelimiter($delimiter);
      fwrite($result, iconv('utf-8', 'ISO-8859-2', "{$dataLine}\r\n"));
      if (!fclose($result)) {
        echo 'Hiba a fájl lezárásakor. <br />';
      }

      // echo 'OK. Data saved to file.<br />';
      // echo '<a href="' . $filename . '" download>Download ' .  $filename . '</a>';
      return;
    } else {
      try {
        if (($result = fopen($filename, 'r')) === false) {
          throw new Exception('Cannot read the file because the file is unaccessible.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }

      // read first line, file pointer moved to the end of first line
      // store first line (header)
      $header = fgets($result);

      // the next line is data, because this function reads the remainder into string
      $dataLine = stream_get_contents($result);

      // echo $tmp;
      if (!fclose($result)) {
        echo 'Hiba a fájl lezárásakor.<br />';
      };
      //--------------------------- File closed

      // explode line at the delimiter chars
      $dataLineExploded = explode($delimiter, $dataLine);
      $votes = array();

      for ($i = 0; $i < count($dataLineExploded); $i++) {
        // increment existing values with the new votes
        $votes[$i] = intval($dataLineExploded[$i], 10) + $this->iterateProps($i);
        // echo $this->iterateProps($i);
      }
      // print_r($votes);

      // delimiter separated updated values
      $updatedDataLine = '';
      foreach ($votes as $vote) {
        $updatedDataLine .= $vote;
        $updatedDataLine .= $delimiter;
      }

      // rempve last delimiter (not needed)
      $updatedDataLine = substr($updatedDataLine, 0, strlen($updatedDataLine) - 1);

      try {
        if (($result = fopen($filename, 'w')) === false) {
          throw new Exception('Error. Cannot write to the file.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }

      // write header to file
      fwrite($result, $header);
      // write updated data
      fwrite($result, "{$updatedDataLine}\r\n");
      if (!fclose($result)) {
        echo 'Hiba a fájl lezárásakor.<br />';
      };
      // echo 'OK. Data saved to file.<br />';
      // echo 'OK. Image data saved to <a href="' . $filename  . '">' . $filename . '</a><br />';
    }
  }

  public function readResultsFromCSV($filename, $delimiter)
  {
    // add extension
    $filename = $filename . '.csv';

    try {
      if (($result = fopen($filename, 'r')) === false) {
        throw new Exception('Cannot read the file because the file is unaccessible.<br />');
      }
    } catch (Exception $e) {
      echo $e->getMessage();
    }

    // store first line (header)
    $header = fgets($result);

    // the next line is data
    $dataLine = stream_get_contents($result);

    if (!fclose($result)) {
      echo 'Hiba a fájl lezárásakor. <br />';
    }
    //--------------------------- File closed

    // split votes
    $dataLineExploded = explode($delimiter, $dataLine);
    $votes = array();
    $languages = array('C#', 'Go', 'Java', 'Kotlin', 'Node.js', 'PHP', 'Python', 'Ruby');

    for ($i = 0; $i < count($dataLineExploded); $i++) {
      // put votes data into array
      $votes[$languages[$i]] = intval($dataLineExploded[$i], 10);
    }

    // descending order by value
    arsort($votes);
    return $votes;
  }

}

$myPoll = new Poll;

if ($myPoll->validatePoll() === true) {
  $myPoll->storeResultInCSV('all-votes', ';');

  $allVotes = $myPoll->readResultsFromCSV('all-votes', ';');

  $numberOfVotes = 0;
  // generate ordered list to send back as response
  $html = '<ol>';
  foreach ($allVotes as $key => $value) {
    $html .= '<li>' . $key . ': ' . $value . '</li>';
    $numberOfVotes += $value;
  }
  $html .= '</ol>';

  echo json_encode(array(
    'html' => $html,
    'chartData' => $allVotes,
    'numberOfVotes' => $numberOfVotes,
    'errorStack' => $myPoll->errorStack
  ));
} else {
  echo json_encode(array(
    'html' => '',
    'chartData' => null,
    'numberOfVotes' => null,
    'errorStack' => $myPoll->errorStack
  ));
}
