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
        $count = CRM_Core_DAO::singleValueQuery($query, [1 => [$coreStudyNumber, "Integer"]]);
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
  public static function existsForContact($studyNumber, $contactId) {
    if (!empty($studyNumber) && !empty($contactId)) {
      $coreStudyNumber = self::getCoreStudyNumber($studyNumber);
    }
    return FALSE;
  }

}
