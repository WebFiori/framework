<?php
namespace webfiori\theme;

use webfiori\framework\Theme;
use webfiori\framework\ui\WebPage;
use webfiori\ui\HTMLNode;
use webfiori\ui\HeadNode;
use webfiori\ui\Anchor;
use webfiori\apiParser\APITheme;
use webfiori\apiParser\FunctionDef;
use webfiori\apiParser\AttributeDef;
use webfiori\ui\UnorderedList;
use webfiori\ui\Paragraph;
use webfiori\apiParser\NameSpaceAPI;
use webfiori\ui\JsCode;
use webfiori\theme\NewFiori;


/**
 * The new WebFiori framework website theme.
 *
 * @author Ibrahim
 */
class NewFioriAPI extends NewFiori implements APITheme {
    /**
     * The class that the theme will use to create APIs description page.
     * 
     * @var ClassAPI 
     */
    private $class;
    /**
     * Sets the class that the theme will use to create APIs description page.
     * 
     * This function will also set the title of the page to the name of the 
     * given class.
     * 
     * @param ClassAPI $class
     */
    public function setClass($class) {
        if($class instanceof ClassAPI){
            $this->class = $class;
            $this->getPage()->setTitle($class->getAccessModifier().' '.$class->getName());
        }
    }
    /**
     * Returns the class that the theme will use to create APIs description page.
     * 
     * @return ClassAPI|NULL The class that the theme will use to create APIs description page. 
     * if no class is set, the function will return NULL.
     * 
     */
    public function getClass() {
        return $this->class;
    }
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0');
        $this->setLicenseName('MIT');
        $this->setDescription('The new WebFiori framework website theme.');
    }
    public function getAsideNode() {
        $page = $this->getPage();
        $right = $page->getWritingDir() == 'rtl' ? 'right' : '';
        $sideDrawer = new HTMLNode('v-navigation-drawer', [
            'v-model' => "drawer",
            'app', $right,
            'width' => '250px',
            'app', 'temporary',
        ]);
        $sideDrawer->addChild('v-divider');
        $itemsPanel = new HTMLNode('template');
        $sideDrawer->addChild($itemsPanel);
        $itemsPanel->addChild('v-expansion-panels')
        ->addChild(
                $this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/docs/webfiori'
                    ], 'API Reference', 'mdi-information-variant')));
        return $sideDrawer;
    }

    public function createAttributeDetailsBlock(AttributeDef $attr): HTMLNode {
        $block = new HTMLNode('v-card', [
            'id' => $attr->getName(),
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ])->addChild($attr->getDetailsNode());
        $vCardTxt = $block->addChild('v-card-text');
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ])->addChild($attr->getDescriptionAsHTMLNode());
        
        return $block;
    }

    public function createAttributeSummaryBlock(AttributeDef $attr): HTMLNode {
        $block = new HTMLNode('v-card', [
            'height' => '85',
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ])->addChild($attr->getSummaryNode());
        $vCardTxt = $block->addChild('v-card-text');
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ])->addChild($attr->getSummaryAsHTMLNode());
        
        return $block;
    }

    public function createClassDescriptionNode($accessMod = '', $className = '', $ns= '', $description= ''): HTMLNode {
        $block = new HTMLNode('v-row');
        $block->addChild('v-col', [
            'cols' => 12
        ])->addChild('p')
        ->addChild('b', [
            'class' => 'mono'
        ])->text('namespace ')->addChild('a', [
            'href' => $this->getBaseURL()."/". str_replace('\\', '/', $ns)
        ])->text($ns);
        $block->addChild('v-col', [
            'cols' => 12
        ])->addChild('h1')->text($accessMod.' '.$className);
        $block->addChild('v-col', [
            'cols' => 12,
            'v-html' => "'". str_replace("'", "\'", $description)."'"
        ]);
        return $block;
    }

    public function createMethodDetailsBlock(FunctionDef $func): HTMLNode {
        $block = new HTMLNode('v-card', [
            'id' => $func->getName(),
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ])->addChild($func->getDetailsSignatorNode($this->getPage()));
        $vCardTxt = $block->addChild('v-card-text');
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ])->addChild($func->getDescriptionAsHTMLNode());
        
        if (count($func->getParameters()) != 0) {
            $paramsCol = $row->addChild('v-col', [
                'cols' => 12
            ]);
            $paramsCol->addChild('p', [
                'style' => [
                    'font-weight' => 'bold'
                ]
            ])->text('Parameters:');
            $ul = $paramsCol->addChild(new UnorderedList());
            $count = count($func->getParameters());
            for($x = 0 ; $x < $count ; $x++){
                $param = $func->getParameters()[$x];
                $optionalTxt = '';
                if($param->isOptional() === true){
                    $optionalTxt = '[Optional] ';
                }
                $li = new \webfiori\ui\ListItem();
                $ul->addChild($li);
                $param instanceof \webfiori\apiParser\MethodParameter;
                $li->addChild($param->getParametersNode($this->getPage()))
                        ->text(' '.$param->getName().' ')
                        ->text($optionalTxt)
                        ->addChild($param->getDescriptionAsHTMLNode(), [
                            'style' => [
                                'font-family' => 'roboto'
                            ],
                            
                        ]);
            }
        }
        $return = $func->getMethodReturnTypesAsHTMLNode();
        
        if($return !== null){
            $retCol = $row->addChild('v-col', [
                'cols' => 12
            ]);
            $retCol->addChild('b')->text('Returns: ')
            ->getParent()
            ->addChild($return, [
                'class' => 'mono'
            ]);
            $retCol->addChild($func->getMethodReturnDescriptionAsHTMLNode());
        }
        return $block;
    }

    public function createMethodSummaryBlock(FunctionDef $func): HTMLNode {
        $block = new HTMLNode('v-card', [
            'height' => '85',
            'hover', 'outlined', 
        ]);
        $block->addChild('v-card-title', [
            'style' => [
                'font-family' => 'monospace',
                'font-weight' => 'bold'
            ]
        ])->addChild($func->getSummarySignatorNode($this->getPage()));
        $vCardTxt = $block->addChild('v-card-text');
        $row = $vCardTxt->addChild('v-row');
        $row->addChild('v-col', [
            'cols' => 12,
        ])->addChild($func->getSummaryAsHTMLNode());
        return $block;
    }

    public function createNSAside($links) {
        $drawer = new HTMLNode('v-navigation-drawer', [
            //'v-model' => "drawer_md",
            'fixed', 'app', 'width' => '300px',
            ':mini-variant.sync'=>"mini"
        ]);
        $list = $drawer->addChild('v-list');
        
        $list->addChild('v-list-item')
        ->addChild('v-list-item-icon')
                 ->addChild('v-icon')
                 ->text('mdi-send-circle')
                 ->getParent()->getParent()
        ->addChild('v-list-item-title')->text('All Classes')
        ->getParent()->addChild('v-btn', [
            'icon', '@click.stop' => 'mini = !mini'
        ])->addChild('v-icon')->text('mdi-chevron-left');
        $drawer->addChild('v-divider');
        
        
        $classes = $this->getPage()->getClasses();
        foreach ($classes as $ns => $classesInNs) {
            $subList = $list->addChild('v-list-group', [
                'dense',
                ':value' => 'true',
                'sub-group',
                'no-action'
            ]);
            $subList->addChild('template', [
                'v-slot:activator',
            ])
            ->addChild('v-list-item-content')
            ->addChild('v-list-item-title')
            ->addChild('a', [
                'href' => $this->getBaseURL(). str_replace('\\', '/', $ns)
            ])->text($ns);
            
            foreach ($classesInNs as $className) {
                $subList->addChild('v-list-item', [
                    'dense',
                    'href' => $this->getBaseURL().$this->getPage()->getLink($className)->getAttribute('href')
                ])
                ->addChild('v-list-item-title')
                ->text($className);
            }
        }
        return $drawer;
    }

    public function createNamespaceContentBlock(NameSpaceAPI $nsObj): HTMLNode {
        $block = new HTMLNode('v-row');
        $block->addChild('v-col', [
            'cols' => 12
        ])->addChild('h1')->text('Namespace '.$nsObj->getName());
        $nsArr = $nsObj->getSubNamespaces();
        if(count($nsArr) !=0 ){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ]);
            $nsNode->addChild('v-card-title')->text('All Sub-namespaces:');
            $list = $nsNode->addChild('v-card-text')
                    ->addChild('v-list', ['dense']);
            foreach ($nsArr as $nsName){
                $list->addChild('v-list-item', [
                    'href' => $this->getBaseURL().'/'.str_replace('\\', '/', $nsName)
                ])
                ->addChild('v-list-item-content')
                ->addChild('v-list-item-title')->text($nsName);
            }
        }
        $interfaces = $nsObj->getInterfaces();
        if(count($interfaces) != 0){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ]);
            $nsNode->addChild('v-card-title')->text('All Interfaces:');
            $list = $nsNode->addChild('v-card-text')->addChild('v-list', ['dense']);
            foreach ($interfaces as $interfaceName => $infoArr){
                $link = $this->getBaseURL().'/'.str_replace('\\', '/', trim($nsObj->getName(),'\\')).'/'.$interfaceName;
                
                $list->addChild('v-list-item', [
                    'href' => $link
                ])
                ->addChild('v-list-item-content')
                ->addChild('v-list-item-title')->text($interfaceName)
                ->getParent()->addChild('v-list-item-subtitle')
                ->text($infoArr['summary']);
            }
        }
        $classes = $nsObj->getClasses();
        if(count($classes) != 0){
            $nsNode = $block->addChild('v-card', [
                'hover', 'outlined', 
            ]);
            $nsNode->addChild('v-card-title')->text('All Classes:');
            $list = $nsNode->addChild('v-card-text')->addChild('v-list', ['dense']);
            foreach ($classes as $className => $infoArr){
                $link = $this->getBaseURL().'/'.str_replace('\\', '/', trim($nsObj->getName(),'\\')).'/'.$className;
                
                $list->addChild('v-list-item', [
                    'href' => $link
                ])
                ->addChild('v-list-item-content')
                ->addChild('v-list-item-title')->text($className)
                ->getParent()->addChild('v-list-item-subtitle')
                ->text($infoArr['summary']);
            }
        }
        return $block;
    }

    public function createAttrsDetailsBlock($classAttrsArr) {
        if (count($classAttrsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ])->addChild('h3')->text('Class Attributes Details');
            $col = $node->addChild('v-col');
            foreach ($classAttrsArr as $attrObj) {
                $col->addChild($this->createAttributeDetailsBlock($attrObj));
            }
            return $node;
        }
    }

    public function createAttrsSummaryBlock($classAttrsArr) {
        if (count($classAttrsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ])->addChild('h3')->text('Class Attributes Summary');
            $col = $node->addChild('v-col');
            foreach ($classAttrsArr as $attrObj) {
                $col->addChild($this->createAttributeSummaryBlock($attrObj));
            }
            return $node;
        }
    }

    public function createMethodsDetailsBlock($classMethodsArr) {
        if (count($classMethodsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ])->addChild('h3')->text('Class Methods Details');
            $col = $node->addChild('v-col');
            foreach ($classMethodsArr as $methObj) {
                $col->addChild($this->createMethodDetailsBlock($methObj));
            }
            return $node;
        }
    }

    public function createMethodsSummaryBlock($classMethodsArr) {
        if (count($classMethodsArr) != 0) {
            $node = new HTMLNode('v-row');
            $node->addChild('v-col', [
                'cols' => 12
            ])->addChild('h3')->text('Class Methods Summary');
            $col = $node->addChild('v-col');
            foreach ($classMethodsArr as $methObj) {
                $col->addChild($this->createMethodSummaryBlock($methObj));
            }
            return $node;
        }
    }

}
return __NAMESPACE__;