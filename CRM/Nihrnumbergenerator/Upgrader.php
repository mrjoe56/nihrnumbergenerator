<?php
use CRM_Nihrnumbergenerator_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Nihrnumbergenerator_Upgrader extends CRM_Nihrnumbergenerator_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Create settings for cbr/nbr study sequence
   */
  public function install() {
    Civi::settings()->set('nbr_cbr_sequence', "0");
    Civi::settings()->set('nbr_nbr_sequence', "0");
    $this->setSequenceNumbers();
  }

  /**
   * Set nbr/cbr sequence to correct value.
   *
   * @return TRUE on success
   */
  public function upgrade_1010() {
    $this->setSequenceNumbers();
    return TRUE;
  }

  /**
   * Create contact identifier type for study participant id if it does not exist
   * @return bool
   */
  public function upgrade_1020() {
    $optionGroupName = "contact_id_history_type";
    $optionValueName = "cih_study_participant_id";
    $query = "SELECT COUNT(*)
        FROM civicrm_option_group AS cog JOIN civicrm_option_value AS cov ON cog.id = cov.option_group_id
        WHERE cog.name = %1 AND cov.name = %2";
    $count = CRM_Core_DAO::singleValueQuery($query, [
      1 => [$optionGroupName, "String"],
      2 => [$optionValueName, "String"],
    ]);
    if ($count == 0) {
      try {
        civicrm_api3('OptionValue', 'create', [
          'option_group_id' => $optionGroupName,
          'name' => $optionValueName,
          'value' => $optionValueName,
          'is_active' => 1,
          'label' => "Study Participant ID",
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Could not create contact identifier type for Study Participant ID in ")
          . __METHOD__ . E::ts(", error from API OptionValue create: ") . $ex->getMessage());
      }
    }
    $this->addExistingIdentifiers($optionValueName);
    return TRUE;
  }

  /**
   * Method to add existing study participant ID's as identifiers if they do not exist yet
   *
   * @param identifierType
   */
  private function addExistingIdentifiers($identifierType) {
    $query = "SELECT cvnpd.nvpd_study_participant_id, cont.contact_id
        FROM civicrm_value_nbr_participation_data AS cvnpd
        JOIN civicrm_case AS part ON cvnpd.entity_id = part.id
        JOIN civicrm_case_contact AS cont ON cvnpd.entity_id = cont.case_id
        LEFT JOIN civicrm_value_contact_id_history AS ident ON ident.entity_id = cont.contact_id
            AND ident.identifier_type = %1 AND cvnpd.nvpd_study_participant_id = ident.identifier
        WHERE nvpd_study_participant_id IS NOT NULL AND part.is_deleted = %2 AND identifier IS NULL";
    $dao = CRM_Core_DAO::executeQuery($query, [
      1 => [$identifierType, "String"],
      2 => [0, "Integer"],
      ]);
    while ($dao->fetch()) {
      try {
        civicrm_api3('Contact', 'addidentity', [
          'contact_id' => $dao->contact_id,
          'identifier' => $dao->nvpd_study_participant_id,
          'identifier_type' => $identifierType,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }

  /**
   * Method to set the sequence numbers
   */
  private function setSequenceNumbers() {
    $table = CRM_Nihrbackbone_BackboneConfig::singleton()->getStudyDataCustomGroup('table_name');
    $studyNumber = CRM_Nihrbackbone_BackboneConfig::singleton()->getStudyCustomField('nsd_study_number', 'column_name');
    $query = "SELECT " . $studyNumber . " AS study_number FROM " . $table . " WHERE " . $studyNumber . " LIKE %1";
    Civi::settings()->set('nbr_nbr_sequence', $highest);
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = E::ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
