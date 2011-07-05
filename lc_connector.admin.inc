<?php
// vim: set ts=4 sw=4 sts=4 et ft=php:

/**
 * @file
 * Admin area routines
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
 * Get module settings form
 *
 * @return array
 * @since  1.0.0
 */
function lc_connector_get_settings_form() {
    return LCConnector_Admin::getModuleSettingsForm();
}

/**
 * Validate module settings form
 *
 * @param array &$form       Form description
 * @param array &$form_state Form state
 *
 * @return void
 * @since  1.0.0
 */
function lc_connector_validate_settings_form(array &$form, array &$form_state) {
    return LCConnector_Admin::validateModuleSettingsForm($form, $form_state);
}

/**
 * Submit module settings form
 *
 * @param array &$form      Form description
 * @param array &$form_state Form state
 *
 * @return void
 * @since  1.0.0
 */
function lc_connector_submit_settings_form(array &$form, array &$form_state) {
    return LCConnector_Admin::submitModuleSettingsForm($form, $form_state);
}
