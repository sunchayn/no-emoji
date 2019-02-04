<?php
/**
 * This file is meant to provide an example of the execution of this package.
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 * @license MIT
 */

require 'vendor/autoload.php';

use MazenTouati\Simple2wayConfig\S2WConfigFactory;
use MazenTouati\NoEmoji\Scrapper;
use MazenTouati\NoEmoji\Handler;
use MazenTouati\NoEmoji\Prettifier;

// Initialize the configuration
$config = S2WConfigFactory::create(__DIR__ . '/config');

$viewData = [];

// Scrapping
// ---
$viewData['scrape'] = Scrapper::factory($config)->run()->export();

// Handling
// ---
$handler = Handler::factory($config)->run();
$viewData['handle'] = $handler->export();

// Testing
// ---
$viewData['test']  = $handler->testPattern();

// Prettifier
// ---
$viewData['Prettifier'] = Prettifier::factory($config)->run()->export();

// Functions
// ---

/**
 * Transform the result array to a meaningful information
 *
 * @param  string $kind   the data kind eg: scrape...
 * @param  bool|string $result export's result
 *
 * @return string treated information
 */
function craftResultString(string $kind, $result)
{
    if ($result === false) {
        return ucfirst($kind) . ': <span class="error">Oops ! something went wrong...</span>';
    } else {
        if (isset($result['count'])) {
            $kind = $kind . ' ( ' . $result['count'] . ' Emoji replaced ) ';
        }
        return ucfirst($kind) . ": <a href='{$result['path']}' target='_blank'>{$result['fileTitle']}</a> <small>[ {$result['size']} Bytes ]</small>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>No Emoji Pattern generator</title>

  <style>
    body {
        color: #222;
    }

    .error {
      color: red;
    }

    a {
        color: blue;
        text-decoration: none;
    }

    small {
        color: #888;
    }

    ul {
      margin: 0;
      padding-left: 18px;
      line-height: 1.5rem;
    }

    footer {
      padding: 2rem 0;
    }
  </style>
</head>
<body>
  <h2>No Emoji Pattern generator</h2>
  <hr>
  The execution generated the following files :
  <ul>
    <?php
      foreach ($viewData as $kind => $result) {
          echo '<li>'. craftResultString($kind, $result) .'</li>';
      }
    ?>
  </ul>

  <footer>
    Copyright <a href="https://mazentouati.github.io">Mazen Touati</a> - MIT License @2019
  </footer>
</body>
</html>
