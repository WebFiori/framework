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
namespace webfiori\examples\views;
use webfiori\entity\Page;
use phpStructs\html\PNode;
use phpStructs\html\HTMLNode;
use phpStructs\html\UnorderedList;
class ExamplePage{
    public function __construct() {
        //load UI template components (JS, CSS and others)
        //it is optional. to use a theme but recomended
        Page::theme('Vuetify Theme');
        //Page::theme('WebFiori V108');
        //Page::theme('Bootstrap Theme');
        //Page::theme('Greeny By Ibrahim Ali');
        //Page::theme('Template Theme');
        //Page::theme('WebFiori Theme');
        
        //sets the title of the page
        $lang = Page::lang();
        if($lang == 'AR'){
            Page::title('مثال على صفحة');
            //adds a paragraph to the body of the page.
            $p = new PNode();
            $p->addText('أهلا و سهلا من إطار "ويب فيوري"!');
            Page::insert($p);
        }
        else{
            Page::title('Example Page');
            //adds a paragraph to the body of the page.
            $p = new PNode();
            $p->addText('Hello from "WebFiori Framework"!');
            Page::insert($p);
        }
        
        $sec = new HTMLNode('section');
        Page::insert($sec);
        $secH = new HTMLNode('h1');
        if(Page::lang() == 'AR'){
            $secH->addTextNode('ما هو إطار برمجة ويب فيوري؟');
        }
        else{
            $secH->addTextNode('What is WebFiori Framework? ');
        }
        $sec->addChild($secH);
        $p2 = new PNode();
        if(Page::lang() == 'AR'){
             $p2->addText('إطار برمجة ويب فيوري هو اطار برمجة يستخدم في بناء '
                     . 'تطبيقات الشبكة بإستخدام لغة برمجة بي إتش بي .'
                     . 'الإطار بالكامل كائني التوجه و يسمح بإستخدام هيكلة ال MVC '
                     . 'لكنه لا يجبر المطور على استخدامها. الإطار يأتي مع العديد من الميزات '
                     . 'اللتي تُساعد مطور الشبكة على بناء المواقع الإلكترونية او '
                     . 'طبيقات الشبكة بشكل سريع.');
        }
        else{
             $p2->addText('WebFiori Framework is a web framework which is built '
                . 'using PHP language. The framework is fully object oriented '
                . '(OOP). It allows the use of the famous model-view-controller '
                . '(MVC) model but it does not force it. The framework comes '
                . 'with many features which can help in making your website '
                . 'or web application up and running in no time.');
        }
        $sec->addChild($p2);
        
        $sec = new HTMLNode('section');
        Page::insert($sec);
        $secH = new HTMLNode('h1');
        if(Page::lang() == 'AR'){
            $secH->addTextNode('الميزات الأساسية');
        }
        else{
            $secH->addTextNode('Key Features');
        }
        $sec->addChild($secH);
        $ul = new UnorderedList();
        $ul->addListItems([
            'Theming and the ability to create multiple UIs for the same web page using any CSS or JavaScript framework.',
            'Support for routing that makes the ability of creating search-engine-friendly links an easy task.',
            'Creation of web APIs that supports JSON, data filtering and validation.',
            'Basic support for MySQL schema and query building.',
            'Lightweight. The total size of framework core files is less than 3 megabytes.',
            'Access management by assigning system user a set of privileges.',
            'The ability to create and manage multiple sessions at once.',
            'Support for creating and sending nice-looking emails in a simple way by using SMTP protocol.',
            'Autoloading of user defined classes.',
            'The ability to create automatic tasks and let them run in specific time using CRON.',
            'Basic support for logging.',
            'Well-defined file upload and file handling sub-system.',
            'Building and manipulating the DOM of a web page using PHP language.',
            'Basic support for running the framework throgh CLI.'
        ]);
        $sec->addChild($ul);
        Page::render();
    }
}
return __NAMESPACE__;