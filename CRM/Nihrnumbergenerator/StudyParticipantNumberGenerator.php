<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyParticipantNumberGenerator {

  public static function generateNumber($study_number, $contact_id, $case_id) {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    // First check for an existing number, if it exists return it
    $sql = "SELECT `participation_data`.`{$config->participationDataStudyParticipantIdColumnName}` AS `id`
            FROM `{$config->participationDataTableName}` `participation_data`
            INNER JOIN `civicrm_case_contact` `case_contact` ON `case_contact`.`case_id` = `participation_data`.`entity_id`
            INNER JOIN `{$config->projectDataTableName}` `project_data` ON `project_data`.`entity_id` = `participation_data`.`{$config->participationDataProjectIdColumnName}`
            INNER JOIN `{$config->studyTableName}` `study` ON `study`.`id` = `project_data`.`{$config->projectDataStudyIdColumnName}`
            WHERE `study`.`{$config->studyNrColumnName}` = %1 and `case_contact`.`contact_id` = %2 AND  `participation_data`.`{$config->participationDataStudyParticipantIdColumnName}` IS NOT NULL
            ORDER BY id DESC
            LIMIT 0, 1";
    $sqlParams[1] = array($study_number, 'String');
    $sqlParams[2] = array($contact_id, 'Integer');
    $existingId = CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    if ($existingId) {
      return $existingId;
    }

    // We need to generate a new number as an number does not exists for this participant in this study.
    $sequenceNrSql = "
            SELECT COUNT(DISTINCT `participation_data`.`{$config->participationDataStudyParticipantIdColumnName}`)
            FROM `{$config->participationDataTableName}` `participation_data`
            INNER JOIN `{$config->projectDataTableName}` `project_data` ON `project_data`.`entity_id` = `participation_data`.`{$config->participationDataProjectIdColumnName}`
            INNER JOIN `{$config->studyTableName}` `study` ON `study`.`id` = `project_data`.`{$config->projectDataStudyIdColumnName}`
            WHERE `study`.`{$config->studyNrColumnName}` = %1 AND `participation_data`.`{$config->participationDataStudyParticipantIdColumnName}` IS NOT NULL ";
    $sequenceNrSqlParams[1] = array($study_number, 'String');
    $newSequenceNr = CRM_Core_DAO::singleValueQuery($sequenceNrSql, $sequenceNrSqlParams);
    $sql = "SELECT COUNT(*) FROM `{$config->participationDataTableName}` WHERE `{$config->participationDataStudyParticipantIdColumnName}` = %1";

    $studyNumberWithouthPrefix = preg_replace("#(NBR|CBR-?)([0-9]+)#", "$2", $study_number);
    $prefix = 'SP';
    if (strpos($study_number, 'CBR')) {
      $prefix = 'S';
    }
    $studyCode = 'S'.str_pad($studyNumberWithouthPrefix, 4, 0, STR_PAD_LEFT);

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
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    $sql ="SELECT
            `civicrm_case_contact`.`contact_id`,
            `study`.`{$config->studyNrColumnName}` as `study_number`
          FROM `civicrm_case_contact`
          INNER JOIN `{$config->participationDataTableName}` `participation_data` ON `participation_data`.`entity_id` = `civicrm_case_contact`.`case_id`
          INNER JOIN `{$config->projectDataTableName}` `project_data` ON `project_data`.`entity_id` = `participation_data`.`{$config->participationDataProjectIdColumnName}`
          INNER JOIN `{$config->studyTableName}` `study` ON `study`.`id` = `project_data`.`{$config->projectDataStudyIdColumnName}`
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
