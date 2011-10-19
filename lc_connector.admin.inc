<?php
// vim: set ts=2 sw=2 sts=2 et ft=php:

/**
 * @file
 * Admin area routines
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
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
 * Get module settings form
 *
 * The method LCConnector_Admin::getModuleSettingsForm() is located in
 * lc_connector/classes/Admin.php
 *
 * This method is used to prepare the LC Connector module settings form and check the synchronization
 * of user profiles in Drupal and LiteCommerce. This method also adds the functions
 * 'lc_connector_validate_settings_form' and 'lc_connector_submit_settings_form'
 * as callbacks to validate and submit the form.
 *
 * @return array
 */
function lc_connector_get_settings_form() {
  return LCConnector_Admin::getModuleSettingsForm();
}

/**
 * Validate module settings form
 *
 * The method LCConnector_Admin::validateModuleSettingsForm() is located in
 * lc_connector/classes/Admin.php
 *
 * This method is used to validate LC Connector module settings form.
 *
 * @param array &$form
 *   Form description
 * @param array &$form_state
 *   Form state
 */
function lc_connector_validate_settings_form(array &$form, array &$form_state) {
  return LCConnector_Admin::validateModuleSettingsForm($form, $form_state);
}

/**
 * Submit module settings form
 *
 * The method LCConnector_Admin::submitModuleSettingsForm() is located in
 * lc_connector/classes/Admin.php
 *
 * This method is used to process a submit of LC Connector module settings form.
 *
 * @param array &$form
 *   Form description
 * @param array &$form_state
 *   Form state
 */
function lc_connector_submit_settings_form(array &$form, array &$form_state) {
  return LCConnector_Admin::submitModuleSettingsForm($form, $form_state);
}
