<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Nihrnumbergenerator_Config {

  private static $singleton;

  public $volunteerIdFieldId;

  public $participantIdFieldId;

  public $volunteerIdIdentifierType = 'nihr_volunteer_id';

  public $volunteerIdsTableName = 'civicrm_value_nihr_volunteer_ids';

  public $participantIdColumnName = 'nva_participant_id';

  public $participationDataTableName = 'civicrm_value_nihr_participation_data';

  public $projectDataTableName = 'civicrm_value_nihr_project_data';

  public $projectDataStudyIdColumnName = 'npd_study_id';

  public $studyTableName = 'civicrm_nihr_study';

  public $studyNrColumnName = 'study_number';

  public $participationDataProjectIdColumnName = 'nvpd_project_id';

  public $participationDataStudyParticipantIdColumnName = 'nvpd_study_participant_id';

  public $cambridgeCenterName = 'NIHR BioResource Centre Cambridge';

  public $cambridgeCenterId;

  public $invitedActivityTypeId;

  private function __construct() {
    $this->volunteerIdFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('nva_bioresource_id', 'nihr_volunteer_ids');
    $this->participantIdFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('nva_participant_id', 'nihr_volunteer_ids');
    $this->invitedActivityTypeId = civicrm_api3('OptionValue', 'getvalue', ['return' => 'value', 'name' => 'nbr_project_invite', 'option_group_id' => 'activity_type']);
    try {
      $this->cambridgeCenterId = civicrm_api3('Contact', 'getvalue', [
        'return' => 'id',
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
