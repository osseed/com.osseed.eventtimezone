<?php

require_once 'eventtimezone.civix.php';
use CRM_Eventtimezone_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function eventtimezone_civicrm_config(&$config) {
  _eventtimezone_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function eventtimezone_civicrm_xmlMenu(&$files) {
  _eventtimezone_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function eventtimezone_civicrm_install() {
  _eventtimezone_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function eventtimezone_civicrm_postInstall() {
  _eventtimezone_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function eventtimezone_civicrm_uninstall() {
  _eventtimezone_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function eventtimezone_civicrm_enable() {
  _eventtimezone_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function eventtimezone_civicrm_disable() {
  _eventtimezone_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function eventtimezone_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eventtimezone_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function eventtimezone_civicrm_managed(&$entities) {
  _eventtimezone_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function eventtimezone_civicrm_caseTypes(&$caseTypes) {
  _eventtimezone_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function eventtimezone_civicrm_angularModules(&$angularModules) {
  _eventtimezone_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function eventtimezone_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _eventtimezone_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_buildForm().
 */
function eventtimezone_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Event_Form_ManageEvent_EventInfo') {
    $timezone_identifiers = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);
    foreach ($timezone_identifiers as $key => $value) {
      $options[$value] = $value;
    }
    $form->add('select', 'timezone', ts('Timezone'), $options);
    if ($form->_id) {
      $query = "
      SELECT timezone FROM civicrm_event WHERE id = $form->_id";
      $timezone_default = CRM_Core_DAO::singleValueQuery($query);
      $defaults['timezone'] = $timezone_default;
      $form->setDefaults($defaults);
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function eventtimezone_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Event_Form_ManageEvent_EventInfo') {
    $submit =  $form->getVar('_submitValues');
    $timezone = $submit['timezone'];
    if (empty($form->_id) && !empty($submit['timezone'])) {
      $result = civicrm_api3('Event', 'get', array(
        'sequential' => 1,
        'return' => array("id"),
        'title' => $submit['title'],
        'event_type_id' => $submit['event_type_id'],
        'default_role_id' => $submit['default_role_id'],
      ));
      if ($result['count'] == 1) {
        $event_id = $result['values'][0]['id'];
        $query = "
        UPDATE civicrm_event
        SET timezone = '$timezone'
        WHERE id = $event_id
        ";
        CRM_Core_DAO::executeQuery($query);
      }
    }
    else {
      $event_id = $form->_id;
      $query = "
      UPDATE civicrm_event
      SET timezone = '$timezone'
      WHERE id = $event_id
      ";
      CRM_Core_DAO::executeQuery($query);
    }
  }
}

/**
 * Implements hook_civicrm_alterContent().
 */
function eventtimezone_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  if ($context == 'page' && $tplName == 'CRM/Event/Page/EventInfo.tpl') {
    $result = civicrm_api3('Event', 'get', array(
      'sequential' => 1,
      'return' => array("start_date", "end_date"),
      'id' => $object->_id,
    ));
    $start_date = $result['values'][0]['event_start_date'];
    $end_date = $result['values'][0]['event_end_date'];
    // Get event timezone.
    $query = "
    SELECT timezone FROM civicrm_event WHERE id = $object->_id";
    $timezone = CRM_Core_DAO::singleValueQuery($query);
    // Convert date
    $start_date_timestamp = new DateTime($start_date, new DateTimeZone($timezone));
    $start_date_st = date_format($start_date_timestamp, 'M jS Y g:iA T');
    $content = str_replace("event_start_date", $start_date_st, $content);

    if ($end_date) {
      $end_date_timestamp = new DateTime($end_date, new DateTimeZone($timezone));
      $end_date_st = date_format($end_date_timestamp, 'M jS Y g:iA T');
      $content = str_replace("event_end_date", $end_date_st, $content);
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function eventtimezone_civicrm_navigationMenu(&$menu) {
  _eventtimezone_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('The Page'),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _eventtimezone_civix_navigationMenu($menu);
} // */
