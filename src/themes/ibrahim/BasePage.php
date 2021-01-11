<?php
namespace ibrahim\themes;

use webfiori\framework\Page;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\json\Json;
use webfiori\ui\HTMLNode;
use webfiori\ui\JsCode;

/**
 * A base page that can be extended to create system pages.
 * 
 * Used to simplfy some of the common tasks.
 *
 * @author Ibrahim
 */
class BasePage {
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
        Page::theme(IbrahimTheme::class);
        Page::description($description);
        Page::insert(Page::theme()->createHTMLNode([
            'name' => 'heading',
            'title' => $pageTitle
        ]));
        Page::beforeRender(function ($vueScript)
        {
            if (strlen($vueScript) > 0) {
                Page::document()->addChild('script', [
                    'src' => $vueScript,
                    'type' => 'text/javascript'
                ]);
                Page::document()->removeChild('default-vue-init');
            }
        },[$vueScript]);
        Page::title($pageTitle);
        $this->jsonData = new Json([
            'snackbar' => new Json([
                'visible' => false,
                'color' => '',
                'text' => '',
            ]),
        ]);
        Page::beforeRender(function($thisPage)
        {
            $thisPage->addInlineJs('window.data = '.$thisPage->getJson().';');
            $node = new HTMLNode('v-snackbar');
            $node->setAttribute('v-model','snackbar.visible');
            $node->addTextNode('{{ snackbar.text }}');
            $node->setAttribute(':color', 'snackbar.color');
            $closeButton = new HTMLNode('v-btn');
            $closeButton->setAttribute('text');
            $closeTxt = Page::translation()->get('general/action/close');
            $closeButton->addTextNode($closeTxt);
            $closeButton->setAttribute('@click','snackbar.visible = false');
            $node->addChild($closeButton);
            Page::insert($node);
        },[$this]);
        $this->topInlineJs = new JsCode();

        Page::document()->getHeadNode()->addChild($this->topInlineJs);
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
            ':return-value.sync' => "$dateModel",
            'transition' => "scale-transition",
            'offset-y',
            'min-width' => "290px",
        ]);
        $attrs[] = 'no-title';
        $attrs[] = 'scrollable';

        $attrs['v-model'] = $dateModel;
        $label = isset($attrs['label']) ? $attrs['label'] : 'Select a date.';
        $node->addChild('template ', [
            'v-slot:activator' => "{ on, attrs }"
        ], false)->addChild('v-text-field', [
            'v-model' => "$dateModel",
            'label' => "$label",
            'prepend-icon' => "mdi-calendar",
            'readonly',
            'v-bind' => "attrs",
            'v-on' => "on"
        ]);
        $node->addChild('v-date-picker', $attrs, false)->addChild('v-spacer')
        ->addChild('v-btn', [
            'text',
            '@click' => "$menuModel = false",
        ], false)->text('Cancel')
        ->getParent()
        ->addChild('v-btn', [
            'text',
            '@click' => "\$refs.$menuModel.save($dateModel)"
        ], false)->text('Ok');

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
        SessionsManager::set('dark', $darkArg);
        $this->addToJson([
            'dark' => $darkArg
        ]);
    }
}
