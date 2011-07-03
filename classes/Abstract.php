<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * @file
 * Base class for all handlers
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
 * Abstract
 *
 * @since 1.0.0
 */
abstract class LCConnector_Abstract {

    /**
     * Data from the module .info file
     *
     * @var   array
     * @since 1.0.0
     */
    protected static $moduleInfo;

    /**
     * Flag; if LC "top.inc.php" is included
     *
     * @var   boolean
     * @since 1.0.0
     */
    protected static $isLCConnected;

    /**
     * Container to store some temporary data
     *
     * @var   array
     * @since 1.0.0
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
     * @since  1.0.0
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
     * @since  1.0.0
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
     * @since  1.0.0
     */
    public static function getLCDir() {
        return variable_get('lc_dir', self::getModuleInfo('lc_dir_default'));
    }

    /**
     * Return absolute path to LC installation
     *
     * @return string
     * @since  1.0.0
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
     * @since  1.0.0
     */
    protected static function getCanonicalDir($dir) {
        return rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Return full path to the LC top inc file
     *
     * @return string
     * @since  1.0.0
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
     * @since  1.0.0
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
     * @since  1.0.0
     */
    public static function isLCExists() {
        return file_exists(self::getLCTopIncFile());
    }

    /**
     * Check if we already connected to LiteCommerce
     *
     * @return boolean
     * @since  1.0.0
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
     * @since  1.0.0
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
     * @since  1.0.0
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
     * @since  1.0.0
     */
    protected static function getLCClassInstance($class) {
        return call_user_func(array('\XLite\Module\CDev\DrupalConnector\Drupal\\' . $class, 'getInstance'));
    }

    // }}}
}
