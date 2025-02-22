<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_Config {

  private static $singleton;

  public $bioresourceIdFieldId;

  public $participantIdFieldId;

  public $volunteerIdsTableName = 'civicrm_value_nihr_volunteer_ids';

  public $bioresourceIdColumnName = 'nva_bioresource_id';

  public $participantIdColumnName = 'nva_participant_id';

  public $contactIdentityTableName = "civicrm_value_contact_id_history";

  public $participantIdentifierType = 'cih_type_participant_id';

  public $bioresourceIdentifierType = 'cih_type_bioresource_id';

  public $participationDataTableName = 'civicrm_value_nbr_participation_data';

  public $studyDataTableName = 'civicrm_value_nbr_study_data';

  public $participationDataStudyIdColumnName = 'nvpd_study_id';

  public $participationDataStudyParticipantIdColumnName = 'nvpd_study_participant_id';

  public $studyNrColumnName = 'nsd_study_number';

  public $cambridgeCenterName = 'NIHR BioResource Centre Cambridge';

  public $cambridgeCenterId;

  public $invitedActivityTypeId;

  public $studyParticipantIdIdentifier = "cih_study_participant_id";

  private function __construct() {
    $this->bioresourceIdFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('nva_bioresource_id', 'nihr_volunteer_ids');
    $this->participantIdFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('nva_participant_id', 'nihr_volunteer_ids');
    $this->invitedActivityTypeId = civicrm_api3('OptionValue', 'getvalue', ['return' => 'value', 'name' => 'nbr_project_invite', 'option_group_id' => 'activity_type']);
    try {
      $this->cambridgeCenterId = civicrm_api3('Contact', 'getvalue', [
        'return' => 'id',
        'contact_type' => 'Organization',
        'contact_sub_type' => '',
        'organization_name' => $this->cambridgeCenterName,
      ]);
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  /**
   * @return CRM_Nihrnumbergenerator_Config
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Nihrnumbergenerator_Config();
    }
    return self::$singleton;
  }

}
