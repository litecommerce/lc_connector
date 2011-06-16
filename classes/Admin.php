<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * @file
 * Coommon handler
 *
 * @category  Litecommerce connector
 * @package   Litecommerce connector
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2010 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @link      http://www.litecommerce.com/
 * @see       ____file_see____
 * @since     1.0.0
 */

/**
 * Admin 
 * 
 * @package XLite
 * @see     ____class_see____
 * @since   1.0.0
 */
abstract class LCConnector_Admin extends LCConnector_Abstract
{
    const OP_NAME_SETTINGS = 'Save';
    const OP_NAME_USERSYNC = 'Synchronize user accounts';

    /**
     * Return form description for the module settings
     *
     * @return array
     * @access public
     * @see    ____func_see____
     * @since  1.0.0
     */
    public static function getModuleSettingsForm()
    {
        $form['lcc'] = array();

        $form['lcc']['settings'] = array(
            '#type'  => 'fieldset',
            '#title' => t('LC Connector module settings'),
 
            'lc_dir' => array(
                '#type'          => 'textfield',
                '#title'         => t('LiteCommerce installation directory'),
                '#required'      => true,
                '#default_value' => variable_get('lc_dir', self::getLCDir()),
            ),
            'submit' => array(
                '#type' => 'submit',
                '#value' => t(self::OP_NAME_SETTINGS),
            ),
        );

        $form['lcc']['usersync'] = array(
            '#type' => 'fieldset',
            '#title' => t('User accounts synchronization'),

            '#description' => <<<OUT
<p>Non-synchronized user accounts were found in the Drupal and LiteCommerce databases. This means that when user is logged in to the Drupal site he/she if hasn't a LiteCommerce account cannot use the catalog of products as a registered user. By clicking on the button below Drupal and LiteCommerce account will be linked with the following rules:</p>
<ul>
<li>If non-linked accounts with same email presented both in Drupal and LiteCommerce databases then these account will be linked</li>
<li>If account presented in Drupal but missed in LiteCommerce database then the linked account will be created in LiteCommerce database with randomly generated password and the same email as Drupal account has</li>
<li>If account presented in LiteCommerce but missed in Drupal database then the linked account will be created in Drupal database with randomly generated password and the same email as LiteCommerce account has</li>
</ul>
<p>Tick the checkbox below to send notifications with links to reset password to the users who will get new Drupal accounts.</p>
OUT
,
            'notify_users' => array(
                '#type' => 'checkbox',
                '#return_value' => 1,
                '#title' => t('Require password reset for new Drupal accounts')
            ),
            'submit' => array(
                '#type' => 'submit',
                '#value' => t(self::OP_NAME_USERSYNC),
            ),

        );

        $form['#submit'][] = 'lc_connector_submit_settings_form';

        // FIXME: it's the hack. See the "submitModuleSettingsForm" method.
        // Unfortunatelly I've not found any solution to update menus immediatelly
        // (when changing the LC path)
        menu_rebuild();

        return $form;
    }

    /**
     * Submit module settings form
     *
     * @param array &$form      Form description
     * @param array &$formState Form state
     *
     * @return void
     * @access public
     * @see    ____func_see____
     * @since  1.0.0
     */
    public static function submitModuleSettingsForm(array &$form, array &$formState)
    {
        switch ($formState['values']['op']) {

            case t(self::OP_NAME_SETTINGS):
                variable_set('lc_dir', $formState['values']['lc_dir']);
                drupal_set_message(t('The configuration options have been saved.'));
                break;

            case t(self::OP_NAME_USERSYNC):
                // Run user accounts synchronization routine
                //(bool)$form_state['values']['notify_users'];
                drupal_set_message(t('User accounts have been synchronized.'));
                break;
        }
    }
}
