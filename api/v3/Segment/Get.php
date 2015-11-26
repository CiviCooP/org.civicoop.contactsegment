<?php

/**
 * Segment.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_segment_get_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'id',
    'type' => CRM_Utils_Type::T_INT
  );
  $spec['name'] = array(
    'name' => 'name',
    'title' => 'name',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['label'] = array(
    'name' => 'label',
    'title' => 'label',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['parent_id'] = array(
    'name' => 'parent_id',
    'title' => 'parent_id',
    'type' => CRM_Utils_Type::T_INT
  );
}

/**
 * Segment.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_segment_get($params) {
  return civicrm_api3_create_success(CRM_Contactsegment_BAO_Segment::getValues($params), $params, 'Segment', 'Get');
}

