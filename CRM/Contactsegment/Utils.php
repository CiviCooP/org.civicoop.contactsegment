<?php
/**
 * Class with general static util functions for extension
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-V3.0
 */
class CRM_Contactsegment_Utils {

  /**
   * Method to delete role option group with all its values
   *
   * @access public
   * @static
   */
  public static function removeRoleOptionGroup() {
    $config = CRM_Contactsegment_Config::singleton();
    $optionGroupId = $config->getRoleOptionGroup('id');
    try {
      $optionValues = civicrm_api3('OptionValue', 'Get', array('option_group_id' => $optionGroupId));
      foreach ($optionValues['values'] as $optionValueId => $optionValue) {
        try {
          civicrm_api3('OptionValue', 'Delete', array('id' => $optionValueId));
        } catch (CRM_Core_Exception $ex) {}
      }
    } catch (CRM_Core_Exception $ex) {}
    try {
      civicrm_api3('OptionGroup', 'Delete', array('id' => $optionGroupId));
    } catch (CRM_Core_Exception $ex) {}
  }

  /**
   * Method to create or update option value
   *
   * @param $params
   * @return array|bool
   * @access public
   * @static
   */
  public static function createOptionValue($params) {
    if (empty($params) || !$params['name'] || !$params['option_group_id']) {
      return FALSE;
    }
    $optionValueId = self::getOptionValueId($params['option_group_id'], $params['name']);
    if ($optionValueId) {
      $params['id'] = $optionValueId;
    }
    try {
      return civicrm_api3('OptionValue', 'Create', $params);
    } catch (CRM_Core_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to get option value id with option group id and name
   *
   * @param int $optionGroupId
   * @param string $optionValueName
   * @return array|bool
   * @access public
   * @static
   */
  public static function getOptionValueId($optionGroupId, $optionValueName) {
    $params = array(
      'option_group_id' => $optionGroupId,
      'name' => $optionValueName,
      'return' => 'id');
    try {
      return civicrm_api3('OptionValue', 'Getvalue', $params);
    } catch (CRM_Core_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to retrieve option group by name
   *
   * @param string $optionGroupName
   * @return array|bool
   * @access public
   * @static
   */
  public static function getOptionGroupWithName($optionGroupName) {
    if (empty($optionGroupName)) {
      return FALSE;
    }
    try {
      return civicrm_api3('OptionGroup', 'Getsingle', array('name' => $optionGroupName));
    } catch (CRM_Core_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to create option group
   *
   * @param $params
   * @return array|bool
   * @access public
   * @static
   */
  public static function createOptionGroup($params) {
    if (empty($params) || !isset($params['name']) || empty($params['name'])) {
      return FALSE;
    }
    if (!$params['title']) {
      $params['title'] = $params['name'];
    }
    if (!$params['description']) {
      $params['description'] = "";
    }
    try {
     return civicrm_api3('OptionGroup', 'Create', $params);
    } catch (CRM_Core_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to determine max key in navigation menu (core solutions do not cater for child keys!)
   *
   * @param array $menuItems
   * @return int $maxKey
   */
  public static function getMaxMenuKey($menuItems) {
    $maxKey = 0;
    foreach ($menuItems as $menuKey => $menuItem) {
      if ($menuKey > $maxKey) {
        $maxKey = $menuKey;
      }
      if (isset($menuItem['child'])) {
        foreach ($menuItem['child'] as $childKey => $child) {
          if ($childKey > $maxKey) {
            $maxKey = $childKey;
          }
        }
      }
    }
    return $maxKey;
  }

  /**
   * Method to generate a name from a label, replacing some chars with "_"
   *
   * @param $label
   * @return mixed
   */
  public static function generateNameFromLabel($label) {
    if (empty($label)) {
      return $label;
    }
    $name = strtolower($label);
    $config = CRM_Contactsegment_Config::singleton();
    foreach ($config->getReplaceableChars() as $char) {
      $name = str_replace($char, "_", $name);
      $name = str_replace("__", "_", $name);
    }
    return $name;
  }


  /**
   * Method to get Role Name With Label
   *
   * @param $roleLabel
   * @return string
   * @throws CRM_Core_Exception
   */
  public static function getRoleNameWithLabel($roleLabel) {
    $config = CRM_Contactsegment_Config::singleton();
    $params = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'label' => $roleLabel,
      'return' => 'name'
    );
    return civicrm_api3('OptionValue', 'Getvalue', $params);
  }

  /**
   * Method to get Role Label With Value
   *
   * @param $roleValue
   * @return array
   * @throws CRM_Core_Exception
   */
  public static function getRoleLabel($roleValue) {
    $config = CRM_Contactsegment_Config::singleton();
    $params = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'value' => $roleValue,
      'return' => 'label'
    );
    return civicrm_api3('OptionValue', 'Getvalue', $params);
  }

  /**
   * Method to get list of possible roles for a segment
   *
   * @return array
   * @throws CRM_Core_Exception
   *
   */
  public static function getRoleList() {
    $roleList = array();
    $config = CRM_Contactsegment_Config::singleton();
    $optionValueParams = array(
      'option_group_id' => $config->getRoleOptionGroup('id'),
      'is_active' => 1
    );
    $roles = civicrm_api3('OptionValue', 'Get', $optionValueParams);
    foreach ($roles['values'] as $optionValueId => $optionValue) {
      $roleList[$optionValue['value']] = $optionValue['label'];
    }
    return $roleList;
  }

  /**
   * Method to get list of segment parents
   *
   * @return array
   * @throws CRM_Core_Exception
   */
  public static function getParentList() {
    $parentList = array();
    $parents = civicrm_api3('Segment', 'Get', array('parent_id' => 'null', 'is_active' => 1));
    foreach ($parents['values'] as $parentId => $parent) {
      if (isset($parent['label'])) {
        $parentList[$parentId] = $parent['label'];
      } else {
        $parentList[$parentId] = " - not specified -";
      }
    }
    asort($parentList);
    return $parentList;
  }

  /**
   * Method to get list of segment children with parent id
   *
   * @param int $parentId
   * @return array
   * @access public
   * @static
   */
  public static function getChildList($parentId = NULL) {
    if (!$parentId) {
      $query = 'SELECT id FROM civicrm_segment WHERE parent_id IS NULL ORDER BY label ASC LIMIT 1';
      $parentId = CRM_Core_DAO::singleValueQuery($query);
    }
    $childList = array();
    $children = civicrm_api3('Segment', 'Get', array('parent_id' => $parentId, 'is_active' => 1));
    foreach ($children['values'] as $childId => $child) {
      $childList[$childId] = $child['label'];
    }
    asort($childList);
    return $childList;
  }

  /**
   * Method to get the full list of all children with parent label
   *
   * @return array
   * @access public
   * @static
   */
  public static function getFullChildList() {
    $childList = array();
    $query = 'SELECT child.id AS childId, parent.label AS parentLabel, child.label AS childLabel
      FROM civicrm_segment child JOIN civicrm_segment parent ON child.parent_id = parent.id
      WHERE child.parent_id IS NOT NULL AND is_active = %1';
    $dao = CRM_Core_DAO::executeQuery($query, array(1 => array(1, 'Integer')));
    while ($dao->fetch()) {
      $childList[$dao->childId] = '('.$dao->parentLabel.') '.$dao->childLabel;
    }
    asort($childList);
    return $childList;
  }

  /**
   * Method to determine if the segment role should be unique or not
   *
   * @param string $roleLabel
   * @param string $segmentType
   * @return boolean
   * @access public
   * @static
   */
  public static function isSegmentRoleUnique($roleLabel, $segmentType) {
    $segmentSettings = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $roleName = self::getRoleNameWithLabel($roleLabel);
    if ($segmentType == 'child') {
      return $segmentSettings['child_roles'][$roleName]['unique'];
    } else {
      return $segmentSettings['parent_roles'][$roleName]['unique'];
    }
  }

  /**
   * Method to check if there is an active contact segment for role in period that is
   * not contact_id in params (only used for unique contact segments)
   *
   * Returns false when there is no active contact segment for role. Otherwise return the id of the current contact segment
   *
   * Ignores contact segments where start_date equals end_date
   *
   * @param $params
   * @return false|int
   */
  public static function activeCurrentContactSegmentForRole($params) {
    $mandatoryParams = array('role', 'segment_id', 'start_date', 'contact_id');
    foreach ($mandatoryParams as $mandatoryParam) {
      if (!array_key_exists($mandatoryParam, $params)) {
        return FALSE;
      }
    }
    $startDate = new DateTime($params['start_date']);
    $apiParams = array(
        'role_value' => $params['role'],
        'segment_id' => $params['segment_id']
    );
    try {
      $existingContactSegments = civicrm_api3('ContactSegment', 'Get', $apiParams);
      foreach ($existingContactSegments['values'] as $existingContactSegment) {
        // no need to check if contact is the same
        if ($existingContactSegment['contact_id'] != $params['contact_id']) {
          // no need to check if start_date equals end_date
          if (empty($existingContactSegment['end_date']) || $existingContactSegment['start_date'] != $existingContactSegment['end_date']) {
            // return id if start_date of params is before end_date of found contact segment
            if (!isset($existingContactSegment['end_date'])) {
              return $existingContactSegment['id'];
            } else {
              $endDate = new DateTime($existingContactSegment['end_date']);
              if ($startDate <= $endDate) {
                return $existingContactSegment['id'];
              }
            }
          }
        }
      }
    } catch (CRM_Core_Exception $ex) {
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Method to check if 2 dates overlap
   *
   * @param $startDate
   * @param $endDate
   * @return bool
   */
  public static function overlapDates($startDate, $endDate) {
    $testStartDate = new DateTime($startDate);
    $testEndDate = new DateTime($endDate);
    if ($testEndDate >= $testStartDate) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}