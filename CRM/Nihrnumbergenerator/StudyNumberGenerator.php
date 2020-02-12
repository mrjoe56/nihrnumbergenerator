<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyNumberGenerator {

  /**
   * Method to generate the study number
   *
   * @param $studyId
   * @param $objectRef
   * @throws API_Exception when unable to save study number
   */
  public static function generateStudyNumber($studyId, $objectRef) {
    // only if study campaign type
    if (isset($objectRef->campaign_type_id)) {
      if ($objectRef->campaign_type_id == CRM_Nihrbackbone_BackboneConfig::singleton()->getStudyCampaignTypeId()) {
        $prefix = 'NBR';
        $centre = CRM_Nihrbackbone_NbrStudy::getCentreOfOrigin($studyId);
        if ($centre && $centre == CRM_Nihrnumbergenerator_Config::singleton()->cambridgeCenterName) {
          $prefix = 'CBR';
        }
        // add prefix to id and save in study number field
        $studyNumber = $prefix . $studyId;
        $studyNumberCustomField = "custom_" . CRM_Nihrbackbone_BackboneConfig::singleton()->getProjectCustomField('npd_study_number', 'id');
        $apiParams = [
          'id' => $studyId,
          $studyNumberCustomField => $studyNumber,
        ];
        try {
          civicrm_api3('Campaign', 'create', $apiParams);
        }
        catch (CiviCRM_API3_Exception $ex) {
          throw new API_Exception('Could not generate a study number, error message from Campaign create API: ' . $ex->getMessage());
        }
      }
    }
  }
}
