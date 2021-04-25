<?php
namespace ibrahim\themes;

use webfiori\framework\Page;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\json\Json;
use webfiori\ui\HTMLNode;
use webfiori\ui\JsCode;
use webfiori\framework\ui\WebPage;
use webfiori\framework\WebFioriApp;

/**
 * A base page that can be extended to create system pages.
 * 
 * Used to simplfy some of the common tasks.
 *
 * @author Ibrahim
 */
class BasePage extends WebPage {
    private $isDark;
    /**
     * A json object that holds backend data. Used to send data to 
     * frontend.
     * 
     * @var Json 
     */
    private $jsonData;
    /**
     *
     * @var JsCode 
     */
    private $topInlineJs;
    /**
     * Creates new instance of the class.
     * 
     * @param string $vueScript The path to the script which is used to initialize 
     * vue and vuetify.
     * 
     * @param string $pageTitle The title of the page.
     * 
     * @param string $description The description of the page.
     */
    public function __construct($vueScript = '', $pageTitle = 'Title', $description = '') {
        parent::__construct();
        $this->setTheme(IbrahimTheme::class);
        $this->jsonData = new Json([
            'rtl' => $this->getTranslation()->getWritingDir() == 'rtl',
            'darkTheme' => new Json([
                'primary' => '#8bc34a',
                'secondary' => '#4caf50',
                'accent' => '#795548',
                'error' => '#f44336',
                'warning' => '#ff9800', 
                'info' => '#607d8b',
                'success' => '#00bcd4'
            ]),
            'lightTheme' => new Json([
                'primary' => '#8bc34a',
                'secondary' => '#4caf50',
                'accent' => '#795548',
                'error' => '#f44336',
                'warning' => '#ff9800', 
                'info' => '#607d8b',
                'success' => '#00bcd4'
            ])
        ]);
        if (strlen($vueScript) != 0) {
            $this->setVueJs($vueScript);
        } else {
            $this->setVueJs('assets/ibrahim/default.js');
        }
        $this->setTitle($pageTitle);
        $this->setDescription($description);
        $this->insert($this->getTheme()->createHTMLNode([
            'name' => 'heading',
            'title' => $pageTitle
        ]));
        $this->addBeforeRender(function(BasePage $thisPage) {
            
            $snackBarsCount = 4;
            $snackbarsJsonArr = [];
            for ($x = 0 ; $x < $snackBarsCount ; $x++) {
                $node = new HTMLNode('v-snackbar', [
                    'v-model' => "snackbars[$x].snackbar",
                    ':color' => "snackbars[$x].statusColor",
                    ':timeout' => "snackbars[$x].snackbarTimeout",
                ]);

                $node->addChild('v-icon')
                ->text("{{snackbars[$x].icon}}");
                $node->addChild('div', [
                    'v-html' => "snackbars[$x].statusText",
                    'style' => [
                        'display' => 'inline'
                    ]
                ]);
                $node->addChild('template', [
                    'v-slot:action' => '{attrs}'
                ])->addChild('v-btn', [
                    '@click' => "snackbars[$x].snackbar = false",
                    'v-bind' => "attrs",
                    ':color' => "snackbars[$x].statusColor",
                    'icon'
                ])->addChild('v-icon', [
                    'color' => 'white'
                ])->text('mdi-close-circle');

                $snackbarsJsonArr[] = new Json([
                    'snackbar' => false,
                    'statusColor' => 'green',
                    'snackbarTimeout' => 5000,
                    'statusText' => 'Hello',
                    'icon' => 'mdi-information'
                ]);
                $thisPage->insert($node);
            }

            $thisPage->addToJson([
                'snackbars' => $snackbarsJsonArr
            ]);


            if ($thisPage->isDark()) {
                $css = new HTMLNode('style');
                $css->addTextNode("input{color:white;}");
                $thisPage->getDocument()->getHeadNode()->addChild($css);
            }

            $thisPage->addInlineJs('window.data = ' . $thisPage->getJson() . ';');
        });
        $this->topInlineJs = new JsCode();

        $this->getDocument()->getHeadNode()->addChild($this->topInlineJs);
        $this->_checkIsDark();
    }
    /**
     * Adds an inline JavAscript code to the &gt;head&lt; tag of the page.
     * 
     * this is mainly used to initialize JavAscript variables that might be used 
     * in front-end.
     * 
     * @param string $code A valid JavaScript code as string.
     */
    public function addInlineJs($code) {
        $this->getTopInlineJs()->addCode($code."\n");
    }
    /**
     * Adds a set of attributes to the json data.
     * 
     * @param array $arrOfAttrs An associative array. The indices of the array 
     * are attributes names and the value of each index is the value that will 
     * be passed.
     */
    public function addToJson($arrOfAttrs) {
        foreach ($arrOfAttrs as $attrKey => $attrVal) {
            $this->getJson()->add($attrKey, $attrVal);
        }
    }
    /**
     * Creates a v-btn element.
     * 
     * @param array $props An associative array that holds button attributes.
     * 
     * @param string $text An optional button text.
     * 
     * @param string $icon An optional icon to add to the button. The value of 
     * this argument must be of the mdi- icons set.
     * 
     * @param array $iconProps An optional array that holds icon properties.
     * 
     * @return HTMLNode
     */
    public function createButton($props = [], $text = null, $icon = null, $iconProps = []) {
        $btn = new HTMLNode('v-btn', $props);

        if ($text !== null) {
            $btn->text($text);
        }

        if ($icon !== null) {
            $btn->addChild('v-icon', $iconProps, false)->text($icon);
        }

        return $btn;
    }
    /**
     * Creates a basic v-data-table with the ability to search.
     * 
     * @param string $searchLabel A string that represents search label.
     * 
     * @param string $searchModel The name of the model that represents search value.
     * 
     * @param array $attrs An array of attributes for the datatable.
     * 
     * @return HTMLNode
     */
    public function createDataTable($searchLabel = 'Search', $searchModel = 'search', array $attrs = []) {
        $table = new HTMLNode('v-data-table');
        $searchArea = new HTMLNode('template', [
            'v-slot:top'
        ]);
        $searchArea->addChild('v-text-field', [
            'v-model' => $searchModel,
            'label' => $searchLabel,
            'class' => "mx-4",
            'append-icon' => "mdi-magnify"
        ]);
        $attrs[':search'] = $searchModel;
        $table->addChild($searchArea);
        $table->setAttributes($attrs);

        return $table;
    }
    /**
     * Creates a v-autocomplete element that represents a select.
     * @param string $items The name of the model that represents the 
     * items to select from.
     * 
     * @param string $label A label for the input element.
     * 
     * @param array $extraAttrs An optional array of extra attributes for 
     * the select.
     * 
     * @return HTMLNode
     */
    public function createSelect($items, $label, array $extraAttrs) {
        $select = new HTMLNode('v-autocomplete');

        if ($items !== null) {
            $select->setAttribute(':items', $items);
        }
        $select->setAttribute('label', $label);
        $select->setAttributes($extraAttrs);

        return $select;
    }
    public function isDark() {
        return $this->isDark;
    }
    /**
     * Creates a basic date picker input element.
     * 
     * @param string $menuModel The name of the model which is used to 
     * represents the menu of the input.
     * 
     * @param array $attrs An associative array that holds extra attributes 
     * for the date picker input.
     * 
     * @return HTMLNode
     */
    public function datePicker($menuModel = 'menu', $attrs = []) {
        $dateModel = isset($attrs['v-model']) ? $attrs['v-model'] : 'date';
        
        $node = new HTMLNode('v-menu', [
            'ref' => "$menuModel",
            'v-model' => "$menuModel",
            ':close-on-content-click' => "false",
            'transition' => "scale-transition",
            'offset-y',
            'min-width' => "290px",
        ]);
        $attrs[] = 'no-title';
        $attrs[] = 'scrollable';
        $attrs['color'] = 'green lighten-1';
        
        $attrs['v-model'] = $dateModel;
        
        $label = isset($attrs['label']) ? $attrs['label'] : 'Select a date.';
        $disabled = in_array('disabled', $attrs) ? 'disabled' : '';
        $node->addChild('template ', [
            'v-slot:activator' => "{ on, attrs }"
        ], false)->addChild('v-text-field', [
            'v-model' => "$dateModel",
            'label' => "$label",
            'prepend-icon' => "mdi-calendar",
            'readonly',
            'clearable',
            'v-bind' => "attrs",
            'v-on' => "on",
            $disabled
        ]);
        if (isset($attrs['@input'])) {
            $node->getLastChild()->getLastChild()->setAttribute('@input', $attrs['@input']);
            $attrs['@change'] = $attrs['@input'];
            unset($attrs['@input']);
        }
        if (isset($attrs[':loading'])) {
            $node->getLastChild()->getLastChild()->setAttribute(':loading', $attrs[':loading']);
            unset($attrs[':loading']);
        }
        $attrs['@input'] = "$menuModel = false";
        $node->addChild('v-date-picker', $attrs);
        return $node;
    }
    /**
     * Returns an object of type Json that contains all JSON attributes.
     * 
     * Initially, the object will contain all common attributes for all pages.
     * 
     * @return Json
     * 
     */
    public function getJson() {
        return $this->jsonData;
    }
    /**
     * Returns the object that holds the inline JavaScript code.
     * 
     * @return JsCode
     */
    public function getTopInlineJs() {
        return $this->topInlineJs;
    }
    private function _checkIsDark() {
        $darkArg = Request::getParam('dark');

        if ($darkArg !== null) {
            $darkArg = $darkArg == 't' ? true : false;
        } else {
            $darkArg = SessionsManager::get('dark');

            if ($darkArg === null) {
                $darkArg = true;
            }
        }
        $this->isDark = $darkArg;
        SessionsManager::set('dark', $darkArg);
        $this->addToJson([
            'dark' => $darkArg
        ]);
    }
    /**
     * Sets the JavaScript file which will be used to initialize vue.
     * 
     * @param string $jsFilePath A string that represents the path of the 
     * file such as 'assets/js/init-vue.js'.
     * 
     */
    public function setVueJs($jsFilePath) {
        $this->addBeforeRender(function (WebPage $page, $jsPath) {
            $page->removeChild('vue-script');
            $page->getDocument()->addChild('script', [
                'type' => 'text/javascript',
                'src' => $jsPath.'?jv='.WebFioriApp::getAppConfig()->getVersion(),
                'id' => 'vue-script'
            ]);
        }, [$jsFilePath]);
    }
}
