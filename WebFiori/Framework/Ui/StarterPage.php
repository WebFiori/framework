<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\ui;

/**
 * A page which is shown to the framework users when the developer has not
 * configured any routes.
 *
 * @author Ibrahim
 *
 * @version 1.0
 *
 * @since 2.3.0
 */
class StarterPage extends WebPage {
    public function __construct() {
        parent::__construct();
        $this->initHead();
        $this->initAppScript();
        $this->getChildByID('page-body')->setNodeName('v-app');
        $this->getDocument()->getDocumentRoot()->setStyle([
            'background-color' => '#e0f2b4'
        ]);
        $this->getChildByID(self::MAIN_ELEMENTS[2])->setStyle([
            'background' => 'rgb(213,238,153)',
            'background' => 'radial-gradient(circle, rgba(213,238,153,0.5550420851934523) 26%, rgba(4,101,37,0.45700286950717783) 68%)'
        ]);
        $this->setTitle('Welcome to WebFiori');
        $div = $this->insert('div');
        $div->addChild('img', [
            'src' => 'https://webfiori.com/assets/images/WFLogo512.png',
            'style' => 'width:250px;height:250px;border-radius:250px;background-color:black'
        ]);
        $div->setStyle([
            'text-align' => 'center'
        ]);
        $div->addChild('h2')->text('Welcome to WebFiori v'.WF_VERSION);

        $row = $div->addChild('v-container')->addChild('v-row', ['justify' => 'center']);
        $row->addChild('v-col', [
            'cols' => 12, 'sm' => 12, 'md' => 6
        ])->addChild('v-text-field', [
            'value' => ROOT_PATH.DS.APP_DIR,
            'disabled',
            'label' => 'Your application is ready at'
        ]);

        $cardsRow = $row->addChild('v-col', [
            'cols' => 12,
        ])->addChild('v-row');
        $this->createCard('https://webfiori.com/learn',
            'mdi-book-open-variant',
            'Learn',
            'Documentation is always the first place where developers can find what they need.'
            .'The framework has good documentation base which is still in development and '
            .'content is added and revewed regularly. '
            .'Whether you are new to WebFiori framework or have some '
            .'experience with it, we recommend the '
            .'documentation as they will help in a way or another.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6, 'sm' => 12]));
        $this->createCard('https://webfiori.com/docs/webfiori',
            'mdi-book-check-outline',
            'API Reference',
            'This reference has all information about core framework classes that a developer '
            .'might need to have specific functionality. In addition to that, it describes all '
            .'uses of every public class attribute and method. It can be handy when developers starts '
            .'using advanced features of the framework.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6, 'sm' => 12]));
        $this->createCard('https://webfiori.com/contribute',
            'mdi-comment-plus-outline',
            'Support The Project',
            'Want to help in development of the framework or contribute? This place is for you. It holds '
            .'basic instructions on how you may help in supporting the framework in many ways.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6, 'sm' => 12]));
    }
    private function createCard($link, $icon, $cardTitle, $paragraph, \WebFiori\UI\HTMLNode $el) {
        $card = $el->addChild('v-card', [
            'hover',
            'height' => '220px',
            'style' => [
                'background' => 'rgba(255,255,255,.6)'
            ]
        ]);
        $card->addChild('v-card-title')->addChild('v-icon',[
            'style' => 'margin:10px'
        ])
            ->text($icon)
            ->getParent()->addChild('a', [
            'href' => $link
        ])->text($cardTitle);
        $card->addChild('v-card-text')->text($paragraph);
    }
    private function initAppScript() {
        $script = $this->getDocument()->addChild('script');
        $script->text(""
                ."new Vue({"
                ."    el:'#page-body',"
                ."    vuetify:new Vuetify({"
                ."        theme: {"
                ."            dark:false,"
                ."            themes:{"
                ."                dark:{},"
                ."                light:{}"
                ."            }"
                ."        }"
                ."    })"
                ."});"
                .""
                .""
                .""
                ."", false);
    }
    private function initHead() {
        $head = $this->getDocument()->getHeadNode();
        $head->addJs('https://unpkg.com/vue@2.x.x');
        $head->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $head->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css');
        $head->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
        $head->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
    }
}
