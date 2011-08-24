<?php
// vim: set ts=4 sw=4 sts=4 et ft=php:

/**
 * @file
 * Admin area routines
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 */

/**
 * Get module settings form
 *
 * @return array
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
 */
function lc_connector_validate_settings_form(array &$form, array &$form_state) {
    return LCConnector_Admin::validateModuleSettingsForm($form, $form_state);
}

/**
 * Submit module settings form
 *
 * @param array &$form       Form description
 * @param array &$form_state Form state
 *
 * @return void
 */
function lc_connector_submit_settings_form(array &$form, array &$form_state) {
    return LCConnector_Admin::submitModuleSettingsForm($form, $form_state);
}
