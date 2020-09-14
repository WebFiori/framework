<?php
namespace webfiori\theme;

use webfiori\ui\Anchor;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use webfiori\ui\Input;
use webfiori\ui\Label;
use webfiori\ui\ListItem;
use webfiori\ui\PNode;
use webfiori\ui\UnorderedList;
use webfiori\conf\Config;
use webfiori\conf\SiteConfig;
use webfiori\entity\Page;
use webfiori\entity\Theme;
use webfiori\logic\WebsiteController;
use webfiori\WebFiori;

class WebFioriTheme extends Theme {
    public function __construct() {
        parent::__construct();
        $this->setAuthor('Ibrahim Ali');
        $this->setName('WebFiori Theme');
        $this->setUrl('https://ibrahim-2017.blogspot.com/');
        $this->setLicenseName('MIT License');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
        $this->setVersion('1.0.1');
        $this->setDescription('The main theme for WebFiori Framework.');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->addComponents([
            'LangExt.php'
        ]);
        $this->setBeforeLoaded(function(){
            LangExt::extLang();
        });
        $this->setAfterLoaded(function()
        {
            Page::document()->getChildByID('main-content-area')->setClassName('wf-'.Page::dir().'-col-10');
            Page::document()->getChildByID('side-content-area')->setClassName('wf-'.Page::dir().'-col-2');
            Page::document()->getChildByID('page-body')->setClassName('wf-row');
            Page::document()->getChildByID('page-header')->setClassName('wf-row-np');
            Page::document()->getChildByID('page-footer')->setClassName('wf-row');
            Page::siteName(WebFiori::getSiteConfig()->getWebsiteNames()[Page::lang()]);
            
        });
    }
    /**
     * 
     * @param array $options An associative array of options. Available 
     * options are:
     * <ul>
     * <li>type: The type of the node that will be created. Supported 
     * types are: 
     * <ul>
     * <li>"div" (default).</li>
     * <li>"wf-row". This type has the following options: 
     * <ul>
     * <li>"with-padding", a boolean. If set to true, the row will have padding. Default is true.</li>
     * <li>"with-margin", a boolean. If set to true, the row will have margins. Default is true.</li>
     * </ul>
     * </li>
     * <li>"wf-col". This type has the following options: 
     * <ul>
     * <li>"size". Size of the column. A number from 1 up to 12. Default is 12.</li>
     * <li>"with-padding", a boolean. If set to true, the column will have padding. Default is true.</li>
     * <li>"with-margin", a boolean. If set to true, the column will have margins. Default is true.</li>
     * </ul>
     * </li>
     * <li>"status-label". A row with a label inside it which has a paragraph 
     * with ID = "status-label"</li>
     * <li>"input-element". A row which represents input element alongside its components. 
     * This type has the following options:
     * <ul>
     * <li>"input-type". The type of input element. Default is "text"</li>
     * <li>"label". The label which will be used for the input element. 
     * If not provided, the value 'Input_label' is used.</li>
     * <li>"input-id". The ID of input element. If not provided, the 
     * value 'input-el' is used.</li>
     * <li>"placeholder" A text to show as a placeholder.</li>
     * <li>"on-input". A String that represents JavaScript code which 
     * will be executed when input element value changes.</li>
     * <li>
     * "name". A string that is used when input type is "radio". Its the value 
     * of the attribute "name" of the radio button.
     * </li>
     * <li>"select-data". An array of sub-associative arrays that has an options which are 
     * used if input element type is "select". Each sub array can have the following indices:
     * <ul>
     * <li>"label". A label to show for the select option.</li>
     * <li>"value". The value of the attribute "value" of the select.</li>
     * <li>"selected". A boolean. If set to true, the attribute "selected" will be set for the option.</li>
     * <li>"disabled". A boolean. If set to true, the attribute "disabled" will be set for the option.</li>
     * </ul>
     * </ul>
     * </li>
     * </ul>
     * </li>
     * </ul>
     * @return HTMLNode
     */
    public function createHTMLNode($options = []) {
        $nodeType = isset($options['type']) ? $options['type'] : 'div';
        $withPadding = isset($options['with-padding']) ? $options['with-padding'] === true : true;
        $withMargin = isset($options['with-margin']) ? $options['with-margin'] === true : true;

        if ($nodeType == 'div') {
            $node = new HTMLNode();

            return $node;
        } else {
            if ($nodeType == 'section') {
                $node = new HTMLNode('section');

                if (isset($options['h-level']) && $options['h-level'] > 0 && $options['h-level'] < 7) {
                    $h = new HTMLNode('h'.$options['h-level']);
                } else {
                    $h = new HTMLNode('h1');
                }
                $h->addTextNode($options['title']);
                $node->addChild($h);
            } else {
                if ($nodeType == 'wf-row') {
                    $wp = $withPadding === true ? '' : '-np';
                    $wm = $withMargin === true ? '' : '-nm';
                    $node = new HTMLNode();
                    $node->setClassName('wf-row'.$wm.$wp);

                    return $node;
                } else {
                    if ($nodeType == 'wf-col') {
                        $colSize = isset($options['size']) ? $options['size'] : 12;

                        if ($colSize > 12 || $colSize < 1) {
                            $colSize = 12;
                        }
                        $wp = $withPadding === true ? '' : '-np';
                        $wm = $withMargin === true ? '' : '-nm';
                        $node = new HTMLNode();
                        $node->setClassName('wf-'.Page::get()->getWritingDir().'-col-'.$colSize.$wm.$wp);

                        return $node;
                    } else if ($nodeType == 'page-title') {
                        $titleRow = $this->createHTMLNode([
                            'type' => 'wf-row'
                        ]);
                        $titleRow->setID('page-title');
                        $title = isset($options['title']) ? $options['title'] : Page::title();
                        $h1 = new HTMLNode('h2');
                        $h1->addTextNode($title);
                        $h1->setClassName('wf-'.Page::dir().'-col-10-nm-np');
                        $titleRow->addChild($h1);

                        return $titleRow;
                    } else if ($nodeType == 'status-label') {
                        $statusContainer = $this->createHTMLNode(['type' => 'wf-row']);
                        $statusContainer->setClassName($statusContainer->getAttributeValue('class').' status-label-container');
                        $statusLabel = new PNode();
                        $statusLabel->setID('status-label');
                        $statusContainer->addChild($statusLabel);

                        return $statusContainer;
                    } else if ($nodeType == 'input-element') {
                        $row = $this->createHTMLNode(['type' => 'wf-row']);
                        $label = isset($options['label']) ? $options['label'] : 'Input_label';
                        $labelNode = new Label($label);
                        $inputId = isset($options['input-id']) ? $options['input-id'] : 'input-el';
                        $labelNode->setAttribute('for', $inputId);
                        $inputType = isset($options['input-type']) ? $options['input-type'] : 'text';

                        if ($inputType == 'select') {
                            $row->addChild($labelNode);
                            $inputEl = new HTMLNode('select');
                            $inputEl->setID($inputId);

                            if (isset($options['select-data'])) {
                                foreach ($options['select-data'] as $data) {
                                    $label = isset($data['label']) ? $data['label'] : 'Lbl';
                                    $val = isset($data['value']) ? $data['value']:null;
                                    $isDisabled = isset($data['disabled']) ? $data['disabled'] === true : false;

                                    if ($val !== null) {
                                        $o = new HTMLNode('option');
                                        $o->addTextNode($label);
                                        $o->setAttribute('value', $val);

                                        if (isset($data['selected']) && $data['selected'] === true) {
                                            $o->setAttribute('selected', '');
                                        }

                                        if ($isDisabled) {
                                            $o->setAttribute('disabled', '');
                                        }
                                        $inputEl->addChild($o);
                                    }
                                }
                            }
                            $onInput = isset($options['on-input']) ? $options['on-input'] : "console.log(this.id+' has changed value.');";
                            $inputEl->setAttribute('onchange', $onInput);
                        } else {
                            $inputEl = new Input($inputType);
                            $inputEl->setID($inputId);
                            $onInput = isset($options['on-input']) ? $options['on-input'] : "console.log(this.id+' has changed value.');";

                            if ($inputType == 'submit') {
                                $inputEl->setAttribute('onclick', $onInput);
                                $inputEl->setAttribute('value', $label);
                            } else if ($inputType == 'checkbox' || $inputType == 'radio') {
                                $labelNode->setStyle([
                                    'display' => 'inline-block'
                                ]);
                                $row->addChild($inputEl);
                                $row->addChild($labelNode);

                                if ($inputType == 'radio') {
                                    $name = isset($options['name']) ? $options['name'] : 'radio-group';
                                    $inputEl->setName($name);
                                }

                                return $row;
                            } else {
                                $row->addChild($labelNode);
                                $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
                                $inputEl->setAttribute('placeholder', $placeholder);
                                $inputEl->setAttribute('oninput', $onInput);
                            }
                        }
                        $row->addChild($inputEl);

                        return $row;
                    }
                }
            }
        }
    }
    public function getAsideNode() {
        $menu = new HTMLNode('div');
        
        return $menu;
    }

    public function getFooterNode() {
        $node = HTMLNode::loadComponent($this->getDirecotry().'footer.html', [
            'version' => Config::getVersion(),
            'version_type' => Config::getVersionType(),
            'writing_dir' => Page::dir(),
            'contact_phone' => '013 xxx xxxx',
            'copyright' => 'All Rights Reserved',
            'contact_mail' => 'hello@example.com'
        ]);
        
        return $node;
    }

    public function getHeadNode() {
        $headTag = new HeadNode();
        $headTag->setBase(SiteConfig::getBaseURL());
        $headTag->addLink('icon', 'favicon.png');
        $headTag->addMeta('robots', 'index, follow');

        return $headTag;
    }

    public function getHeadrNode() {
        $headerSec = HTMLNode::loadComponent($this->getDirecotry().'header.html', [
            'menu-labels' => Page::translation()->get('menus/main-menu'),
            'home_link' => WebFiori::getSiteConfig()->getBaseURL(),
            'dir' => Page::dir(),
            'site_name' => WebFiori::getSiteConfig()->getWebsiteNames()[Page::lang()],
            'menu-links' => [
                'm_1_link' => '#',
                'm_2_link' => '#',
                'm_3_link' => '#'
            ],
        ]);
        
        return $headerSec;
    }
}

return __NAMESPACE__;
