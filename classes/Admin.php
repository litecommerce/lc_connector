<?php
// vim: set ts=2 sw=2 sts=2 et:

/**
 * @file
 * Admin area handler
 */

/**
 * Since the integration code highly depends on the LiteCommerce version being
 * integrated, this Drupal module only forwards Drupal hooks into a
 * LiteCommerce doing the actual work. That way shop owners can upgrade both
 * LiteCommerce and the integration code at once via the LiteCommerce automatic
 * upgrade function within the shop back end.
 *
 * The back side of this is the use of complex wrapper functions for Drupal
 * hooks. To prevent possible issues, get rid of global variables and make the
 * interface easier to understand the wrapper logic is localized in PHP classes
 * with private and protected class methods and static fields.
 */

/**
 * Admin
 */
abstract class LCConnector_Admin extends LCConnector_Abstract {

  /**
   * LC Connector settings form button label
   */
  const OP_NAME_SETTINGS = 'Save';


  /**
   * Return form description for the module settings
   *
   * @return array
   */
  public static function getModuleSettingsForm() {
    variable_del('lc_user_sync_notify');

    $form['lcc'] = array();

    if (variable_get('lc_dir', FALSE) && !self::isLCExists()) {
      drupal_set_message(t('LiteCommerce software not found in the specified directory'), 'error');
    }

    $form['lcc']['settings'] = array(
      '#type'  => 'fieldset',
      '#title' => t('LC Connector module settings'),

      'lc_dir' => array(
        '#type'          => 'textfield',
        '#title'         => t('LiteCommerce installation directory'),
        '#required'      => TRUE,
        '#default_value' => variable_get('lc_dir', self::getLCDir()),
      ),

      'submit' => array(
        '#type' => 'submit',
        '#value' => t(self::OP_NAME_SETTINGS),
      ),
    );

    self::callSafely('UserSync', 'addUserSyncForm', array(&$form));

    $form['#validate'][] = 'lc_connector_validate_settings_form';
    $form['#submit'][] = 'lc_connector_submit_settings_form';

    // FIXME: it's the hack. See the "submitModuleSettingsForm" method.
    // Unfortunatelly I've not found any solution to update menus immediatelly
    // (when changing the LC path)
    menu_rebuild();

    return $form;
  }

  /**
   * Validate module settings form
   *
   * @param array &$form
   *   Form description
   * @param array &$formState
   *   Form state
   */
  public static function validateModuleSettingsForm(array &$form, array &$formState) {
    $message = NULL;

    // Check if LiteCommerce exists in the specified directory
    if (t(self::OP_NAME_SETTINGS) == $formState['values']['op']) {

      if (!empty($formState['values']['lc_dir'])) {

        // Backup of lc_dir option
        $lcDirOrig = variable_get('lc_dir');

        // Set new value to lc_dir
        variable_set('lc_dir', $formState['values']['lc_dir']);

        // Check if LC exists in directory specified by the options 'lc_dir'
        if (!self::isLCExists()) {
          $message = t(
            'LiteCommerce software not found in the specified directory (:dir)',
            array(':dir' => $formState['values']['lc_dir'])
          );
        }

        // Restore original value of lc_dir to allow submitModuleSettingsForm() method modify it
        variable_set('lc_dir', $lcDirOrig);
      }

      if ($message) {
        form_error($form['lcc']['settings']['lc_dir'], $message, 'error');
      }
    }
  }

  /**
   * Submit module settings form
   *
   * @param array &$form
   *   Form description
   * @param array &$formState
   *   Form state
   */
  public static function submitModuleSettingsForm(array &$form, array &$formState) {
    switch ($formState['values']['op']) {
      case t(self::OP_NAME_SETTINGS):
        variable_set('lc_dir', $formState['values']['lc_dir']);
        drupal_set_message(t('The configuration options have been saved.'));
        break;

      default:
        // Run user accounts synchronization routine
        self::callSafely('UserSync', 'processUserSyncFormSubmit', array(&$form, &$formState));
    }
  }
}
