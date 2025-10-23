<?php

/**
 * ContactSegment.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_contact_segment_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['contact_id'] = array(
    'name' => 'contact_id',
    'title' => 'contact_id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['segment_id'] = array(
    'name' => 'segment_id',
    'title' => 'segment_id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['role_value'] = array(
    'name' => 'role_value',
    'title' => 'role_value',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['start_date'] = array(
    'name' => 'start_date',
    'title' => 'start_date',
    'type' => CRM_Utils_Type::T_DATE
  );
  $spec['end_date'] = array(
    'name' => 'end_date',
    'title' => 'end_date',
    'type' => CRM_Utils_Type::T_DATE
  );
  $spec['is_active'] = array(
    'name' => 'is_active',
    'title' => 'is_active',
    'type' => CRM_Utils_Type::T_INT
  );
}

/**
 * ContactSegment.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_contact_segment_get($params) {
  return civicrm_api3_create_success(CRM_Contactsegment_BAO_ContactSegment::getValues($params), $params, 'ContactSegment', 'Get');
}

