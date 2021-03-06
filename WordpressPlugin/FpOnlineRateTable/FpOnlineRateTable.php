<?php

/**
 * Plugin Name: FpOnlineRateTable
 * Plugin URI: http://unknown
 * Description: A client for the OnlineRateTable
 * Version: 01.01.16.033000
 * Author: Carsten Scharfenberg
 * Author URI: http://unknown
 * License: FP internal use only
 */

// Service URL:
// http://localhost/wordpress/wp-content/plugins/FpOnlineRateTable/3rdParty/lib/resource-proxy/proxy.php?http://localhost:33115/api/RateCalculation/GetActiveTables?clientUtcDate=10/30/2015
//
// Hinweis:
// Der resource-proxy verwendet die Enviroment Variables "HTTP_PROXY" und
// "NO_PROXY" um die System Proxy Einstellungen zu übernehmen (das läßt sich
// leider nicht abschalten...)

namespace FP\Web\Portal\FpOnlineRateTable;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once 'src/Plugin/Helper/GlobalLogger.php';
require_once 'src/Utils/Wordpress/LocalizationTextDomain.php';
require_once 'src/Utils/Wordpress/CustomCssWrapper.php';
require_once 'src/Utils/Wordpress/CustomCssWrapperConfigXml.php';
require_once 'src/Utils/Wordpress/AdminNotice.php';
require_once 'src/Plugin/Widget/Widget.php';
require_once 'src/Plugin/Widget/WidgetConfigXml.php';
require_once 'src/Utils/Helper/MonologgerConfigXml.php';

use FP\Web\Portal\FpOnlineRateTable\src\Plugin\Helper\GlobalLogger;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\MonologgerConfigXml;
use FP\Web\Portal\FpOnlineRateTable\src\Plugin\Widget\Widget;
use FP\Web\Portal\FpOnlineRateTable\src\Plugin\Widget\WidgetConfigXml;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\LocalizationTextDomain;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\CustomCssWrapper;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\CustomCssWrapperConfigXml;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\AdminNotice;


class CannotLoadConfigFileException extends \RuntimeException {
    public function __construct($configFile, $previous = null) {
        parent::__construct(
                'Cannot load config file "' . $configFile .'"',
                0, $previous);
    }
}


class Plugin {
    
    const CONFIG_FILE = 'config.xml';
    
    static private $instance;
    
    
    private function __construct() {
        
        // read config file
        $config = $this->loadConfig();
        
        // use config file to initialize logger settings.
        // Note: if 'loadConfig' fails (the failure is logged using
        // GlobalLogger) GlobalLogger uses it's default configuration.
        $this->initLogger($config);
        
        $this->initWordpressHelper($config);
        $textDomain = $this->initLocalization();
        $this->registerWidget($config, $textDomain);
        $this->handleAdminNotices();
    }
      
    private function loadConfig() {
        
        $fileName = path_join(__DIR__, self::CONFIG_FILE);
        $xml = file_get_contents($fileName);
        if(false === $xml) {
            throw new CannotLoadConfigFileException($fileName);
        }
        
        $config = new \SimpleXMLElement($xml);
        
        return $config;
    }
    
    private function initLogger(\SimpleXMLElement $config) {
        
        $loggerConfig = new MonoLoggerConfigXml($config);
        GlobalLogger::readInitialConfig($loggerConfig);
    }
    
    private function registerWidget(
            \SimpleXMLElement $config,
            LocalizationTextDomain $textDomain) {
        
        $widgetConfig = new WidgetConfigXml($config);
        Widget::registerOnAction($widgetConfig, $textDomain);
    }
    
    private function initWordpressHelper(\SimpleXMLElement $config) {
        
        $customCssConfig = new CustomCssWrapperConfigXml($config);
        CustomCssWrapper::readConfiguration($customCssConfig);
    }
    
    private function initLocalization() {
     
        $languageDir = 'languages';
        $textDomain = new LocalizationTextDomain(
                $languageDir, 'FpOnlineRateTable',
                GlobalLogger::instance());
     
        // Note:
        // the TextDomain will be globally loaded in the admin backend. In the
        // frontend it won't be loaded before the widget is rendered.
        $textDomain->loadOnAction('admin_init');
        
        return $textDomain;
    }
    
    private function handleAdminNotices() {
        
        if(!GlobalLogger::canWriteLog()) {
            AdminNotice::register(
                    'FpOnlineRateTable',
                    function() { return __(
                            'No write access to the log file. Please check the file "config.xml" in the plugin directory for the log file path.',
                            'FpOnlineRateTable');
                    });
        }
    }
    
    static public function init() {
        
        try {
            if(!isset(self::$instance)) {
                self::$instance = new self();
            }
        } catch (\Exception $ex) {
            GlobalLogger::addError($ex);
            AdminNotice::register('FpOnlineRateTable', $ex->getMessage());
        }
    }
}

Plugin::init();
