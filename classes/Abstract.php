<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * @file
 * Base class for all handlers
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 */

/**
 * Abstract
 */
abstract class LCConnector_Abstract {

    /**
     * Data from the module .info file
     *
     * @var   array
     */
    protected static $moduleInfo;

    /**
     * Flag; if LC "top.inc.php" is included
     *
     * @var   boolean
     */
    protected static $isLCConnected;

    /**
     * Container to store some temporary data
     *
     * @var   array
     */
    protected static $tmpData;


    // {{{ Working with temporary data

    /**
     * Set temporary data variable
     *
     * @param string $name Variable name
     * @param mixed  $value Variable value
     *
     * @return void
     */
    public static function saveVariable($name, $value) {
        self::$tmpData[$name] = $value;
    }

    /**
     * Get stored variable
     *
     * @param string $name         Variable name
     * @param mixed  $defaultValue Default value
     *
     * @return mixed
     */
    public static function getVariable($name, $defaultValue = NULL) {
        return (isset(self::$tmpData[$name])) ? self::$tmpData[$name] : $defaultValue;
    }

    // }}}

    // {{{ LC-related methods

    /**
     * Return path to LC installation (from settings or default)
     *
     * @return string
     */
    public static function getLCDir() {
        return variable_get('lc_dir', self::getModuleInfo('lc_dir_default'));
    }

    /**
     * Return absolute path to LC installation
     *
     * @return string
     */
    public static function getLCCanonicalDir() {
        return self::getCanonicalDir(self::getLCDir());
    }

    /**
     * Prepare file path
     *
     * @param string $dir Dir to prepare
     *
     * @return string
     */
    protected static function getCanonicalDir($dir) {
        return rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Return full path to the LC top inc file
     *
     * @return string
     */
    protected static function getLCTopIncFile() {
        return self::getLCCanonicalDir() . 'top.inc.php';
    }

    /**
     * Return data from the module .info file
     *
     * @param string $field Name of the field to retrieve
     *
     * @return array|string
     */
    protected static function getModuleInfo($field = NULL) {
        if (!isset(self::$moduleInfo)) {
            self::$moduleInfo = (array) drupal_parse_info_file(
                self::getCanonicalDir(dirname(dirname(__FILE__))) . 'lc_connector.info'
            );
        }

        return isset($field) ? @self::$moduleInfo[$field] : self::$moduleInfo;
    }

    /**
     * Check if we can connect to LiteCommerce
     *
     * @return boolean
     */
    public static function isLCExists() {
        return file_exists(self::getLCTopIncFile());
    }

    /**
     * Check if we already connected to LiteCommerce
     *
     * @return boolean
     */
    protected static function isLCConnected() {
        if (!isset(self::$isLCConnected) && (self::$isLCConnected = self::isLCExists())) {
            require_once self::getLCTopIncFile();
        }

        return self::$isLCConnected;
    }

    // }}}

    // {{{ Call wrappers

    /**
     * Wrapper to directly call LC-dependend methods
     *
     * @param string  $class  Handler class name
     * @param string  $method Method to call
     * @param array   $args   Call arguments
     *
     * @return mixed
     */
    public static function callDirectly($class, $method, array $args = array()) {
        return call_user_func_array(array(self::getLCClassInstance($class), $method), $args);
    }

    /**
     * Wrapper to safely call LC-dependend methods
     *
     * @param string  $class  Handler class name
     * @param string  $method Method to call
     * @param array   $args   Call arguments
     *
     * @return mixed
     */
    public static function callSafely($class, $method, array $args = array()) {
        return self::isLCConnected() ? self::callDirectly($class, $method, $args) : NULL;
    }

    /**
     * Get instance of LC singleton
     *
     * @param string $class Base part of a sigleton class name
     *
     * @return \XLite\Module\CDev\DrupalConnector\Drupal\ADrupal
     */
    protected static function getLCClassInstance($class) {
        return call_user_func(array('\XLite\Module\CDev\DrupalConnector\Drupal\\' . $class, 'getInstance'));
    }

    // }}}
}
