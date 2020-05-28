<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_StudyParticipantNumberGenerator {

  public static function generateNumber($study_number, $contact_id, $case_id) {
    // only if study campaign type
    if (isset($objectRef->campaign_type_id)) {
      if ($objectRef->campaign_type_id == CRM_Nihrbackbone_BackboneConfig::singleton()->getStudyCampaignTypeId()) {
        $centre = CRM_Nihrbackbone_NbrStudy::getCentreOfOrigin($studyId);
        if ($centre && $centre == CRM_Nihrnumbergenerator_Config::singleton()->cambridgeCenterName) {
          $prefix = 'CBR';
          $sequence = Civi::settings()->get('nbr_cbr_sequence');
          $sequence++;
          Civi::settings()->set('nbr_cbr_sequence', $sequence);
        }
        else {
          $prefix = 'NBR';
          $sequence = Civi::settings()->get('nbr_nbr_sequence');
          $sequence++;
          Civi::settings()->set('nbr_nbr_sequence', $sequence);
        }
        // add prefix to id and save in study number field
        $studyNumber = $prefix . $sequence;
        $studyNumberCustomField = "custom_" . CRM_Nihrbackbone_BackboneConfig::singleton()->getStudyCustomField('nsd_study_number', 'id');
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
