<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_Utils {

  public static function generateCheckCharacter($number) {
    $char = '';
    $mod = $number % 23;
    switch ($mod) {
      case 0:
        $char = 'Z';
        break;
      case 1:
        $char = 'A';
        break;
      case 2:
        $char = 'B';
        break;
      case 3:
        $char = 'C';
        break;
      case 4:
        $char = 'D';
        break;
      case 5:
        $char = 'E';
        break;
      case 6:
        $char = 'F';
        break;
      case 7:
        $char = 'G';
        break;
      case 8:
        $char = 'H';
        break;
      case 9:
        $char = 'J';
        break;
      case 10:
        $char = 'K';
        break;
      case 11:
        $char = 'L';
        break;
      case 12:
        $char = 'M';
        break;
      case 13:
        $char = 'N';
        break;
      case 14:
        $char = 'P';
        break;
      case 15:
        $char = 'Q';
        break;
      case 16:
        $char = 'R';
        break;
      case 17:
        $char = 'S';
        break;
      case 18:
        $char = 'T';
        break;
      case 19:
        $char = 'V';
        break;
      case 20:
        $char = 'W';
        break;
      case 21:
        $char = 'X';
        break;
      case 22:
        $char = 'Y';
        break;
    }
    return $char;
  }

}