<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyNumberGenerator {

  public static function generateStudyNumber($study_id) {
    $config = CRM_Nihrnumbergenerator_Config::singleton();
    $study = civicrm_api3('NihrStudy', 'getsingle', ['id' => $study_id]);
    $prefix = 'NBR';
    if (isset($study['centre_study_origin_id']) && $study['centre_study_origin_id'] == $config->cambridgeCenterId) {
      $prefix = 'CBR';
    }

    $existingCheckSql = "SELECT COUNT(*) FROM `{$config->studyTableName}` WHERE `{$config->studyNrColumnName}` = %1";
    $newSequenceSql = "SELECT COUNT(*) FROM `{$config->studyTableName}` WHERE `{$config->studyNrColumnName}` LIKE '{$prefix}%'";
    $newSequenceNr = CRM_Core_DAO::singleValueQuery($newSequenceSql);
    $studyNr = $prefix.$newSequenceNr;
    do {
      $newSequenceNr++;
      $studyNr = $prefix.$newSequenceNr;
      $sqlParams = array(
        1 => array($studyNr, 'String'),
      );
      $existAlready = CRM_Core_DAO::singleValueQuery($existingCheckSql, $sqlParams);
    } while ($existAlready);

    $updateSql = "UPDATE `{$config->studyTableName}` SET `{$config->studyNrColumnName}` = %1 WHERE `id` = %2";
    $sqlParams = array (
      1 => array($studyNr, 'String'),
      2 => array($study_id, 'Integer')
    );
    CRM_Core_DAO::executeQuery($updateSql, $sqlParams);
  }

}
