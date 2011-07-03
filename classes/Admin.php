<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * @file
 * Admin area handler
 *
 * @category  Litecommerce connector
 * @package   Litecommerce connector
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @link      http://www.litecommerce.com/
 * @since     1.0.0
 */

/**
 * Admin
 *
 * @since 1.0.0
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
     * @since  1.0.0
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
     * @param array &$form      Form description
     * @param array &$formState Form state
     *
     * @return void
     * @since  1.0.0
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
     * @param array &$form      Form description
     * @param array &$formState Form state
     *
     * @return void
     * @since  1.0.0
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
