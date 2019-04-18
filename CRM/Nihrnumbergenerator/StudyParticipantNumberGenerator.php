<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyParticipantNumberGenerator {

  public static function generateNumber($study_id, $contact_id, $case_id) {
    // First check for an existing number, if it exists return it
    $sql = "SELECT participation_data.nvpd_study_participant_id AS id 
            FROM civicrm_value_nihr_participation_data participation_data 
            INNER JOIN civicrm_case_contact case_contact ON case_contact.case_id = participation_data.entity_id
            INNER JOIN civicrm_value_nihr_project_data project_data ON project_data.entity_id = participation_data.nvpd_project_id
            WHERE project_data.npd_study_id = %1 and case_contact.contact_id = %2 AND  participation_data.nvpd_study_participant_id IS NOT NULL
            ORDER BY id DESC
            LIMIT 0, 1";
    $sqlParams[1] = array($study_id, 'Integer');
    $sqlParams[2] = array($contact_id, 'Integer');
    $existingId = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($existingId) {
      return $existingId;
    }

    // We need to generate a new number as an number does not exists for this participant in this study.
    $sequenceNrSql = "
            SELECT COUNT(DISTINCT participation_data.`nvpd_study_participant_id`) 
            FROM `civicrm_value_nihr_participation_data` participation_data
            INNER JOIN civicrm_value_nihr_project_data project_data ON project_data.entity_id = participation_data.nvpd_project_id 
            WHERE project_data.npd_study_id = %1 AND participation_data.`nvpd_study_participant_id` IS NOT NULL ";
    $sequenceNrSqlParams[1] = array($study_id, 'Integer');
    $newSequenceNr = CRM_Core_DAO::singleValueQuery($sequenceNrSql, $sequenceNrSqlParams);
    $sql = "SELECT COUNT(*) FROM civicrm_value_nihr_participation_data participation_data WHERE nvpd_study_participant_id = %1";
    $studyCode = 'SP'.str_pad($study_id, 4, 0, STR_PAD_LEFT);

    do {
      $newSequenceNr++;
      $sequenceCode = str_pad($newSequenceNr, 7, 0, STR_PAD_LEFT);
      $checkCharacter = CRM_Nihrnumbergenerator_Utils::generateCheckCharacter($newSequenceNr);
      $newId = $studyCode.$sequenceCode.$checkCharacter;
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
  public static function createNewNumberForCase($case_id) {
    $sql ="SELECT 
            civicrm_case_contact.contact_id, 
            project_data.npd_study_id
          FROM civicrm_case_contact
          INNER JOIN civicrm_value_nihr_participation_data participation_data ON participation_data.entity_id = civicrm_case_contact.case_id
          INNER JOIN civicrm_value_nihr_project_data project_data ON project_data.entity_id = participation_data.nvpd_project_id
          WHERE civicrm_case_contact.case_id = %1";
    $sqlParams[1] = array($case_id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while($dao->fetch()) {
      $id = self::generateNumber($dao->npd_study_id, $dao->contact_id, $case_id);
      // We assume a record already exists for this case.
      CRM_Core_DAO::executeQuery("UPDATE civicrm_value_nihr_participation_data SET nvpd_study_participant_id = %1 WHERE entity_id = %2", array(
        1 => array($id, 'String'),
        2 => array($case_id, 'Integer'),
      ));
    }
  }

}