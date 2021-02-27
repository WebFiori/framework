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

use ibrahim\themes\IbrahimTheme;
use webfiori\framework\Page;
use webfiori\theme\vutifyTheme\VuetifyTheme;
use webfiori\theme\WebFioriV108;
use webfiori\framework\ui\WebPage;
class ExamplePage extends WebPage {
    public function __construct() {
        parent::__construct();
        //load UI components (JS, CSS, ...)
        //Experement with all themes.
        //it is optional. to use a theme but recomended
        //$this->setTheme(VuetifyTheme::class);
        //$this->setTheme(IbrahimTheme::class);
        //$this->setTheme('Vuetify Template');
        //$this->setTheme('Bootstrap Theme');
        //$this->setTheme('Greeny By Ibrahim Ali');
        //Page::theme('Template Theme');
        $this->setTheme('WebFiori Theme');
        //$this->setTheme(WebFioriV108::class);
        //Load language. Used to make the page i18n compatable.

        $this->setTitle($this->get('pages/sample-page/title'));
        $this->setDescription($this->get('pages/sample-page/description'));

        $mainContentArea = $this->getDocument()->getChildByID('main-content-area');

        //Load HTML component and insert it in the body of the page.
        $templateDir = ROOT_DIR.DS.'app'.DS.'pages'.DS.'example-template.html';
        $mainContentArea->component($templateDir, $this->get('pages/sample-page'));
    }
}
