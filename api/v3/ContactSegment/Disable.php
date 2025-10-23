<?php

/**
 * ContactSegment.Disable API
 * Will disable all active contact segments where end date has passed
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_contact_segment_disable($params) {
  $sql = "SELECT id, segment_id, contact_id FROM civicrm_contact_segment WHERE end_date <= CURDATE() AND is_active = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array(1, 'Integer')));
  while ($dao->fetch()) {
    civicrm_api3('ContactSegment', 'create', array(
      'id' => $dao->id,
      'end_date' => date('Y-m-d'),
      'is_active'=> 0,
      'segment_id' => $dao->segment_id,
      'contact_id' => $dao->contact_id,
    ));
  }
  $sql = "SELECT id, segment_id, contact_id FROM civicrm_contact_segment WHERE start_date = CURDATE() AND is_active = %1";
  $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array(0, 'Integer')));
  while ($dao->fetch()) {
    civicrm_api3('ContactSegment', 'create', array(
      'id' => $dao->id,
      'start_date' => date('Y-m-d'),
      'is_active'=> 1,
      'segment_id' => $dao->segment_id,
      'contact_id' => $dao->contact_id,
    ));
  }
  return civicrm_api3_create_success(array(), $params, 'ContactSegment', 'Disable');
}

