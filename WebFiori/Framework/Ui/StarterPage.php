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
namespace WebFiori\Framework\Ui;

class StarterPage extends WebPage {
    public function __construct() {
        parent::__construct();

        $this->initHead();
        $this->initAppScript();

        // Vue mount must be a normal div
        $this->getChildByID('page-body')->setNodeName('div');

        $this->setTitle('Welcome to WebFiori');

        // === ORIGINAL BACKGROUND COLORS ===
        $this->getDocument()->getDocumentRoot()->setStyle([
            'background-color' => '#e0f2b4'
        ]);

        $this->getChildByID(self::MAIN_ELEMENTS[2])->setStyle([
            'background' => 'radial-gradient(circle, rgba(213,238,153,0.55) 26%, rgba(4,101,37,0.45) 68%)',
            'min-height' => '100vh'
        ]);

        // Vue root
        $root = $this->insert('div')->setID('starter-root');

        // CSS: keep palette, reduce inline styles, rely on Vuetify utility classes
        $style = $this->getDocument()->addChild('style');
        $style->text("
:root{
  --wf-green-900:#1b3a1b;
  --wf-green-700:#2e4e2e;
}

/* Keep expansion chevron visible on light surfaces */
#starter-root .v-expansion-panel-header__icon .v-icon {
  color: var(--wf-green-900) !important;
}

/* Page spacing */
#starter-root .wf-page {
  padding-bottom: 40px;
}

/* Hero wrapper */
#starter-root .wf-hero {
  background: rgba(255,255,255,.84);
  border: 1px solid rgba(27,58,27,.14);
  border-radius: 14px;
  padding: 22px 18px;
}

/* Typography helpers */
#starter-root .wf-title {
  color: var(--wf-green-900);
  margin: 14px 0 0;
  letter-spacing: .2px;
}
#starter-root .wf-subtitle {
  margin-top: 6px;
  color: var(--wf-green-700);
  font-size: 14px;
}
#starter-root .wf-section-label {
  color: var(--wf-green-700);
  font-size: 13px;
  font-weight: 600;
  letter-spacing: .2px;
  margin: 18px 0 6px;
}

/* Soft card background */
#starter-root .wf-soft-card {
  background: rgba(255,255,255,.82);
  border: 1px solid rgba(27,58,27,.14);
}

/* Code blocks */
#starter-root .wf-code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, \"Liberation Mono\", \"Courier New\", monospace;
  font-size: 12.5px;
  line-height: 1.45;
  background: rgba(255,255,255,.92);
  border: 1px solid rgba(27,58,27,.12);
  border-radius: 8px;
  padding: 12px;
  overflow: auto;
  margin-top: 8px;
  color: var(--wf-green-900);
}

/* Clickable cards */
#starter-root .wf-card-clickable {
  cursor: pointer;
  transition: transform .15s ease;
}
.transparent{
background: transparent !important
}
#starter-root .wf-card-clickable:hover {
  transform: translateY(-2px);
}
        ", false);

        // ---------- v-app ----------
        $app = $root->addChild('v-app', [
            'class' => 'transparent'
        ]);

        // Global snackbar for copy feedback
        $app->addChild('v-snackbar', [
            'v-model' => 'snackbar',
            ':color' => 'snackbarColor',
            'timeout' => 2200,
            'top',
        ])->text('{{ snackbarText }}');

        $main = $app->addChild('v-main');
        $container = $main->addChild('v-container', [
            'class' => 'wf-page py-8',
        ]);

        /**
         * HERO
         */
        $heroRow = $container->addChild('v-row', [
            'justify' => 'center'
        ]);

        $heroCol = $heroRow->addChild('v-col', [
            'cols' => 12,
            'md' => 8,
            'lg' => 7
        ]);

        $hero = $heroCol->addChild('div', [
            'class' => 'wf-hero text-center'
        ]);

        $hero->addChild('img', [
            'src' => 'https://WebFiori.com/assets/images/WFLogo512.png',
            'class' => 'mx-auto',
            'style' => 'width:200px;height:200px;border-radius:200px;background:black'
        ]);

        $titleWrap = $hero->addChild('div', ['class' => 'mt-2']);
        $titleWrap->addChild('h2', [
            'class' => 'wf-title'
        ])->text('Welcome to WebFiori');

        $titleWrap->addChild('div', [
            'class' => 'wf-subtitle'
        ])->text('Framework installed and ready. Start by creating a route.');

        $chipRow = $hero->addChild('div', ['class' => 'mt-3']);
        $chipRow->addChild('v-chip', [
            'small',
            'color' => 'green lighten-4',
            'text-color' => '#1b3a1b',
            'class' => 'mx-auto'
        ])->text('v'.WF_VERSION);

        // App path field (copy icon)
        $hero->addChild('div', ['class' => 'wf-section-label'])->text('Application path');

        $hero->addChild('v-text-field', [
            ':value' => 'appPath',
            'label' => 'Your application is ready at',
            'outlined',
            'readonly',
            'append-icon' => 'mdi-content-copy',
            '@click:append' => 'copyText(appPath)',
            'hint' => 'Click the copy icon to copy the path',
            'persistent-hint',
            'class' => 'mt-1'
        ]);

        /**
         * Next steps (collapsed with teaser)
         */
        $stepsRow = $container->addChild('v-row', [
            'justify' => 'center',
            'class' => 'mt-4'
        ]);

        $stepsCol = $stepsRow->addChild('v-col', [
            'cols' => 12,
            'md' => 8,
            'lg' => 7
        ]);

        $panels = $stepsCol->addChild('v-expansion-panels', [
            'accordion',
            'flat'
        ]);

        $panel = $panels->addChild('v-expansion-panel', [
            'class' => 'wf-soft-card'
        ]);

        $header = $panel->addChild('v-expansion-panel-header', [
            'class' => 'py-3'
        ]);

        $headerWrap = $header->addChild('div');

        $headerTitle = $headerWrap->addChild('div', [
            'class' => 'font-weight-bold d-flex align-center'
        ]);
        $headerTitle->addChild('v-icon', [
            'class' => 'mr-2',
            'color' => 'green darken-4'
        ])->text('mdi-map-marker-path');
        $headerTitle->addChild('span', [
            'style' => 'color:var(--wf-green-900);'
        ])->text('Next steps (recommended)');

        $headerWrap->addChild('div', [
            'class' => 'caption mt-1',
            'style' => 'color:var(--wf-green-700);'
        ])->text('Create a route → point it to a page/controller → test it in the browser.');

        $content = $panel->addChild('v-expansion-panel-content');

        $stepsText = $content->addChild('div', [
            'class' => 'pt-2',
            'style' => 'color:var(--wf-green-900);'
        ]);

        $stepsText->addChild('div', [
            'class' => 'body-2 mb-3',
            'style' => 'color:var(--wf-green-700);'
        ])->text('A simple, recommended flow to get your first page/API running:');

        $list = $stepsText->addChild('v-list', [
            'dense',
            'class' => 'transparent pa-0'
        ]);

        $this->addStep($list, '1', 'Create a route', 'Define the URL contract first (PATH + methods).');
        $this->addStep($list, '2', 'Point it to a page/controller', 'Use Router::page() for pages or Router::addRoute() for classes/controllers.');
        $this->addStep($list, '3', 'Test it in the browser', 'Confirm 200/404/405 behavior, then expand functionality.');

        $stepsText->addChild('div', [
            'class' => 'font-weight-bold mt-4',
            'style' => 'color:var(--wf-green-900);'
        ])->text('Examples');

        // Example 1
        $ex1 = $stepsText->addChild('div', [
            'class' => 'd-flex align-center justify-space-between mt-3'
        ]);
        $ex1->addChild('div', [
            'class' => 'body-2',
            'style' => 'color:var(--wf-green-700);'
        ])->text('Home page route (static file):');

        $ex1->addChild('v-btn', [
            'small',
            'outlined',
            'color' => 'green darken-2',
            '@click' => "copyFromRef('exHome')"
        ])->text('Copy');

        $stepsText->addChild('pre', [
            'class' => 'wf-code',
            'ref' => 'exHome'
        ])->text(
            "Router::page([\n".
                "   RouteOption::PATH => '/',\n".
                "   RouteOption::TO => 'Home.html'\n".
                "]);"
        );

        // Example 2
        $ex2 = $stepsText->addChild('div', [
            'class' => 'd-flex align-center justify-space-between mt-4'
        ]);
        $ex2->addChild('div', [
            'class' => 'body-2',
            'style' => 'color:var(--wf-green-700);'
        ])->text('Dynamic route parameters (PHP page):');

        $ex2->addChild('v-btn', [
            'small',
            'outlined',
            'color' => 'green darken-2',
            '@click' => "copyFromRef('exDynamic')"
        ])->text('Copy');

        $stepsText->addChild('pre', [
            'class' => 'wf-code',
            'ref' => 'exDynamic'
        ])->text(
            "Router::page([\n".
                "   RouteOption::PATH => 'products/{category}/{sub-category}',\n".
                "   RouteOption::TO => 'ViewProductsPage.php'\n".
                "]);"
        );

        // Example 3
        $ex3 = $stepsText->addChild('div', [
            'class' => 'd-flex align-center justify-space-between mt-4'
        ]);
        $ex3->addChild('div', [
            'class' => 'body-2',
            'style' => 'color:var(--wf-green-700);'
        ])->text('API endpoint (controller action):');

        $ex3->addChild('v-btn', [
            'small',
            'outlined',
            'color' => 'green darken-2',
            '@click' => "copyFromRef('exApi')"
        ])->text('Copy');

        $stepsText->addChild('pre', [
            'class' => 'wf-code',
            'ref' => 'exApi'
        ])->text(
            "Router::addRoute([\n".
                "   RouteOption::PATH => '/api/add-user',\n".
                "   RouteOption::TO => UsersController::class,\n".
                "   RouteOption::REQUEST_METHODS => ['post', 'put'],\n".
                "   RouteOption::ACTION => 'addUser'\n".
                "]);"
        );

        // Divider before cards
        $container->addChild('v-row', ['justify' => 'center'])
            ->addChild('v-col', ['cols' => 12, 'md' => 8, 'lg' => 7])
            ->addChild('v-divider', ['class' => 'my-8']);

        /**
         * Resource cards
         */
        $cardsRow = $container->addChild('v-row', [
            'class' => 'mt-2',
            'justify' => 'center'
        ]);

        $this->createCard(
            'https://WebFiori.com/learn',
            'mdi-book-open-variant',
            'Learn',
            'Guides, concepts, and examples to get productive fast.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6])
        );

        $this->createCard(
            'https://WebFiori.com/docs/WebFiori',
            'mdi-book-check-outline',
            'API Reference',
            'Explore framework classes, attributes, and method usage.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6])
        );

        $this->createCard(
            'https://WebFiori.com/contribute',
            'mdi-comment-plus-outline',
            'Support The Project',
            'Help improve WebFiori by contributing, reporting issues, or sponsoring.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6])
        );

        $this->createCard(
            'https://github.com/WebFiori/framework/discussions',
            'mdi-forum-outline',
            'Community Discussions',
            'Ask questions, share ideas, and discuss WebFiori with the community.',
            $cardsRow->addChild('v-col', ['cols' => 12, 'md' => 6])
        );

        // Footer
        $container->addChild('div', [
            'class' => 'text-center mt-8',
            'style' => 'color:var(--wf-green-700);font-size:13px'
        ])->text('WebFiori v'.WF_VERSION.' • MIT License');
    }

    private function addStep($list, $num, $title, $desc) {
        $item = $list->addChild('v-list-item', [
            'class' => 'px-0'
        ]);

        $item->addChild('v-list-item-icon')
            ->addChild('v-avatar', [
                'size' => 26,
                'color' => 'green darken-4'
            ])->addChild('span', [
                'class' => 'white--text',
                'style' => 'font-size:12px'
            ])->text($num);

        $content = $item->addChild('v-list-item-content');
        $content->addChild('v-list-item-title', [
            'class' => 'font-weight-bold',
            'style' => 'color:var(--wf-green-900);'
        ])->text($title);

        $content->addChild('v-list-item-subtitle', [
            'class' => 'body-2',
            'style' => 'color:var(--wf-green-700);'
        ])->text($desc);
    }

    private function createCard($link, $icon, $cardTitle, $paragraph, \WebFiori\Ui\HTMLNode $el) {
        $card = $el->addChild('v-card', [
            'hover',
            'link',
            'href' => $link,
            'target' => '_blank',
            'rel' => 'noopener',
            'height' => '220px',
            'class' => 'wf-card-clickable',
            'style' => [
                'background' => 'rgba(255,255,255,.8)'
            ]
        ]);

        $title = $card->addChild('v-card-title', [
            'class' => 'pb-1'
        ]);

        $title->addChild('v-icon', [
            'class' => 'mr-2',
            'color' => 'green darken-4'
        ])->text($icon);

        $title->addChild('span', [
            'class' => 'font-weight-medium',
            'style' => 'color:var(--wf-green-900);'
        ])->text($cardTitle);

        $card->addChild('v-card-text', [
            'class' => 'pt-2'
        ])->text($paragraph);
    }

    private function initAppScript() {
        $appPath = json_encode(ROOT_PATH.DS.APP_DIR, JSON_UNESCAPED_SLASHES);

        $this->getDocument()->addChild('script')->text("
new Vue({
  el: '#starter-root',
  vuetify: new Vuetify(),
  data: function () {
    return {
      appPath: {$appPath},
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success'
    };
  },
  methods: {
    copyFromRef: function (refName) {
      var el = this.\$refs[refName];
      if (!el) {
        this.snackbarColor = 'error';
        this.snackbarText = 'Nothing to copy.';
        this.snackbar = true;
        return;
      }
      this.copyText(el.innerText);
    },

    copyText: function (text) {
      var self = this;
      if (!text) {
        self.snackbarColor = 'error';
        self.snackbarText = 'Nothing to copy.';
        self.snackbar = true;
        return;
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () {
          self.snackbarColor = 'success';
          self.snackbarText = 'Copied!';
          self.snackbar = true;
        }).catch(function () {
          self.fallbackCopy(text);
        });
        return;
      }

      self.fallbackCopy(text);
    },

    fallbackCopy: function (text) {
      try {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);

        this.snackbarColor = 'success';
        this.snackbarText = 'Copied!';
        this.snackbar = true;
      } catch (e) {
        this.snackbarColor = 'error';
        this.snackbarText = 'Copy failed. Please copy manually.';
        this.snackbar = true;
      }
    }
  }
});
        ", false);
    }

    private function initHead() {
        $head = $this->getDocument()->getHeadNode();

        $head->addMeta('viewport', 'width=device-width, initial-scale=1');

        $head->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $head->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css');
        $head->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');

        $head->addJs('https://unpkg.com/vue@2.x.x');
        $head->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
    }
}
