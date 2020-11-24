<?php
use CRM_Nihrnumbergenerator_ExtensionUtil as E;

/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyParticipantNumberGenerator {

  public static function generateNumber($studyNumber, $contactId, $caseId) {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    // only if there is no study participant id for this contact in the study yet
    $currentId = CRM_Nihrnumbergenerator_BAO_StudyParticipantSequence::existsForContact($studyNumber, $contactId);
    if (!$currentId) {
      // find sequence for study
      $sequence = CRM_Nihrnumbergenerator_BAO_StudyParticipantSequence::getStudySequence($studyNumber);
      if ($sequence) {
        $studyNumberWithouthPrefix = preg_replace("#(NBR|CBR-?)([0-9]+)#", "$2", $studyNumber);
        // only take bit before dot it there is a dot
        $numberParts = explode(".", $studyNumberWithouthPrefix);
        $studyNumberWithouthPrefix = $numberParts[0];
        // studyCode = "S" if first 3 chars of study number are "CBR", else "SP"
        $studyCode = 'SP' . str_pad($studyNumberWithouthPrefix, 4, 0, STR_PAD_LEFT);
        $sequenceCode = str_pad($sequence, 7, 0, STR_PAD_LEFT);
        if (substr($studyNumber, 0, 3) == "CBR") {
          $studyCode = 'S' . str_pad($studyNumberWithouthPrefix, 3, 0, STR_PAD_LEFT);
          $sequenceCode = str_pad($sequence, 5, 0, STR_PAD_LEFT);
        }
        // new ID = studyCode + padded new sequence + check character
        $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($sequence);
        $newId = $studyCode . $sequenceCode . $checkCharacter;
        // update sequence for study
        CRM_Nihrnumbergenerator_BAO_StudyParticipantSequence::updateStudySequence($studyNumber, $sequence);
        // generate contact identifier
        try {
          civicrm_api3("Contact", "addidentity", [
            'contact_id' => $contactId,
            'identifier' => $newId,
            'identifier_type' => CRM_Nihrnumbergenerator_Config::singleton()->studyParticipantIdIdentifier,
          ]);
        }
        catch (CiviCRM_API3_Exception $ex) {
          Civi::log()->warning(E::ts("Could not add study participant id ") . $newId .E::ts(" as new contact identifier for contact ID ")
            . $contactId . E::ts(", error from API Contact addidentity: ") . $ex->getMessage());
        }
        // return new id
        return $newId;
      }
      else {
        $message = "Could not generate Study Participant ID for contact ID " . $contactId
          . ", case ID " . $caseId . " and study number " . $studyNumber . ", please contact ICT support!";
        Civi::log()->error($message);
        throw new Exception($message);
      }
    }
    return $currentId;
  }

  /**
   * Generate a new study participation number for a new participation case.
   *
   * @param $case_id
   */
  public static function createNewNumberForCase($case_id) {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    $sql ="SELECT
            `civicrm_case_contact`.`contact_id`,
            `study_data`.`{$config->studyNrColumnName}` as `study_number`
          FROM `civicrm_case_contact`
          INNER JOIN `{$config->participationDataTableName}` `participation_data` ON `participation_data`.`entity_id` = `civicrm_case_contact`.`case_id`
          INNER JOIN `{$config->studyDataTableName}` `study_data` ON `study_data`.`entity_id` = `participation_data`.`{$config->participationDataStudyIdColumnName}`
          WHERE `civicrm_case_contact`.`case_id` = %1";

    $sqlParams[1] = array($case_id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while($dao->fetch()) {
      $id = self::generateNumber($dao->study_number, $dao->contact_id, $case_id);
      // We assume a record already exists for this case.
      CRM_Core_DAO::executeQuery("UPDATE `{$config->participationDataTableName}` SET `{$config->participationDataStudyParticipantIdColumnName}` = %1 WHERE `entity_id` = %2", array(
        1 => array($id, 'String'),
        2 => array($case_id, 'Integer'),
      ));
    }
  }

  /**
   * Checks whether the activity is of type invitation.
   *
   * @param int $activity_type_id
   * @return bool
   */
  public static function isValidActivityType($activity_type_id) {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    if ($activity_type_id == $config->invitedActivityTypeId) {
      return true;
    }
    return false;
  }

}
