<?php

/**
 * ContactSegment.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_contact_segment_delete_spec(&$spec) {
  $spec['id']['api.required'] = 1;
}

/**
 * ContactSegment.Delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_contact_segment_delete($params) {
  if (array_key_exists('id', $params)) {
    return civicrm_api3_create_success(CRM_Contactsegment_BAO_ContactSegment::deleteWithId($params['id']), $params, 'ContactSegment', 'Delete');
  } else {
    throw new CRM_Core_Exception('Id is a mandatory param when deleting a contact segment', 'mandatory_id_missing', 0020);
  }
}

