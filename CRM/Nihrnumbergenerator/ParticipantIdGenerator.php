<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_ParticipantIdGenerator{

  public static function generateNumber() {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    // We need to generate a new number as an number does not exists for this participant in this study.
    $sequenceNrSql = "
            SELECT COUNT(DISTINCT `{$config->participantIdColumnName}`)
            FROM `{$config->volunteerIdsTableName}`
            WHERE `{$config->participantIdColumnName}` IS NOT NULL ";

    $newSequenceNr = CRM_Core_DAO::singleValueQuery($sequenceNrSql);
    $sql = "SELECT COUNT(*) FROM `{$config->volunteerIdsTableName}` WHERE `{$config->participantIdColumnName}` = %1";

    do {
      $newSequenceNr++;
      $sequenceCode = str_pad($newSequenceNr, 8, 0, STR_PAD_LEFT);
      $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($newSequenceNr);
      $newId = 'N'.$sequenceCode.$checkCharacter;
      $sqlParams = array(
        1 => array($newId, 'String'),
      );
      $existAlready = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    } while ($existAlready);

    return $newId;
  }

  /**
   * Generate a new study participation number for a new particpation case.
   *
   * @param $case_id
   */
  public static function createNewNumberForContact($contact_id) {
    $count = civicrm_api3('Contact', 'getcount', array('id' => $contact_id, 'contact_sub_type' => ['IN' => ["nihr_volunteer"]]));
    if ($count) {
      $config = CRM_Nihrnumbergenerator_Config::singleton();
      $id = self::generateNumber();
      $apiParams['custom_'.$config->participantIdFieldId] = $id;
      $apiParams['entity_id'] = $contact_id;
      try {
        civicrm_api3('CustomValue', 'create', $apiParams);
      } catch (Exception $e) {
        echo $e->getMessage(); exit();
      }
    }
  }

}
