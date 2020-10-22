<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_ParticipantIdGenerator{

  /**
   * Method to generate new participant id and save latest in setting
   */
  public static function generateParticipantId() {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    // first get latest sequence from setting
    $latestSequence = Civi::settings()->get('nbr_participant_sequence');
    // check if new participant id already exists in volunteer ids or as contact identifier and if so,
    // generate new one
    $newId = NULL;
    $newIdCorrect = FALSE;
    while (!$newIdCorrect) {
      $latestSequence++;
      $sequenceCode = str_pad($latestSequence, 8, 0, STR_PAD_LEFT);
      $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($latestSequence);
      $newId = 'N' . $sequenceCode . $checkCharacter;
      $volQuery = "SELECT COUNT(*) FROM " . $config->volunteerIdsTableName . " WHERE "
        . $config->participantIdColumnName . " = %1";
      $volCount = CRM_Core_DAO::singleValueQuery($volQuery, [1 => [$newId, "String"]]);
      $idQuery = "SELECT COUNT(*) FROM " . $config->contactIdentityTableName . " WHERE identifier = %1
      AND identifier_type = %2";
      $idCount = CRM_Core_DAO::singleValueQuery($idQuery, [
        1 => [$newId, "String"],
        2 => [$config->participantIdentifierType, "String"],
      ]);
      if ($volCount == 0 && $idCount == 0) {
        $newIdCorrect = TRUE;
      }
    }
    Civi::settings()->set('nbr_participant_sequence', $latestSequence);
    return $newId;
  }

  /**
   * Generate a new participant id for a new contact.
   *
   * @param $contactId
   * @throws
   */
  public static function createNewParticipantIdForContact($contactId) {
    $volunteer = new CRM_Nihrbackbone_NihrVolunteer();
    if ($volunteer->isValidVolunteer($contactId)) {
      $config = CRM_Nihrnumbergenerator_Config::singleton();
      $id = self::generateParticipantId();
      if ($id) {
        $apiParams['custom_' . $config->participantIdFieldId] = $id;
        $apiParams['entity_id'] = $contactId;
        try {
          civicrm_api3('CustomValue', 'create', $apiParams);
        } catch (CiviCRM_API3_Exception $ex) {
          new Exception("Error adding participant_id in " . __METHOD__, ", contact IT support mentioning error from CustomValue create API: " . $ex->getMessage());
        }
      }
      else {
        new Exception("Could not generate new participant id (id is empty) in " . __METHOD__, ", contact IT support!");
      }
    }
  }

}
