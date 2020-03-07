<?php
namespace webfiori\theme\vutifyTheme;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\theme;
use jsonx\JsonX;
use webfiori\entity\Page;
use phpStructs\html\JsCode;
/**
 * Description of VuetifyPage
 *
 * @author Ibrahim
 */
class VuetifyPage {
    public function __construct() {
        Page::setBeforeRender(function(){
            $vueJs = new JsCode();
            $vueJs->addCode('new Vue('
                    . '{'
                    . 'el: \'#page-header\''
                    . ''
                    . '};');
            $vueJs->addCode('new Vue('
                    . '{'
                    . 'el: \'#page-body\''
                    . ''
                    . '};');
            $vueJs->addCode('new Vue('
                    . '{'
                    . 'el: \'#page-footer\''
                    . ''
                    . '}');
            Page::document()->getBody()->addChild($vueJs);
        });
    }
    /**
     * 
     * @return JsonX
     */
    public function getVuetifyTranslation(){
        $retVal = new JsonX();
        $data = $this->getLabel('vuetify');
        foreach ($data as $key => $v){
            if(gettype($v) == 'array'){
                $retVal->addArray($key, $v,true);
            }
            else{
                $retVal->add($key, $v);
            }
        }
        return $retVal;
    }
}
