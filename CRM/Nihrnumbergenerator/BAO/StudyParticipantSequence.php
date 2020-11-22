<?php
use CRM_Nihrnumbergenerator_ExtensionUtil as E;

class CRM_Nihrnumbergenerator_BAO_StudyParticipantSequence extends CRM_Nihrnumbergenerator_DAO_StudyParticipantSequence {

  /**
   * Create a new StudyParticipantSequence based on array-data
   *
   * @param string $studyNumber
   *
   * @return CRM_Nihrnumbergenerator_DAO_StudyParticipantSequence|NULL
   */
  public static function create($studyNumber) {
    $className = 'CRM_Nihrnumbergenerator_DAO_StudyParticipantSequence';
    $entityName = 'StudyParticipantSequence';
    if (!self::sequenceExists($studyNumber)) {
      $instance = new $className();
      $instance->copyValues([
        'study_number' => $studyNumber,
        'sequence' => 0,
      ]);
      $instance->save();
      CRM_Utils_Hook::post("create", $entityName, $instance->id, $instance);
      return $instance;
    }
    return NULL;
  }

  /**
   * Method to check if a sequence already exists for a study (should only exists for the part
   * of the number before the dot, so CBR012.1 and CBR012.2 should only have 1 sequence for CBR012
   *
   * @param $studyId
   * @param $studyNumber
   * @return false
   */
  private static function sequenceExists($studyNumber) {
    if (!empty($studyNumber)) {
      $coreStudyNumber = self::getCoreStudyNumber($studyNumber);
      if ($coreStudyNumber) {
        $query = "SELECT COUNT(*) FROM civicrm_study_participant_sequence WHERE study_number = %1";
        $count = CRM_Core_DAO::singleValueQuery($query, [1 => [$coreStudyNumber, "String"]]);
        if ($count > 0) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Method to get the part of the study number before the dot
   *
   * @param $studyNumber
   * @return mixed|string
   */
  private static function getCoreStudyNumber($studyNumber) {
    $coreStudyNumber = $studyNumber;
    if (!empty($studyNumber)) {
      $parts = explode(".", $studyNumber);
      $coreStudyNumber = $parts[0];
    }
    return $coreStudyNumber;
  }

  /**
   * Method to return the study participant id for this study for the contact if one exists
   * (should not happen often)
   * This method should only check the part of the study number before the potential dot
   *
   * @param $studyNumber
   * @param $contactId
   * @return false|int
   */
  public static function existsForContact($studyNumber, $contactId) {
    if (!empty($studyNumber) && !empty($contactId)) {
      $coreNumber = self::getCoreStudyNumber($studyNumber);
      $coreLength = strlen($coreNumber);
      $query = "SELECT c.nvpd_study_participant_id
        FROM civicrm_case_contact AS a
            JOIN civicrm_case AS b ON a.case_id = b.id
            LEFT JOIN civicrm_value_nbr_participation_data AS c ON a.case_id = c.entity_id
            LEFT JOIN civicrm_value_nbr_study_data AS d ON c.nvpd_study_id = d.entity_id
        WHERE a.contact_id = %1 AND b.is_deleted = %2 AND b.case_type_id = %3
          AND SUBSTR(d.nsd_study_number, 1, " . $coreLength . ") = %4";
      $currentId = CRM_Core_DAO::singleValueQuery($query, [
        1 => [(int) $contactId, "Integer"],
        2 => [0, "Integer"],
        3 => [(int) CRM_Nihrbackbone_BackboneConfig::singleton()->getParticipationCaseTypeId(), "Integer"],
        4 => [$coreNumber, "String"],
      ]);
      if ($currentId) {
        return ($currentId);
      }
    }
    return FALSE;
  }

  /**
   * Method to get the sequence for the study
   *
   * @param $studyNumber
   * @return false|int
   */
  public static function getStudySequence($studyNumber)   {
    $coreNumber = self::getCoreStudyNumber($studyNumber);
    $query = "SELECT sequence FROM civicrm_study_participant_sequence WHERE study_number = %1";
    $sequence = CRM_Core_DAO::singleValueQuery($query, [1 => [$coreNumber, "String"]]);
    if ($sequence || $sequence == "0") {
      $sequence++;
      return (int)$sequence;
    } else {
      Civi::log()->error("Could not find a sequence for study " . $studyNumber . " in " . __METHOD__);
      return FALSE;
    }
  }

  /**
   * Method to update the sequence number for a study in the table
   *
   * @param $studyNumber
   * @param $seqeunce
   */
  public static function updateStudySequence($studyNumber, $sequence) {
     $coreNumber = self::getCoreStudyNumber($studyNumber);
     $update = "UPDATE civicrm_study_participant_sequence SET sequence = %1 WHERE study_number = %2";
     CRM_Core_DAO::executeQuery($update, [
       1 => [(int) $sequence, "Integer"],
       2 => [$coreNumber, "String"],
     ]);
  }

}
