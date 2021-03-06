<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FP\Web\Portal\FpOnlineRateTable\src\Plugin\Widget;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/Helper/GlobalLogger.php';
require_once dirname(dirname(__DIR__)) . '/Utils/Wordpress/CustomCssWrapper.php';
require_once dirname(dirname(__DIR__)) . '/Utils/Wordpress/LocalizationTextDomain.php';
require_once dirname(dirname(__DIR__)) . '/Utils/Wordpress/AdminNotice.php';
require_once dirname(__DIR__) . '/Helper/ResourceProxyConfigurator.php';
require_once 'IWidgetConfig.php';
require_once 'WidgetSettings.php';
require_once 'Translation.php';
require_once 'RateCalculationServiceConfigWidget.php';
require_once 'AppSettings.php';

use FP\Web\Portal\FpOnlineRateTable\src\Plugin\Helper\GlobalLogger;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\CustomCssWrapper;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\LocalizationTextDomain;
use FP\Web\Portal\FpOnlineRateTable\src\Utils\Wordpress\AdminNotice;


class Widget extends \WP_Widget {
    
    const STYLE_PATH = 'app/css/style.css';
    //const ANIM_IN_OUT_STYLE_PATH = 'bower_components/angular-ui-router-anim-in-out/css/anim-in-out.css';
    const REQUIREJS_ID = "requirejs-script-tag";
    
    static private $config;
    static private $textDomain;
    
    private $css;
    
    
    public function __construct() {
        
        $widget_ops = [
            'classname' => self::$config->htmlClass(),
            'description' => _x(
                    'Use this plugin to calculate postage prices',
                    'Widget Description',
                    'FpOnlinerateTable') ];

        parent::__construct(
                self::$config->id(),
                _x('FP Online Rate Table', 'Widget Title', 'FpOnlinerateTable'),
                $widget_ops);
        
        $this->registerAssets();
    }
    
    private function registerAssets() {
        
        $pluginUrl = plugin_dir_url(dirname(dirname(__DIR__)));
        
        $this->css = [];
        $this->css[] = new CustomCssWrapper(
                $pluginUrl . self::STYLE_PATH);
//        $this->css[] = new CustomCssWrapper(
//                $pluginUrl . self::ANIM_IN_OUT_STYLE_PATH);
        
        foreach($this->css as $style) {
            $style->register();
            $style->loadOnAction();
        }
    }
    
    public function widget($args, $instance) {
        
        echo $args['before_widget'];
        
        try {
            $widgetSettings = new WidgetSettings($this, $instance);
            self::$textDomain->load();
            //$this->css->registerAndLoad();
            
            // render title if necessary
            $title = $widgetSettings->getIfValid(WidgetSettings::TITLE);
            if(!empty($title)) {
                echo $args['before_title'] . $title . $args['after_title'];
            }
            
            // will be used in template
            $appData = $this->buildJsAppData($widgetSettings);
            
            // render actual widget
            include dirname(__DIR__) . '/Templates/widget.php';
            
            // append script tag to footer
            $this->loadRequireJs();
        } catch(\Exception $ex) {
            GlobalLogger::addError($ex);
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        
        try {
            // the first call to this function will be with an empty instance array.
            // Also the results won't be rendered. So do nothing in this case.
            if (!empty($instance)) {
                // will also be used in template
                $widgetSettings = new WidgetSettings($this, $instance);

                include dirname(__DIR__) . '/Templates/admin.php';
            }
        } catch (\Exception $ex) {
            $msg = _x('An error occured when trying to render this form',
                    'Widget Setting Error', 'FpOnlineRateTable');
            $error = $ex->getMessage();
            echo "
                <div class='error'>
                    <p>
                        {$msg}
                        <br>
                        {$error}
                    </p>
                </div>
            ";
        }
    }
    
    public function update($new_instance, $old_instance) {
     
        try {
            $widgetSettings = new WidgetSettings($this, $old_instance);
            $widgetSettings->mergeArray($new_instance);

            $result = $widgetSettings->toArray();
        } catch (\Exception $ex) {
            $result = $old_instance;
            GlobalLogger::addError($ex);
            AdminNotice::register('FpOnlineRateTable', $ex->getMessage());
        }
        
        return $result;
    }
    

    public static function registerWidget_callback() {
        register_widget(get_class());
    }
    
    public static function shortcode_callback() {
        
        try {        
            // start output buffering
            ob_start();
            
            // render this widget (all output is redirected into the buffer)
            the_widget(get_class());
            
            // extract the buffer content
            $result = ob_get_clean();
            
        } catch(\Exception $ex) {
            GlobalLogger::addError($ex);
        }
        
        return $result;
    }
    
    public static function registerOnAction(
            IWidgetConfig $config,
            LocalizationTextDomain $textDomain,
            $action = 'widgets_init') {
        
        self::$config = $config;
        self::$textDomain = $textDomain;
        
        add_action($action, get_class() . '::registerWidget_callback' );
        add_shortcode(self::$config->shortcode(),
                get_class() . '::shortcode_callback' );
    }
    
    public static function config() {
        return self::$config;
    }
    
    
    private function loadRequireJs() {
        
        // load RequireJS within the footer tag of the page. This is done
        // because currently also the scripts managed by RequireJS depend
        // on legacy scripts loaded by standard Wordpress mechanisms inside the
        // footer.
        // RequireJS is the new way of dealing with Javascript dependencies so
        // we do not need CustomJavascriptWrapper for scripts managed through
        // it.
        $jsPath = plugins_url('app', dirname(dirname(__DIR__)));
        $requireJsPath = plugins_url(
                'bower_components/requirejs', dirname(dirname(__DIR__)));
        add_action( 'wp_footer', function() use ($jsPath, $requireJsPath) {
            // Note: here we are adding an id to the script tag. This is used to
            // find the tag from Javascript (in main.js) in order to extract the
            // data-main attribute which will be used as base URL to load
            // further files from Javascript.
            // WARNING: in fact this is a hack!
            $id = self::REQUIREJS_ID;
            echo "<script id='$id' data-main='{$jsPath}/main' src='{$requireJsPath}/require.js'></script>";
        }, 99);
    }
    
    private function buildJsAppData(WidgetSettings $widgetSettings) {
        
        $appSettings = new AppSettings($widgetSettings);
        $data = [
            'config' => $appSettings->toArray(),
            'translation' => Translation::getTexts()
        ];
        $result = json_encode($data);
        
        return $result;
    }
}