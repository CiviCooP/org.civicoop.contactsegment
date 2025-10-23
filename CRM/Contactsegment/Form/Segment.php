<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 19 November 2015
 * @license AGPL-3.0
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Contactsegment_Form_Segment extends CRM_Core_Form {

  protected $_segmentId = NULL;
  protected $_segment = array();
  protected $_parentLabel = NULL;
  protected $_childLabel = NULL;

  /**
   * Overridden parent method to buildQuickForm (call parent method too)
   *
   * @access public
   */
  function buildQuickForm() {
    $this->addFormElements();
    parent::buildQuickForm();
  }

  /**
   * Method to get the segment labels
   *
   * @access private
   */
  private function getSegmentLabels() {
    $segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $this->_parentLabel = $segmentSetting['parent_label'];
    $this->_childLabel = $segmentSetting['child_label'];
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_segmentId = CRM_Utils_Request::retrieve('sid', 'Integer');
    $this->getSegmentLabels();
    if ($this->_action != CRM_Core_Action::ADD && $this->_segmentId) {
      $this->_segment = civicrm_api3('Segment', 'Getsingle', array('id' => $this->_segmentId));
    }
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->deleteSegmentAndReturn();
    }
    if ($this->_action == CRM_Core_Action::DISABLE) {
      $this->disableSegmentAndReturn();
    }
    if ($this->_action == CRM_Core_Action::ENABLE) {
      $this->enableSegmentAndReturn();
    }
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionLabel = "Add";
        $segmentTypeLabel = $this->_parentLabel." or ".$this->_childLabel;
        break;
      case CRM_Core_Action::UPDATE:
        $actionLabel = "Edit";
        if (!isset($this->_segment['parent_id']) || empty($this->_segment['parent_id'])) {
          $segmentTypeLabel = $this->_parentLabel;
        } else {
          $segmentTypeLabel = $this->_childLabel;
        }
        break;
      case CRM_Core_Action::VIEW:
        $actionLabel = "View";
        if (!$this->_segment['parent_id']) {
          $segmentTypeLabel = $this->_parentLabel;
        } else {
          $segmentTypeLabel = $this->_childLabel;
        }
        break;
    }
    CRM_Utils_System::setTitle($segmentTypeLabel);
    $this->assign('actionLabel', $actionLabel);
    $this->assign('segmentTypeLabel', $segmentTypeLabel);
  }

  /**
   * Overridden parent method to process form (calls parent method too)
   *
   * @access public
   */
  function postProcess() {
    $this->_segmentId = $this->_submitValues['segment_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveSegment($this->_submitValues);
    }
    parent::postProcess();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['segment_id'] = $this->_segmentId;
    if (!isset($this->_segment['parent_id']) || empty($this->_segment['parent_id'])) {
      $defaults['segment_type'] = 'parent';
    } else {
      $defaults['segment_type'] = 'child';
    }
    if ($this->_action == CRM_Core_Action::VIEW || $this->_action == CRM_Core_Action::UPDATE) {
      if (isset($this->_segment['label'])) {
        $defaults['segment_label'] = $this->_segment['label'];
        if (!isset($this->_segment['parent_id']) || empty($this->_segment['parent_id'])) {
          $defaults['segment_parent'] = $this->_segment['parent_id'];
        }
      }
    }
    $defaults['is_active'] = true;
    if ($this->_segmentId && empty($this->_segment['is_active'])) {
      $defaults['is_active'] = false;
    }

    return $defaults;
  }

  /**
   * Function to add form elements
   *
   * @access protected
   */
  protected function addFormElements() {
    $parentList = $this->getParentList();
    $this->add('hidden', 'segment_id');
    $this->add('text', 'segment_label', ts('Label'), array('size' => 128), true);
    if ($this->_action == CRM_Core_Action::ADD) {
      $types = array($this->_parentLabel, $this->_childLabel);
      foreach ($types as $key => $var) {
        $typeOptions[$key] = HTML_QuickForm::createElement('radio', $key, ts('Type'), $var, $key);
      }
      $this->addGroup($typeOptions, 'segment_type_list', ts('Type'));
    }
    $this->add('select', 'segment_parent', ts('Parent'), $parentList);
    $this->add('checkbox', 'is_active', ts('Is active'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Function to save the segment
   *
   * @param $formValues
   * @access protected
   */
  protected function saveSegment($formValues) {
    $params = array();
    if ($formValues['segment_id']) {
      $params['id'] = $formValues['segment_id'];
    }
    $params['label'] = $formValues['segment_label'];
    if (isset($formValues['is_active'])) {
      $params['is_active'] = $formValues['is_active'] ? '1' : '0';
    } else {
      $params['is_active'] = 0;
    }
    $params['name'] = CRM_Contactsegment_Utils::generateNameFromLabel($params['label']);
    if ($this->_action == CRM_Core_Action::ADD) {
      $segmentType = key($formValues['segment_type_list']);
    } else {
      if ($formValues['segment_parent']) {
        $segmentType = 1;
      } else {
        $segmentType = 0;
      }
    }
    switch ($segmentType) {
      case 0:
        $params['parent_id'] = NULL;
        $statusTitle = $this->_parentLabel." saved";
        $statusMessage = $this->_parentLabel." ".$params['label']." saved";
        break;
      case 1:
        $params['parent_id'] = $formValues['segment_parent'];
        $statusTitle = $this->_childLabel." saved";
        $statusMessage = $this->_childLabel." ".$params['label']." from "
          .$this->_parentLabel." ".$this->getSegmentParentLabel($formValues['segment_parent'])." saved";
        break;
    }
    $this->_segment = civicrm_api3('Segment', 'Create', $params);
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
  }

  /**
   * Method to delete segment
   *
   */
  protected function deleteSegmentAndReturn() {
    if (!$this->_segment['parent_id']) {
      $statusMessage = $this->_parentLabel." ".$this->_segment['label']." deleted";
      $statusTitle = $this->_parentLabel." deleted";
    } else {
      $statusMessage = $this->_childLabel." ".$this->_segment['label']." from "
        .$this->_parentLabel." ".$this->getSegmentParentLabel($this->_segment['parent_id'])." deleted";
      $statusTitle = $this->_childLabel." deleted";
    }
    civicrm_api3('Segment', 'Delete', array('id' => $this->_segmentId));
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Method to disable segment
   *
   */
  protected function disableSegmentAndReturn() {
    if (!$this->_segment['parent_id']) {
      $statusMessage = $this->_parentLabel." ".$this->_segment['label']." disabled";
      $statusTitle = $this->_parentLabel." disabled";
    } else {
      $statusMessage = $this->_childLabel." ".$this->_segment['label']." from "
        .$this->_parentLabel." ".$this->getSegmentParentLabel($this->_segment['parent_id'])." disabled";
      $statusTitle = $this->_childLabel." disabled";
    }
    civicrm_api3('Segment', 'Create', array('id' => $this->_segmentId, 'is_active' => 0));
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Method to enable segment
   *
   */
  protected function enableSegmentAndReturn() {
    if (!$this->_segment['parent_id']) {
      $statusMessage = $this->_parentLabel." ".$this->_segment['label']." enabled";
      $statusTitle = $this->_parentLabel." disabled";
    } else {
      $statusMessage = $this->_childLabel." ".$this->_segment['label']." from "
        .$this->_parentLabel." ".$this->getSegmentParentLabel($this->_segment['parent_id'])." enabled";
      $statusTitle = $this->_childLabel." enabled";
    }
    civicrm_api3('Segment', 'Create', array('id' => $this->_segmentId, 'is_active' => 1));
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

  /**
   * Method to get select list of possible parent segments
   *
   * @access protected
   * @return array
   */
  protected function getParentList() {
    $parentList = array("- select -") + CRM_Contactsegment_Utils::getParentList();
    asort($parentList);
    return $parentList;
  }

  /**
   * Method to get the label of a segment
   *
   * @param int $parentId
   * @return array|string
   */
  protected function getSegmentParentLabel($parentId) {
    $parentParams = array('id' => $parentId, 'return' => 'label');
    try {
      return civicrm_api3('Segment', 'Getvalue', $parentParams);
    } catch (CRM_Core_Exception $ex) {
      return "";
    }
  }
}

