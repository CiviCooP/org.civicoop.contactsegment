<?php

/**
 * SegmentSetting.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_segment_setting_get_spec(&$spec) {
  $spec['parent_label'] = array(
    'name' => 'parent_label',
    'title' => 'label of the parent segment',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['parent_roles'] = array(
    'name' => 'parent_roles',
    'title' => 'roles linked to the parent segment',
    'type' => 'array'
  );
  $spec['child_label'] = array(
    'name' => 'child_label',
    'title' => 'label of the child segment',
    'type' => CRM_Utils_Type::T_STRING
  );
  $spec['child_roles'] = array(
    'name' => 'child_roles',
    'title' => 'roles linked to the child segment',
    'type' => 'array'
  );
}

/**
 * SegmentSetting.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 * {@getfields segment_setting}
 */
function civicrm_api3_segment_setting_get($params) {
  $segmentSetting = new CRM_Contactsegment_BAO_SegmentSetting();
  return civicrm_api3_create_success($segmentSetting->get($params), $params, 'SegmentSetting', 'Get');
}
