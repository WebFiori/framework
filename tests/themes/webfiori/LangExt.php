<?php
namespace webfiori\theme;

use webfiori\framework\i18n\Language;
/**
 * Extending language file by adding more labels.
 *
 * @author Ibrahim
 */
class LangExt {
    public static function extLang(&$trans = null) {
        $trans->createDirectory('menus/main-menu');
        $langCode = $trans->getCode();

        if ($langCode == 'AR') {
            $trans->setMultiple('menus/main-menu', [
                'menu-item-1' => 'عنصر قائمة 1',
                'menu-item-2' => 'عنصر قائمة 2',
                'menu-item-3' => 'عنصر قائمة 3',
                'menu-item-4' => 'عنصر قائمة 4'
            ]);
        } else {
            $trans->setMultiple('menus/main-menu', [
                'menu-item-1' => 'Menu Item 1',
                'menu-item-2' => 'Menu Item 2',
                'menu-item-3' => 'Menu Item 3',
                'menu-item-4' => 'Menu Item 4'
            ]);
        }
    }
}
