<?php
// vim: set ts=4 sw=4 sts=4 et ft=php:

/**
 * @file
 * Installation process handler
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
 * Install
 *
 * @since 1.0.0
 */
abstract class LCConnector_Install extends LCConnector_Abstract {

    /**
     * Database params (cache)
     *
     * @var   array
     * @since 1.0.0
     */
    protected static $dbParams;


    // {{{ Hook handlers

    /**
     * Get module tables schema
     *
     * @return array
     * @since  1.0.0
     */
    public static function getSchema() {
        return array(
            'block_lc_widget_settings' => array(
                'description' => t('List of LC widget settings'),
                'fields'      => array(
                    'bid' => array(
                        'description' => t('Block id'),
                        'type'        => 'int',
                        'not null'    => TRUE,
                        'default'     => 0,
                    ),
                    'name' => array(
                        'description' => t('Setting code'),
                        'type'        => 'char',
                        'length'      => 32,
                        'not null'    => TRUE,
                        'default'     => '',
                    ),
                    'value' => array(
                        'description' => t('Setting value'),
                        'type'        => 'varchar',
                        'length'      => 255,
                    ),
                ),
                'indexes' => array(
                    'bid' => array('bid'),
                ),
                'unique keys' => array(
                    'bid_name' => array('bid', 'name'),
                ),
                'foreign keys' => array(
                    'settings' => array(
                        'table'   => 'block',
                        'columns' => array('bid' => 'bid'),
                    ),
                ),
            ),
        );
    }

    /**
     * Perform install
     *
     * @return void
     * @since  1.0.0
     */
    public static function performInstall() {
        $description = array(
            'description' => t('LC class'),
            'type'        => 'varchar',
            'length'      => 255,
        );

        db_add_field('block_custom', 'lc_class', $description);

        self::callSafely('Module', 'setDrupalRootURL', array(self::getDrupalBaseURL()));
    }

    /**
     * Perform uninstall
     *
     * @return void
     * @since  1.0.0
     */
    public static function performUninstall() {
        db_drop_field('block_custom', 'lc_class');
    }

    /**
     * Check requirements
     *
     * @param string $phase Installation type
     *
     * @return array
     * @since  1.0.0
     */
    public static function checkRequirements($phase) {
        if (self::includeLCFiles()) {
            $method = 'checkRequirements' . ('install' === $phase ? 'Install' : 'Update');
            $requirements = self::$method();
        }
        else {
            $requirements['lc_not_found'] = array(
                'description' => st('LiteCommerce software not found in :dir', array('dir' => self::getLCDir())),
                'severity'    => REQUIREMENT_ERROR,
            );
        }

        return $requirements;
    }

    // }}}

    // {{{ Auxiliary methods

    /**
     * Returns Drupal URL (original drupal_detect_baseurl() corrected to support aliases)
     *
     * @return string
     * @since  1.0.0
     */
    public static function getDrupalBaseURL() {
        $url = drupal_detect_baseurl();

        if (preg_match('/\.php(.*)$/', basename($url))) {
            $url = dirname($url);
        }

        if (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME']) {
            $url = str_replace(parse_url($url, PHP_URL_HOST), $_SERVER['HTTP_HOST'], $url);
        }

        return rtrim($url, '/');
    }

    /**
     * Trying to include installation scripts
     *
     * @return string
     * @since  1.0.0
     */
    public static function includeLCFiles() {
        if (!defined('XLITE_INSTALL_MODE')) {
            define('XLITE_INSTALL_MODE', TRUE);
        }

        if (self::isLCExists()) {
            $result = TRUE;

            foreach (array('Includes/install/init.php', 'Includes/install/install.php')  as $includeFile) {
                $file = self::getLCCanonicalDir() . $includeFile;

                if (file_exists($file)) {
                    require_once $file;
                }
                else {
                    $result = FALSE;
                    break;
                }
            }
        }

        return !empty($result);
    }

    /**
     * Returns an array of database connection parameters
     *
     * @return array
     * @since  1.0.0
     */
    public static function getDatabaseParams() {
        if (!isset(self::$dbParams)) {
            global $databases;

            self::$dbParams = array();

            if (!empty($databases['default']['default'])) {
                $mysql = $databases['default']['default'];
            }
            elseif ('post' == strtolower($_SERVER['REQUEST_METHOD']) && !empty($_POST['mysql'])) {
                $mysql = $_POST['mysql'];
                $mysql['driver'] = 'mysql';
            }

            if (!empty($mysql)) {
                self::$dbParams['mysqluser'] = $mysql['username'];
                self::$dbParams['mysqlpass'] = $mysql['password'];
                self::$dbParams['mysqlhost'] = $mysql['host'];
                self::$dbParams['mysqlport'] = isset($mysql['port']) ? $mysql['port'] : '';
                self::$dbParams['mysqlsock'] = isset($mysql['unix_socket']) ? $mysql['unix_socket'] : '';
                self::$dbParams['mysqlbase'] = $mysql['database'];

                if (isset($mysql['db_prefix'])) {
                    self::$dbParams['drupal_prefix'] = $mysql['db_prefix'];
                }
                elseif (isset($mysql['prefix'])) {
                    self::$dbParams['drupal_prefix'] = $mysql['prefix'];
                }

                self::$dbParams['driver'] = $mysql['driver'];
            }
        }

        return self::$dbParams;
    }

    // }}}

    // {{{ Check requirements

    /**
     * Check requirements in update mode ($phase != 'install')
     *
     * @return array
     * @since  1.0.0
     */
    protected static function checkRequirementsUpdate() {
        $requirements = array();

        if (!isLiteCommerceInstalled(self::getDatabaseParams(), $message)) {
            $requirements['lc_not_installed'] = array(
                'description' => st(
                    'The installed LiteCommerce software not found. '
                    . 'It is required to install LiteCommerce and specify correct path '
                    . 'to them in the LC Connector module settings.'
                ),
                'severity'    => REQUIREMENT_WARNING,
            );
        }

        return $requirements;
    }

    /**
     * Check requirements in install mode ($phase == 'install')
     *
     * @return array
     * @since  1.0.0
     */
    protected static function checkRequirementsInstall() {
        $requirements = array();
        $dbParams     = self::getDatabaseParams();
        $message      = NULL;

        if (isLiteCommerceInstalled($dbParams, $message)) {
            $requirements['lc_already_installed'] = array(
                'title'       => 'Installation status',
                'value'       => st('The installed LiteCommerce software found. It means that LiteCommerce will not be installed.'),
                'description' => $message,
                'severity'    => REQUIREMENT_WARNING,
            );
        }
        else {
            $stopChecking = FALSE;

            if (isset($dbParams['driver']) && 'mysql' !== $dbParams['driver']) {
                $requirements['lc_mysql_needed'] = array(
                    'description' => 'LiteCommerce software does not support the specified database type: ' . $db_type . '(' . $db_url . ')',
                    'severity'    => REQUIREMENT_ERROR,
                );
                $stopChecking = TRUE;
            }

            $tablePrefix = \Includes\Utils\ConfigParser::getOptions(array('database_details', 'table_prefix'));

            if (isset($dbParams['drupal_prefix']) && $tablePrefix === $dbParams['drupal_prefix']) {
                $requirements['lc_db_prefix_reserved'] = array(
                    'description' => st(
                        'Tables prefix \':prefix\' is reserved by LiteCommerce. Please specify other prefix in the settings.php file.',
                        array(':prefix' => $tablePrefix)
                    ),
                    'severity'    => REQUIREMENT_ERROR,
                );
                $stopChecking = TRUE;
            }

            if (!$stopChecking) {
                if (!defined('LC_URI')) {
                    define('LC_URI', preg_replace('/\/install(\.php).*$/', '', request_uri()) . '/modules/lc_connector/litecommerce');
                }

                if (!defined('DB_URL') && !empty($dbParams)) {
                    define('DB_URL', serialize($dbParams));
                }

                $requirements['lc_already_installed'] = array(
                    'title'       => 'Installation status',
                    'value'       => 'LiteCommerce software is not installed',
                    'description' => $message,
                    'status'      => TRUE,
                );

                $requirements = array_merge($requirements, doCheckRequirements());

                foreach ($requirements as $reqName => $reqData) {
                    $requirements[$reqName]['description'] = 'LiteCommerce: '
                        . $reqData[empty($reqData['description']) ? 'title' : 'description'];

                    $requirements[$reqName]['severity'] = $reqData['status']
                        ? REQUIREMENT_OK
                        : ($reqData['critical'] ? REQUIREMENT_ERROR : REQUIREMENT_WARNING);
                }
            }
        }

        return $requirements;
    }

    // }}}
}
