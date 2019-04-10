<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_VolunteerNumberGenerator {

  public static function generateNumber() {
    $sqlParams = array();
    $sql = "SELECT COUNT(*) FROM `civicrm_value_contact_id_history` WHERE `identifier` = %1 AND `identifier_type` = 'nihr_volunteer_id'";

    do {
      $randomNr = str_pad(mt_rand(1, 99999999), 8, 0, STR_PAD_LEFT);
      $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($randomNr);
      $randomNr = 'P'.$randomNr.$checkCharacter;
      $sqlParams[1] = array($randomNr, 'String');
      $existAlready = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    }
    while ($existAlready);

    return $randomNr;
  }

  /**
   * @param $contact_id
   * @throws \CiviCRM_API3_Exception
   */
  public static function createNewNumberForContact($contact_id) {
    $count = civicrm_api3('Contact', 'getcount', array('id' => $contact_id, 'contact_sub_type' => ['IN' => ["nihr_volunteer"]]));
    if ($count) {
      $randomNr = self::generateNumber();
      $apiParams['custom_nihr_volunteer_data:nvd_volunteer_id'] = $randomNr;
      $apiParams['entity_id'] = $contact_id;
      civicrm_api3('CustomValue', 'create', $apiParams);
    }
  }

}