<?php
/**
 * Page Segment to list all Segments with their Children
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 November 2015
 * @license AGPL-3.0
 */
require_once 'CRM/Core/Page.php';

class CRM_Contactsegment_Page_Segment extends CRM_Core_Page {
  protected $_segmentSetting = array();

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->initializePager();
    $displaySegments = $this->getSegments();
    $this->assign('segments', $displaySegments);
    parent::run();
  }

  /**
   * Function to get the segments
   *
   * @return array $displaySegments
   * @access protected
   */
  protected function getSegments() {
    $displaySegments = array();
    list($offset, $limit) = $this->_pager->getOffsetAndRowCount();
    $query = "SELECT sgmnt.* FROM civicrm_segment_tree tree JOIN civicrm_segment sgmnt ON tree.id = sgmnt.id LIMIT %1, %2";
    $queryParams[1] = array($offset, 'Integer');
    $queryParams[2] = array($limit, 'Integer');
    $daoSegments = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($daoSegments->fetch()) {
      $row = array();
      $row['label'] = $daoSegments->label;
      $row['is_active'] = $daoSegments->is_active ? true : false;
      if (empty($daoSegments->parent_id)) {
        $row['type'] = $this->_segmentSetting['parent_label'];
        $row['parent'] = "";
      } else {
        $row['type'] = $this->_segmentSetting['child_label'];
        try {
          $row['parent'] = civicrm_api3('Segment', 'Getvalue', array('id' => $daoSegments->parent_id, 'return' => 'label'));
        } catch (CRM_Core_Exception $ex) {
          $row['parent'] = '';
        }
      }
      $row['actions'] = $this->setRowActions($daoSegments);
      $displaySegments[$daoSegments->id] = $row;
    }
    return $displaySegments;  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param object $daoSegments
   * @return array $pageActions
   * @access protected
   */
  protected function setRowActions($daoSegments) {
    $pageActions = array();
    $editUrl = CRM_Utils_System::url('civicrm/segment', 'action=update&sid='.$daoSegments->id, true);
    $viewUrl = CRM_Utils_System::url('civicrm/segment', 'action=view&sid='.$daoSegments->id, true);
    $pageActions[] = '<a class="action-item" title="View" href="'.$viewUrl.'">View</a>';
    $pageActions[] = '<a class="action-item" title="Edit" href="'.$editUrl.'">Edit</a>';
    if ($daoSegments->is_active == 0) {
      $enableUrl = CRM_Utils_System::url('civicrm/segment', 'action=enable&sid='.$daoSegments->id, true);
      $pageActions[] = '<a class="action-item" title="Enable" href="'.$enableUrl.'">Enable</a>';
    } else {
      $disableUrl = CRM_Utils_System::url('civicrm/segment', 'action=disable&sid='.$daoSegments->id, true);
      $pageActions[] = '<a class="action-item" title="Disable" href="'.$disableUrl.'">Disable</a>';
    }
    $deleteUrl = CRM_Utils_System::url('civicrm/segment', 'action=delete&sid='.$daoSegments->id);
    $pageActions[] = '<a class="action-item" title="Delete" href="'.$deleteUrl.'">Delete</a>';
    return $pageActions;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    $this->_segmentSetting = civicrm_api3('SegmentSetting', 'Getsingle', array());
    $this->assign('parentSegmentLabel', $this->_segmentSetting['parent_label']);
    $this->assign('childSegmentLabel', $this->_segmentSetting['child_label']);
    CRM_Utils_System::setTitle(ts($this->_segmentSetting['parent_label']. "s and " . $this->_segmentSetting['child_label'] . "s"));
    $this->assign('addUrl', CRM_Utils_System::url('civicrm/segment', 'action=add', true));
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/segmentlist', 'reset=1', true));
  }

  /**
   * Method to initialize pager
   *
   * @access protected
   */
  protected function initializePager() {
    $params = [
      'total' => CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_segment"),
      'rowCount' => 50,
      'status' => ts('Sectors and Areas of Expertise %%StatusMessage%%'),
      'buttonBottom' => 'PagerBottomButton',
      'buttonTop' => 'PagerTopButton',
      'pageID' => $this->get(CRM_Utils_Pager::PAGE_ID),
    ];
    $this->_pager = new CRM_Utils_Pager($params);
    $this->assign('pager', $this->_pager);
  }
}
