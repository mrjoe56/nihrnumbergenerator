<?php
use CRM_Nihrnumbergenerator_ExtensionUtil as E;

/**
 * StudyParticipantSequence.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_study_participant_sequence_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * StudyParticipantSequence.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_study_participant_sequence_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'StudyParticipantSequence');
}

/**
 * StudyParticipantSequence.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_study_participant_sequence_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * StudyParticipantSequence.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_study_participant_sequence_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'StudyParticipantSequence');
}
/**
 * StudyParticipantSequence.existcreate API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_study_participant_sequence_existcreate($params) {
  CRM_Nihrnumbergenerator_BAO_StudyParticipantSequence::setExistingStudySequences();
  return civicrm_api3_create_success([], [], 'StudyParticipantSequence', 'existcreate');
}
