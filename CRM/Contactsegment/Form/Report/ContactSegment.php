<?php

class CRM_Contactsegment_Form_Report_ContactSegment extends CRM_Report_Form {

  protected $_addressField = FALSE;
  protected $_emailField = FALSE;
  protected $_summary = NULL;
  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE;

  /**
   * CRM_Contactsegment_Form_Report_ContactSegment constructor.
   */
  function __construct() {
    $this->_exposeContactID = FALSE;
    $config = CRM_Contactsegment_Config::singleton();
    $roleOptionGroup = $config->getRoleOptionGroup();
    foreach ($roleOptionGroup['values'] as $optionValueId => $optionValue) {
      $roleList[$optionValue['value']] = $optionValue['label'];
    }
    asort($roleList);
    $activeLabels = array(
      '' => ts('- select -'),
      0 => ts('No'),
      1 => ts('Yes')
    );

    $this->_columns = array(
      'civicrm_contact_segment' => array(
        'dao' => 'CRM_Contactsegment_DAO_ContactSegment',
        'alias' => 'cs',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'contact_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'is_active' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'segment_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'role_value' => array(
            'title' => ts('Role'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'default' => TRUE,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'default' => TRUE,
          ),
        ),
        'filters' => array(
          'role_value' => array(
            'title' => ts('Roles'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $roleList,
          ),
          'start_date' => array(
            'title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array(
            'title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'is_active' => array(
            'title' => ts('Active?'),
            'type' => CRM_Report_Form::OP_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $activeLabels,
            'default' => 1,
          ),
        ),
      ),
      'civicrm_segment' => array(
        'dao' => 'CRM_Contactsegment_DAO_Segment',
        'alias' => 'segment',
        'fields' => array(
          'label' => array(
            'title' => 'Segment',
            'type' => CRM_Utils_Type::T_STRING,
            'default' => TRUE,
            'required' => TRUE,
          ),
          'parent_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
        ),
      ),
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'alias' => 'contact',
        'fields' => array(
          'display_name' => array(
            'title' => ts('Contact Name'),
            'required' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'gender_id' => array(
            'title' => ts('Gender'),
            'type' => CRM_Utils_Type::T_STRING
          ),
          'birth_date' => array(
            'title' => ts('Birth Date'),
            'type' => CRM_Utils_Type::T_DATE
          ),
        ),
      ),
      'civicrm_phone' => array(
        'dao' => 'CRM_Core_DAO_Phone',
        'alias' => 'phone',
        'fields' => array(
          'phone' => array(
            'title' => ts('Phone'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
      ),
      'civicrm_email' => array(
        'dao' => 'CRM_Core_DAO_Email',
        'alias' => 'email',
        'fields' => array(
          'email' => array(
            'title' => ts('Email'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
      ),
      'civicrm_address' => array(
        'dao' => 'CRM_Core_DAO_Address',
        'alias' => 'addr',
        'fields' => array(
          'street_address' => array(
            'title' => ts('Address'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'postal_code' => array(
            'title' => ts('Post Code'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'city' => array(
            'title' => ts('City'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'country_id' => array(
            'title' => ts('Country'),
            'default' => TRUE,
            'type' => CRM_Utils_Type::T_STRING
          )
        ),
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  /**
   * Overridden parent method for initial processing
   */
  function preProcess() {
    $segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $this->setTitle(ts($segmentSetting['parent_label'].' Report'));
    parent::preProcess();
  }

  /**
   * Overridden parent method to set select statement
   */
  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($tableName == 'civicrm_address') {
              $this->_addressField = TRUE;
            }
            elseif ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = $field['type'] ?? NULL;
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  /**
   * Overridden parent method to set from part of query
   */
  function from() {
    $this->_from = NULL;
    foreach ($this->_columns as $tableName => $table) {
      switch ($tableName) {
        case 'civicrm_contact_segment':
          $this->_from = "FROM ".$tableName." ".$table['alias'];
          break;
        case 'civicrm_segment':
          $this->_from .= " JOIN ".$tableName." ".$table['alias']." ON "
            .$this->_aliases['civicrm_contact_segment'].".segment_id = ".$table['alias'].".id";
          break;
        case 'civicrm_contact':
          $this->_from .= " JOIN ".$tableName." ".$table['alias']." ON "
            .$this->_aliases['civicrm_contact_segment'].".contact_id = ".$table['alias'].".id";
          break;
        case 'civicrm_phone':
          $this->_from .= " JOIN ".$tableName." ".$table['alias']." ON "
            .$this->_aliases['civicrm_contact'].".id = ".$table['alias'].".contact_id AND ".
            $table['alias'].".is_primary = 1";
          break;
        case 'civicrm_email':
          $this->_from .= " JOIN ".$tableName." ".$table['alias']." ON "
            .$this->_aliases['civicrm_contact'].".id = ".$table['alias'].".contact_id AND ".
            $table['alias'].".is_primary = 1";
          break;
        case 'civicrm_address':
          $this->_from .= " JOIN ".$tableName." ".$table['alias']." ON "
            .$this->_aliases['civicrm_contact'].".id = ".$table['alias'].".contact_id AND ".
            $table['alias'].".is_primary = 1";
          break;
      }
    }
  }

  /**
   * Overridden parent method to set where part of query
   */
  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = $this->_params["{$fieldName}_relative"] ?? NULL;
            $from     = $this->_params["{$fieldName}_from"] ?? NULL;
            $to       = $this->_params["{$fieldName}_to"] ?? NULL;

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            $op = $this->_params["{$fieldName}_op"] ?? NULL;
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                $this->_params["{$fieldName}_value"] ?? NULL,
                $this->_params["{$fieldName}_min"] ?? NULL,
                $this->_params["{$fieldName}_max"] ?? NULL
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }
    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_segment']}.label";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * Overridden parent method to modify column headers
   */
  function modifyColumnHeaders() {
    $this->_columnHeaders['civicrm_segment_type'] = array('title' => ts("Type"), 'type' => CRM_Utils_Type::T_STRING);
  }


  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $gender = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'gender_id', array('localize' => TRUE));
    foreach ($rows as $rowNum => $row) {

      if (array_key_exists('civicrm_segment_parent_id', $row)) {
        if (empty($row['civicrm_segment_parent_id'])) {
          $rows[$rowNum]['civicrm_segment_type'] = $segmentSetting['parent_label'];
        } else {
          $rows[$rowNum]['civicrm_segment_type'] = $segmentSetting['child_label'];
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_gender_id', $row)) {
        $rows[$rowNum]['civicrm_contact_gender_id'] = $gender[$row['civicrm_contact_gender_id']];
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_country_id', $row)) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_display_name', $row) && $rows[$rowNum]['civicrm_contact_display_name'] &&
        array_key_exists('civicrm_contact_segment_contact_id', $row)) {
        $url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' .
          $row['civicrm_contact_segment_contact_id'], $this->_absoluteUrl);
        $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }

      if (!$entryFound) {
        break;
      }
    }
  }
}
