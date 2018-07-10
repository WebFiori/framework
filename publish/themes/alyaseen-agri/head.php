<?php

/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/**
 * Modify the content of this function to add custom head tags.
 * @return HeadNode Head tag as <b>HeadNode</b> object.
 */
function getHeadNode(){
    $page = Page::get();
    $page->setLang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    $page->usingLanguage();
    extendLanguage();
    $page->setWebsiteName($page->getLanguage()->get('general/website-name'));
    $page->setTitle('Hello');
    $headTag = new HeadNode();
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    $headTag->addLink('icon', $page->getThemeImagesDir().'/favicon.png');
    $headTag->addCSS($page->getThemeCSSDir().'/Grid.css');
    $headTag->addCSS($page->getThemeCSSDir().'/colors.css');
    $headTag->addCSS($page->getThemeCSSDir().'/theme-specific.css');
    $headTag->addMeta('robots', 'index, follow');
    return $headTag;
}
/**
 * 
 * @param Language $language
 */
function extendLanguage(){
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/main-nav');
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/home/headers');
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/home/sections-contents');
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/contact-us');
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/branches');
    Page::get()->getLanguage()->createDirectory('alyaseen-agri/suppliers');
    if(Page::get()->getLanguage()->getCode() == 'AR'){
        Page::get()->getLanguage()->set('general', 'website-name', 'شركة الياسين الزراعية');
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home', array(
            'title'=>'الرئيسية',
            'description'=>'الصفحة الرئيسية لشركة الياسين الزراعية.'
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/main-nav', array(
            'our-products'=>'منتجاتنا',
            'branches'=>'الفروع',
            'about-management'=>'حول الإدارة',
            'contact-us'=>'الإتصال بنا',
            'suppliers'=>'الموردون'
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home/headers', array(
            'h1'=>'التأسيس',
            'h2'=>'نشاطنا',
            'h3'=>'مدى عملنا',
            'h4'=>'إستراتيجيتنا المستقبلية',
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home/sections-contents', array(
            'p1'=>'تأسست شركتنا عام 1980 م بفرع واحد في الأحساء و تطورت أعمالها حتى وصل عدد فروعها إلى احدى عشرة فروع منتشرة على مستوى السوق الزراعي في المملكة',
            'p2'=>'من نشاطاتنا تسويق المدخلات الزراعية مثل البذور و الأسمدة و الكيماويات الزراعية و مواد زراعية لمزارعي الخضروات في المملكة',
            'p3'=>'شركتنا رائدة في السوق الزراعية السعودية بتخصصها في خدمة مزارعي الخضروات بشكل خاص والسوق الزراعية بشكل عام. لدينا توليفة من المدخلات الزراعية المتميزة والتي تفي بهذه الأغراض',
            'p4'=>'نحن شركة ذات دوافع تسويقية. نبذل جهود كبيرة لدراسة السوق الزراعية السعودية وتحديد مستوياتها كمقدمة لتلبية طلبات عملائنا بما يرضيهم من مدخلات زراعية مميزة الجودة والسعر وبذا نحقق حصة تسويقية مرضية من حجم السوق العالمي في جدتها وجودتها مما يعطي عملائنا من الشركات الزراعية الكبرى والأفراد من المزارعين مردودات مالية مجزية',
        ));
    }
    else{
        Page::get()->getLanguage()->set('general', 'website-name', 'Alyaseen Agricultar Company');
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home', array(
            'title'=>'Home',
            'description'=>'Home page of alyaseen agri co.'
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/main-nav', array(
            'our-products'=>'Products',
            'branches'=>'Branches',
            'about-management'=>'About Management',
            'contact-us'=>'Contact Us',
            'suppliers'=>'Suppliers'
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home/headers', array(
            'h1'=>'Our Vision',
            'h2'=>'Our Mission',
            'h3'=>'Our Goals',
            'h4'=>'Our Business',
        ));
        Page::get()->getLanguage()->setMultiple('alyaseen-agri/home/sections-contents', array(
            'p1'=>'Achieve Leadership and Excellence in the Saudi Agricultural Market.',
            'p2'=>'Provide efficient and Cost-Effective solutions to our customers.',
            'p3'=>'Continuous quest for excellence in performance. Transfer ideal technologies to the Saudi Agricultural Market. Attain adequate financial returns to ourselves and to our ',
            'p4'=>'We service the Saudi vegetable growers in particular and also the farming community in general through our Eleven branches by supplying outstanding range of vegetable seeds, excellent specialty fertilizers and efficient agrochemicals, and an assortment of other agricultural inputs.
We have won -over the years- a prominent position in our market and have a wide spectrum of customers ranging from the individual vegetable growers to the large Saudi shareholding Agricultural companies. We take great pride in satisfying both ends of this range.',
        ));
    }
}


