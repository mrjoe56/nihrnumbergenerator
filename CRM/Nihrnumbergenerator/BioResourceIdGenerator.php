<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_BioResourceIdGenerator {

  public static function generateNumber() {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    $sql = "SELECT COUNT(*) FROM " . $config->contactIdentityTableName . " WHERE identifier = %1 AND identifier_type = %2";
    do {
      $randomNr = str_pad(mt_rand(1, 99999999), 8, 0, STR_PAD_LEFT);
      $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($randomNr);
      $randomNr = 'P' . $randomNr . $checkCharacter;
      $sqlParams = [
        1 => [$randomNr, 'String'],
        2 => [$config->bioresourceIdentifierType, 'String'],
      ];
      $existAlready = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    }
    while ($existAlready);
    return $randomNr;
  }

  /**
   * @param $contactId
   */
  public static function createNewBioResourceIdForContact($contactId) {
    $volunteer = new CRM_Nihrbackbone_NihrVolunteer();
    if ($volunteer->isValidVolunteer($contactId)) {
      $config = CRM_Nihrnumbergenerator_Config::singleton();
      $randomNr = self::generateNumber();
      if ($randomNr) {
        $apiParams['custom_'.$config->bioresourceIdFieldId] = $randomNr;
        $apiParams['entity_id'] = $contactId;
        try {
          civicrm_api3('CustomValue', 'create', $apiParams);
        }
        catch (CiviCRM_API3_Exception $ex) {
          new Exception("Error adding bioresource_id in " . __METHOD__, ", contact IT support mentioning error from CustomValue create API: " . $ex->getMessage());
        }
      }
      else {
        new Exception("Could not generate new bioresource id (id is empty) in " . __METHOD__, ", contact IT support!");
      }
    }
  }

}
