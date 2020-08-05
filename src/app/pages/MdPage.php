<?php
namespace webfiori\examples\views;

use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use Parsedown;

/**
 * Description of MdPage
 *
 * @author Ibrahim
 */
class MdPage {
    public function __construct($mdLink) {
        Page::theme('WebFiori V108');
        
        $curl = curl_init();

        
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $mdLink
        ]);
        $exeResult = curl_exec($curl);
        if($exeResult === false){
            echo 'False';
            die();
        } else {
            $parsedown = new Parsedown();
            $asTxt = $parsedown->text($exeResult);
            $node = HTMLNode::fromHTMLText($asTxt);
            $super = new HTMLNode();
            foreach ($node as $xNode) {
                if ($xNode->getNodeName() == 'h1') {
                    $title = $xNode->getChild(0) !== null ? $xNode->getChild(0)->getText() : 'Default';
                    Page::title($title);
                }
                $super->addChild($xNode);
            }
            Page::insert($super);
        }
    }
}
