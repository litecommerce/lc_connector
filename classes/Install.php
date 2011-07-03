<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * @file
 * Installation process handler
 *
 * @category  Litecommerce connector
 * @package   Litecommerce connector
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2010 Creative Development LLC <info@cdev.ru>. All rights reserved
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
        $description = array('description' => t('LC class'), 'type' => 'varchar', 'length' => 255);
        db_add_field('block_custom', 'lc_class', $description);

        self::callSafely('Module', 'setDrupalRootURL', array(rtrim(lc_connector_detect_drupal_baseurl(), '/') . '/'));
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

    // }}}

    // {{{ Check requirements

    /**
     * Check requirements
     *
     * @return array
     * @since  1.0.0
     */
    public static function checkRequirements($phase) {
        $errorMsg = NULL;
        $requirements = array();

        // Trying to include LiteCommerce installation scripts
        $errorMsg = self::includeLCFiles();

        if (!isset($errorMsg)) {
            $requirements = ('install' === $phase) ? self::checkRequirementsInstall() : self::checkRequirementsUpdate();

        }
        elseif (defined('XLITE_INSTALL_MODE')) {
            $requirements['lc_not_found'] = array('description' => $errorMsg, 'severity' => REQUIREMENT_ERROR);
        }

        return $requirements;
    }

    /**
     * Trying to include installation scripts and return an error message if failed
     *
     * @return string
     * @since  1.0.0
     */
    protected static function includeLCFiles() {
        $errorMsg = NULL;

        if (!defined('XLITE_INSTALL_MODE')) {
            define('XLITE_INSTALL_MODE', true);
        }

        $includeFiles = array(
            'Includes/install/init.php',
            'Includes/install/install.php',
        );

        foreach ($includeFiles as $includeFile) {
            $file = self::getLCCanonicalDir() . $includeFile;

            if (file_exists($file)) {
                require_once $file;

            }
            else {
                $errorMsg = st(
                    'LiteCommerce software not found in :lcdir (file :filename)',
                    array(':lcdir' => self::getLCDir(), ':filename' => $file)
                );
                break;
            }
        }

        return $errorMsg;
    }

    /**
     * Check requirements in update mode ($phase != 'install')
     *
     * @return array
     * @since  1.0.0
     */
    protected static function checkRequirementsUpdate() {
        $requirements = array();

        $message = 'The installed LiteCommerce software not found. '
            . 'It is required to install LiteCommerce and specify correct path '
            . 'to them in the LC Connector module settings.';

        if (!isLiteCommerceInstalled(lc_connector_get_database_params(), $message)) {
            $requirements['lc_not_installed'] = array('description' => st($message), 'severity' => REQUIREMENT_WARNING);
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
        $dbParams = lc_connector_get_database_params();
        $message = NULL;

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
                    $requirements[$reqName]['description'] = 'LiteCommerce: ' . $reqData[empty($reqData['description']) ? 'title' : 'description'];

                    if (FALSE === $reqData['status']) {
                        if (TRUE === $reqData['critical']) {
                            $requirements[$reqName]['severity'] = REQUIREMENT_ERROR;

                        }
                        else {
                            $requirements[$reqName]['severity'] = REQUIREMENT_WARNING;
                        }

                    }
                    else {
                        $requirements[$reqName]['severity'] = REQUIREMENT_OK;
                    }
                }
            }
        }

        return $requirements;
    }

    // }}}
}
