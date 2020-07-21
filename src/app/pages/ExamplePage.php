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
use webfiori\WebFiori;

class ExamplePage {
    public function __construct() {
        //load UI components (JS, CSS and others)
        //it is optional. to use a theme but recomended
        Page::theme('Vuetify Theme');
        //Page::theme('Vuetify Template');
        //Page::theme('WebFiori V108');
        //Page::theme('Bootstrap Theme');
        //Page::theme('Greeny By Ibrahim Ali');
        //Page::theme('Template Theme');
        //Page::theme('WebFiori Theme');
        
        //Sets the language of the page. Must be set before loading translation.
        Page::lang(WebFiori::getWebsiteController()->getSessionLang());
        //Load language
        $translation = Page::translation();
        
        Page::title($translation->get('pages/sample-page/title'));
        Page::description($translation->get('pages/sample-page/description'));
        //adds a paragraph to the body of the page.
        
        $mainContentArea = Page::document()->getChildByID('main-content-area');
        $mainContentArea->section(Page::title())
                ->paragraph($translation->get('pages/sample-page/question'))
                ->paragraph($translation->get('pages/sample-page/framework-description'))
                ->label($translation->get('pages/sample-page/features'))
                ->ul($translation->get('pages/sample-page/features-list'));
        
        //Render the page and display the result
        Page::render();
    }
}

return __NAMESPACE__;
