<?php

require_once 'nihrnumbergenerator.civix.php';
use CRM_Nihrnumbergenerator_ExtensionUtil as E;

function nihrnumbergenerator_civicrm_post($op, $objectName, $id, &$objectRef) {
  if ($objectName == 'Individual' && $op == 'create') {
    CRM_Nihrnumbergenerator_VolunteerNumberGenerator::createNewNumberForContact($id);
  }
  if ($objectName == 'Case' && $op == 'create') {
    CRM_Core_Transaction::addCallback(
      CRM_Core_Transaction::PHASE_POST_COMMIT,
      function() use ($id) {
        CRM_Nihrnumbergenerator_StudyParticipantNumberGenerator::createNewNumberForCase($id);
      }
    );
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function nihrnumbergenerator_civicrm_config(&$config) {
  _nihrnumbergenerator_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function nihrnumbergenerator_civicrm_xmlMenu(&$files) {
  _nihrnumbergenerator_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function nihrnumbergenerator_civicrm_install() {
  _nihrnumbergenerator_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function nihrnumbergenerator_civicrm_postInstall() {
  _nihrnumbergenerator_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function nihrnumbergenerator_civicrm_uninstall() {
  _nihrnumbergenerator_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function nihrnumbergenerator_civicrm_enable() {
  _nihrnumbergenerator_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function nihrnumbergenerator_civicrm_disable() {
  _nihrnumbergenerator_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function nihrnumbergenerator_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _nihrnumbergenerator_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function nihrnumbergenerator_civicrm_managed(&$entities) {
  _nihrnumbergenerator_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function nihrnumbergenerator_civicrm_caseTypes(&$caseTypes) {
  _nihrnumbergenerator_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function nihrnumbergenerator_civicrm_angularModules(&$angularModules) {
  _nihrnumbergenerator_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function nihrnumbergenerator_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _nihrnumbergenerator_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function nihrnumbergenerator_civicrm_entityTypes(&$entityTypes) {
  _nihrnumbergenerator_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function nihrnumbergenerator_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function nihrnumbergenerator_civicrm_navigationMenu(&$menu) {
  _nihrnumbergenerator_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _nihrnumbergenerator_civix_navigationMenu($menu);
} // */
