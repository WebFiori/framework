<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\logic;

use webfiori\conf\SiteConfig;
use webfiori\framework\File;
/**
 * A class that can be used to control basic settings of the web site and 
 * save them to the file 'SiteConfig.php'
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class WebsiteController extends Controller {
    /**
     * An associative array that contains initial system configuration variables.
     * The array has the following values:
     * <ul>
     * <li>site-descriptions = array(<ul>
     * <li>EN = 'WebFiori'</li>
     * <li>AR = 'ويب فيوري'</li>
     * </ul>)</li>
     * <li>base-url = ''</li>
     * <li>primary-language = 'EN'</li>
     * <li>title-separator = ' | '</li>
     * <li>home-page = 'index'</li>
     * <li>admin-theme-name = 'WebFiori Theme'</li>
     * <li>theme-name = 'WebFiori Theme'</li>
     * <li>site-descriptions = array(<ul>
     * <li>EN = ''</li>
     * <li>AR = ''</li>
     * </ul>)</li>
     * <li>config-file-version => 1.2.1</li>
     * </ul>
     * @since 1.0
     */
    const INITIAL_WEBSITE_CONFIG_VARS = [
        'website-names' => [
            'EN' => 'WebFiori',
            'AR' => 'ويب فيوري'
        ],
        'base-url' => '',
        'primary-language' => 'EN',
        'title-separator' => ' | ',
        'home-page' => 'index',
        'admin-theme-name' => 'WebFiori Theme',
        'theme-name' => 'WebFiori Theme',
        'site-descriptions' => [
            'EN' => '',
            'AR' => ''
        ],
        'config-file-version' => '1.2.1',
    ];
    /**
     *
     * @var WebsiteController 
     */
    private static $singleton;
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * @since 1.0
     */
    public function createSiteConfigFile() {
        if (!class_exists('webfiori\conf\SiteConfig')) {
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
        }
    }
    /**
     * Returns a singleton instance of the class.
     * @return WebsiteController
     * @since 1.0
     */
    public static function get() {
        if (self::$singleton === null) {
            self::$singleton = new WebsiteController();
        }

        return self::$singleton;
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * The returned array will have the following indices: 
     * <ul>
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the name of the web site in the given language.</li>
     * <li><b>base-url</b>: The URL at which system pages will be served from. 
     * usually, this URL is used in the tag 'base' of the web page.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>primary-language</b>: Primary language of the website.
     * <li><b>admin-theme-name</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general web site description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains web site configuration 
     * info.
     * @since 1.0
     */
    public function getSiteConfigVars() {
        $cfgArr = WebsiteController::INITIAL_WEBSITE_CONFIG_VARS;

        if (class_exists('webfiori\conf\SiteConfig')) {
            $SC = SiteConfig::get();
            $cfgArr['website-names'] = $SC->getWebsiteNames();
            $cfgArr['base-url'] = $SC->getBaseURL();
            $cfgArr['title-separator'] = $SC->getTitleSep();
            $cfgArr['home-page'] = $SC->getHomePage();
            $cfgArr['primary-language'] = $SC->getPrimaryLanguage();
            $cfgArr['site-descriptions'] = $SC->getDescriptions();
            $cfgArr['theme-name'] = $SC->getBaseThemeName();
            $cfgArr['admin-theme-name'] = $SC->getAdminThemeName();
        }

        return $cfgArr;
    }
    /**
     * Updates web site configuration based on some attributes.
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>primary-language</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the name of the web site in the given language.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title. Two common 
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site. For example, 
     * If root URL of the web site is 'https://www.example.com', This page is served 
     * when the user visits this URL.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>admin-theme-name</b>: If the web site has two UIs (One for normal 
     * users and another for admins), this one 
     * can be used to serve the UI for web site admins.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the general web site description in the given language.</li></li>
     * </ul> 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr) {
        $confArr = $this->getSiteConfigVars();

        foreach ($confArr as $k => $v) {
            if (isset($websiteInfoArr[$k])) {
                $confArr[$k] = $websiteInfoArr[$k];
            }
        }
        $this->writeSiteConfig($confArr);
    }
    /**
     * Initialize new session or use an existing one.
     * Note that the name of the session must be 'wf-session' in 
     * order to initialize it.
     * @param array $options An array of session options. See 
     * Controller::useSettion() for more information about available options.
     * @return boolean If session is created or resumed, the method will 
     * return true. False otherwise.
     * @since 1.0.1
     */
    public function useSession($options = []) {
        if (gettype($options) == 'array' && isset($options['name']) && $options['name'] == 'wf-session') {
            return parent::useSession($options);
        }

        return false;
    }
    /**
     * A method to save changes to web site configuration file.
     * @param array $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeSiteConfig($configArr) {
        $names = "[\n";
        foreach ($configArr['website-names'] as $k => $v) {
            $names .= '            \''.$k.'\'=>\''.$v.'\','."\n";
        }
        $names .= '        ]';
        $descriptions = "[\n";

        foreach ($configArr['site-descriptions'] as $k => $v) {
            $descriptions .= '            \''.$k.'\'=>\''.$v.'\','."\n";
        }
        $descriptions .= '        ]';
        
        $fileAsStr = "<?php\n"
            . "namespace webfiori\conf;\n"
            . "\n"
            . "use webfiori\\framework\Util;\n"
            . "/**\n"
            . "  * Website configuration class.\n"
            . "  * This class is used to control the following settings:\n"
            . "  * <ul>\n"
            . "  * <li>The base URL of the website.</li>\n"
            . "  * <li>The primary language of the website.</li>\n"
            . "  * <li>The name of the website in different languages.</li>\n"
            . "  * <li>The general description of the website in different languages.</li>\n"
            . "  * <li>The character that is used to separate the name of the website from page title.</li>\n"
            . "  * <li>The theme of the website.</li>\n"
            . "  * <li>Admin theme of the website (if uses one).</li>\n"
            . "  * <li>The home page of the website.</li>\n"
            . "  * </ul>\n"
            . "  */\n"
            . "class SiteConfig {\n"
            . "    /**\n"
            . "     * The name of admin control pages Theme.\n"
            . "     * @var string\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    private \$adminThemeName;\n"
            . "    /**\n"
            . "     * The name of base website UI Theme.\n"
            . "     * @var string\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    private \$baseThemeName;\n"
            . "    /**\n"
            . "     * The base URL that is used by all web site pages to fetch resource files.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$baseUrl;\n"
            . "    /**\n"
            . "     * Configuration file version number.\n"
            . "     * @var string\n"
            . "     * @since 1.2\n"
            . "     */\n"
            . "    private \$configVision;\n"
            . "    /**\n"
            . "     * An array which contains different descriptions in different languages.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$descriptions;\n"
            . "    /**\n"
            . "     * The URL of the home page.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$homePage;\n"
            . "    /**\n"
            . "     * The primary language of the website.\n"
            . "     */\n"
            . "    private \$primaryLang;\n"
            . "    /**\n"
            . "     * A singleton instance of the class.\n"
            . "     * @var SiteConfig\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private static \$siteCfg;\n"
            . "    /**\n"
            . "     *\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$titleSep;\n"
            . "    /**\n"
            . "     * An array which contains all website names in different languages.\n"
            . "     * @var string\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    private \$webSiteNames;\n"
            . "    private function __construct() {\n"
            . "        \$this->configVision = '".$configArr['config-file-version']."';\n"
            . "        \$this->webSiteNames = ".$names.";\n"
            . "        \$this->baseUrl = Util::getBaseURL();\n"
            . "        \$this->titleSep = '".trim($configArr['title-separator'])."';\n"
            . "        \$this->primaryLang = '".trim($configArr['primary-language'])."';\n"
            . "        \$this->baseThemeName = '".$configArr['theme-name']."';\n"
            . "        \$this->adminThemeName = '".$configArr['admin-theme-name']."';\n"
            . "        \$this->homePage = Util::getBaseURL();\n"
            . "        \$this->descriptions = ".$descriptions.";\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an instance of the configuration file.\n"
            . "     * @return SiteConfig\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function get() {\n"
            . "        if (self::\$siteCfg != null) {\n"
            . "            return self::\$siteCfg;\n"
            . "        }\n"
            . "        self::\$siteCfg = new SiteConfig();\n"
            . "        \n"
            . "        return self::\$siteCfg;\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the name of the theme that is used in admin control pages.\n"
            . "     * @return string The name of the theme that is used in admin control pages.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getAdminThemeName() {\n"
            . "        return self::get()->_getAdminThemeName();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the name of base theme that is used in website pages.\n"
            . "     * Usually, this theme is used for the normall visitors of the web site.\n"
            . "     * @return string The name of base theme that is used in website pages.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getBaseThemeName() {\n"
            . "        return self::get()->_getBaseThemeName();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the base URL that is used to fetch resources.\n"
            . "     * The return value of this method is usually used by the tag 'base'\n"
            . "     * of web site pages.\n"
            . "     * @return string the base URL.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getBaseURL() {\n"
            . "        return self::get()->_getBaseURL();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns version number of the configuration file.\n"
            . "     * This value can be used to check for the compatability of configuration\n"
            . "     * file\n"
            . "     * @return string The version number of the configuration file.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getConfigVersion() {\n"
            . "        return self::get()->_getConfigVersion();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an associative array which contains different website descriptions\n"
            . "     * in different languages.\n"
            . "     * Each index will contain a language code and the value will be the description\n"
            . "     * of the website in the given language.\n"
            . "     * @return string An associative array which contains different website descriptions\n"
            . "     * in different languages.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getDescriptions() {\n"
            . "        return self::get()->_getDescriptions();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the home page URL of the website.\n"
            . "     * @return string The home page URL of the website.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getHomePage() {\n"
            . "        return self::get()->_getHomePage();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the primary language of the website.\n"
            . "     * This function will return a language code such as 'EN'.\n"
            . "     * @return string Language code of the primary language.\n"
            . "     * @since 1.3\n"
            . "     */\n"
            . "    public static function getPrimaryLanguage() {\n"
            . "        return self::get()->_getPrimaryLanguage();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns the character (or string) that is used to separate page title from website name.\n"
            . "     * @return string A string such as ' - ' or ' | '. Note that the method\n"
            . "     * will add the two spaces by default.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getTitleSep() {\n"
            . "        return self::get()->_getTitleSep();\n"
            . "    }\n"
            . "    /**\n"
            . "     * Returns an array which contains diffrent website names in different languages.\n"
            . "     * Each index will contain a language code and the value will be the name\n"
            . "     * of the website in the given language.\n"
            . "     * @return array An array which contains diffrent website names in different languages.\n"
            . "     * @since 1.0\n"
            . "     */\n"
            . "    public static function getWebsiteNames() {\n"
            . "        return self::get()->_getWebsiteNames();\n"
            . "    }\n"
            . "    private function _getAdminThemeName() {\n"
            . "        return \$this->adminThemeName;\n"
            . "    }\n"
            . "    private function _getBaseThemeName() {\n"
            . "        return \$this->baseThemeName;\n"
            . "    }\n"
            . "    private function _getBaseURL() {\n"
            . "        return \$this->baseUrl;\n"
            . "    }\n"
            . "    private function _getConfigVersion() {\n"
            . "        return \$this->configVision;\n"
            . "    }\n"
            . "    private function _getDescriptions() {\n"
            . "        return \$this->descriptions;\n"
            . "    }\n"
            . "    private function _getHomePage() {\n"
            . "        return \$this->homePage;\n"
            . "    }\n"
            . "    \n"
            . "    private function _getPrimaryLanguage() {\n"
            . "        return \$this->primaryLang;\n"
            . "    }\n"
            . "    private function _getTitleSep() {\n"
            . "        return \$this->titleSep;\n"
            . "    }\n"
            . "    private function _getWebsiteNames() {\n"
            . "        return \$this->webSiteNames;\n"
            . "    }\n"
            . "}\n";
        $mailConfigFile = new File('SiteConfig.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
}
