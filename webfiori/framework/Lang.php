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
namespace webfiori\framework;

use webfiori\framework\exceptions\MissingLangException;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;

/**
 * A class that is can be used to make the application ready for
 * Internationalization (i18n).
 *
 * In order to create a language file, the developer must extend this class.
 * The language class must be added to the namespace 'app/langs' and the name
 * of language file must be 'LangXX.php' where 'XX' are two characters that
 * represents language code. The directory at which the language file must exist in
 * is not important, but it is recommended to add them to the folder 'app/langs'
 * of the framework.
 *
 * @author Ibrahim
 *
 * @version 1.2.2
 */
class Lang {
    /**
     * A constant for left to right writing direction.
     *
     * @var string
     *
     * @since 1.0
     */
    const DIR_LTR = 'ltr';
    /**
     * A constant for right to left writing direction.
     *
     * @var string
     *
     * @since 1.0
     */
    const DIR_RTL = 'rtl';
    /**
     * The current active translation object.
     *
     * @var Lang|null
     */
    private static $ActiveLang;
    /**
     * An array that contains language definition.
     *
     * @var array
     */
    private $languageVars;
    /**
     * An associative array that contains loaded languages.
     *
     * @var array The key of the array represents two
     * characters language code. The index will contain an object of type <b>Lang</b>.
     */
    private static $loadedLangs = [];
    /**
     * An attribute that will be set to 'true' if the language
     * is added to the set of loaded languages.
     *
     * @var bool
     *
     * @since 1.2
     */
    private $loadLang;
    /**
     * Creates new instance of the class.
     *
     * @param string $dir 'ltr' or 'rtl'. Default is 'ltr'.
     *
     * @param string $code Language code (such as 'AR'). Default is 'XX'
     *
     * @param bool $addtoLoadedAfterCreate If set to true, the language object that
     * will be created will be added to the set of loaded languages. Default is true.
     *
     * @since 1.0
     */
    public function __construct(string $dir = 'ltr', string $code = 'XX', bool $addtoLoadedAfterCreate = true) {
        $this->languageVars = [];
        $this->loadLang = $addtoLoadedAfterCreate === true;

        if (!$this->setCode($code)) {
            $this->setCode('XX');
        }

        if (!$this->setWritingDir($dir)) {
            $this->setWritingDir('ltr');
        }
    }
    /**
     * Returns a reference to an associative array that contains an objects of
     * type 'Lang'.
     *
     * @return array The key of the array represents two
     * characters language code. The index will contain an object of type 'Lang'.
     *
     */
    public static function &getLoadedLangs() : array {
        return self::$loadedLangs;
    }
    /**
     * Creates a sub-array for defining language variables given initial set
     * of variables.
     *
     * @param string $dir A string that looks like a
     * directory.
     *
     * @param array $labels An associative array. The key will act as the variable
     * name and the value of the key will act as the variable value.
     *
     * @since 1.2.1
     */
    public function createAndSet(string $dir, array $labels) {
        $this->createDirectory($dir);
        $this->setMultiple($dir, $labels);
    }
    /**
     * Creates a sub array to define language variables.
     *
     * @param string $dir A string that looks like a
     * directory. For example, if the given string is 'general',
     * an array with key name 'general' will be created. Another example is
     * if the given string is 'pages/login', two arrays will be created. The
     * top one will have the key value 'pages' and another one inside
     * the pages array with key value 'login'. Also, this value can be
     * something like 'pages.login'.
     *
     * @since 1.0
     */
    public function createDirectory(string $dir) {
        $trim00 = trim($this->replaceDot($dir));
        $trim01 = trim($trim00,'/');

        if (strlen($trim01) != 0) {
            $subSplit = explode('/', $trim01);

            if (count($subSplit) != 0) {
                if (!isset($this->languageVars[$subSplit[0]])) {
                    $this->languageVars[$subSplit[0]] = [];
                    $this->_create($subSplit, $this->languageVars[$subSplit[0]],1);

                    return;
                }
                $this->_create($subSplit, $this->languageVars[$subSplit[0]],1);
            }
        }
    }
    /**
     * Returns the value of a language variable.
     *
     * @param string $name A directory to the language variable (such as 'pages/login/login-label').
     * This also can be a string similar to 'pages.login.login-label'.
     *
     * @return string|array If the given directory represents a label, the
     * function will return its value. If it represents an array, the array will
     * be returned. If nothing was found, the returned value will be the passed
     * value to the function.
     *
     * @since 1.0
     */
    public function get(string $name) {
        $trimmed = trim($this->replaceDot($name));
        $toReturn = trim($trimmed, '/');
        $trim = $toReturn;
        $subSplit = explode('/', $trim);

        if (count($subSplit) == 1) {
            if (isset($this->languageVars[$subSplit[0]])) {
                $toReturn = $this->languageVars[$subSplit[0]];
            }
        } else if (isset($this->languageVars[$subSplit[0]])) {
            $val = $this->_get($subSplit, $this->languageVars[$subSplit[0]], 1);

            if ($val !== null) {
                $toReturn = $val;
            }
        }

        return $toReturn;
    }
    /**
     * Returns the active translation.
     *
     * @return Lang|null If a translation is active, it is returned as an
     * object. Other than that, null is returned.
     */
    public static function getActive() {
        return self::$ActiveLang;
    }
    /**
     * Returns the language code that the object represents.
     *
     * @return string Language code in upper case (such as 'AR'). If language
     * code is not set, default is returned which is 'XX'.
     *
     */
    public function getCode() : string {
        if (isset($this->languageVars['code'])) {
            return $this->languageVars['code'];
        }

        return 'XX';
    }

    /**
     * Returns the value of a language variable.
     *
     * @param string $dir A directory to the language variable (such as 'pages/login/login-label').
     * This also can be a string similar to 'pages.login.login-label'.
     *
     * @param string|null $langCode An optional language code. If provided, the
     * method will attempt to replace active language with the provided
     * one. If not provided, the method
     * will attempt to load a translation based on the session or default
     * web application language.
     *
     * @return string|array If the given directory represents a label, the
     * method will return its value. If it represents an array, the array will
     * be returned. If nothing was found, the returned value will be the passed
     * value to the function.
     *
     * @throws MissingLangException
     * @since 1.0
     */
    public static function getLabel(string $dir, ?string $langCode = null) {
        if ($langCode === null) {
            $session = SessionsManager::getActiveSession();

            if ($session !== null) {
                $langCode = $session->getLangCode(true);
            } else {
                $langCode = Request::getParam('lang');

                if ($langCode === null || strlen($langCode) != 2) {
                    $langCode = App::getConfig()->getPrimaryLanguage();
                }
            }
        }

        $active = self::getActive();

        if ($active !== null && $active->getCode() == $langCode) {
            return $active->get($dir);
        }

        return self::loadTranslation($langCode)->get($dir);
    }
    /**
     * Returns an associative array that contains language variables definition.
     *
     * @return array An associative array that contains language variables definition.
     *
     * @since 1.0
     */
    public function getLanguageVars() : array {
        return $this->languageVars;
    }
    /**
     * Returns language writing direction.
     *
     * @return string 'ltr' or 'rtl'.
     *
     * @since 1.0
     */
    public function getWritingDir() : string {
        return $this->languageVars['dir'];
    }
    /**
     * Checks if the language is added to the set of loaded languages or not.
     *
     * @return bool The function will return true if the language is added to
     * the set of loaded languages.
     *
     * @since 1.2
     */
    public function isLoaded() : bool {
        return $this->loadLang;
    }
    /**
     * Loads a language file based on language code.
     *
     * @param string $langCode A two digits language code (such as 'ar').
     *
     * @throws MissingLangException An exception will be thrown if no language file
     * was found that matches the given language code. Language files must
     * have the name 'LanguageXX.php' where 'XX' is language code. Also, the method
     * will throw an exception when the translation file is loaded but no object
     * of type 'Lang' was stored in the set of loaded translations.
     *
     * @return Lang an object of type 'Lang' is returned if
     * the language was loaded.
     *
     */
    public static function loadTranslation(string $langCode) {
        $uLangCode = strtoupper(trim($langCode));

        if (isset(self::$loadedLangs[$uLangCode])) {
            self::$ActiveLang = self::$loadedLangs[$uLangCode];

            return self::getActive();
        }
        $langClassName = APP_DIR.'\\langs\\Lang'.$uLangCode;

        if (!class_exists($langClassName)) {
            throw new MissingLangException('No language class was found for the language \''.$uLangCode.'\'.');
        }
        $class = new $langClassName();

        if (!($class instanceof Lang)) {
            throw new MissingLangException('A language class for the language \''.$uLangCode.'\' was found. But it is not a sub class of \''.Lang::class.'\'.');
        }

        if (!isset(self::$loadedLangs[$uLangCode])) {
            throw new MissingLangException('The translation file was found. But no object of type \''.Lang::class.'\' is stored. Make sure that the parameter '
                    .'$addtoLoadedAfterCreate is set to true when creating the language object.');
        }
        self::$ActiveLang = self::$loadedLangs[$uLangCode];

        return self::getActive();
    }
    /**
     * Removes all loaded languages.
     *
     * @since 1.2.2
     */
    public static function reset() {
        self::$loadedLangs = [];
        self::$ActiveLang = null;
    }
    /**
     * Sets or updates a language variable.
     *
     * Note that the variable will be set only if the directory does exist.
     *
     * @param string $dir A string that looks like a
     * directory.
     *
     * @param string $varName The name of the variable. Note that if the name
     * of the variable is set, and it was an array, it will become a string
     * which has the given name and value.
     *
     * @param string $varValue The value of the variable.
     *
     * @since 1.0
     */
    public function set(string $dir, string $varName, string $varValue) {
        $dirTrimmed = trim($this->replaceDot($dir));
        $varTrimmed = trim($varName);

        if (strlen($dirTrimmed) != 0 && strlen($varTrimmed) != 0) {
            $trim = trim($dirTrimmed, '/');
            $subSplit = explode('/', $trim);

            if (count($subSplit) == 1) {
                if (isset($this->languageVars[$subSplit[0]])) {
                    $this->languageVars[$subSplit[0]][$varTrimmed] = $varValue;
                }
            } else if (isset($this->languageVars[$subSplit[0]])) {
                $this->_set($subSplit, $this->languageVars[$subSplit[0]],$varTrimmed,$varValue, 1);
            } else {
                $this->createAndSet($dir, [$varName => $varValue]);
            }
        }
    }
    /**
     * Sets the code of the language.
     *
     * @param string $code Language code (such as 'AR').
     *
     * @return bool The method will return true if the language
     * code is set. If not set, the method will return false.
     *
     * @since 1.1
     */
    public function setCode(string $code) : bool {
        $trimmedCode = strtoupper(trim($code));

        if (strlen($trimmedCode) == 2 && $trimmedCode[0] >= 'A' && $trimmedCode[0] <= 'Z' && $trimmedCode[1] >= 'A' && $trimmedCode[1] <= 'Z') {
            $oldCode = $this->getCode();

            if ($this->isLoaded() && isset(self::$loadedLangs[$oldCode])) {
                unset(self::$loadedLangs[$oldCode]);
            }
            $this->languageVars['code'] = $trimmedCode;

            if ($this->isLoaded()) {
                self::$loadedLangs[$trimmedCode] = &$this;
            }

            return true;
        }

        return false;
    }
    /**
     * Sets multiple language variables.
     *
     * @param string $dir A string that looks like a
     * directory.
     *
     * @param array $arr An associative array. The key will act as the variable
     * name and the value of the key will act as the variable value. The value
     * can be a sub associative array of labels or simple strings.
     *
     * @since 1.0
     */
    public function setMultiple(string $dir, array $arr = []) {
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array') {
                $this->createDirectory($dir.'/'.$k);
                $this->setMultiple($dir.'/'.$k, $v);
                continue;
            }
            $this->set($dir, $k, $v);
        }
    }
    /**
     * Sets language writing direction.
     *
     * @param string $dir 'ltr' or 'rtl'. Letters case does not matter.
     *
     * @return bool The method will return <b>true</b> if the language
     * writing direction is updated. The only case that the method
     * will return <b>false</b> is when the writing direction is invalid (
     * Any value other than 'ltr' and 'rtl').
     *
     * @since 1.0
     */
    public function setWritingDir(string $dir) : bool {
        $lDir = strtolower(trim($dir));

        if ($lDir == self::DIR_LTR || $lDir == self::DIR_RTL) {
            $this->languageVars['dir'] = $lDir;

            return true;
        }

        return false;
    }
    /**
     * Unload translation based on its language code.
     *
     * @param string $langCode A two digits language code (such as 'ar').
     *
     * @return bool If the translation file was unloaded, the method will
     * return true. If not, the method will return false.
     *
     * @since 1.2
     */
    public static function unloadTranslation(string $langCode): bool {
        $uLangCode = strtoupper(trim($langCode));

        if (isset(self::$loadedLangs[$uLangCode])) {
            unset(self::$loadedLangs[$uLangCode]);

            return true;
        }

        return false;
    }
    private function _create($subs,&$top,$index) {
        $count = count($subs);

        if ($index < $count) {
            if (!isset($top[$subs[$index]])) {
                $top[$subs[$index]] = [];

                return $this->_create($subs, $top[$subs[$index]],++$index);
            }

            return $this->_create($subs, $top[$subs[$index]],++$index);
        }

        return null;
    }
    private function _get(&$subs,&$top,$index) {
        $count = count($subs);

        if ($index + 1 == $count) {
            if (isset($top[$subs[$index]])) {
                return $top[$subs[$index]];
            }
        } else if (isset($top[$subs[$index]])) {
            return $this->_get($subs, $top[$subs[$index]], ++$index);
        }

        return null;
    }

    private function _set($subs,&$top,$var,$val,$index): bool {
        $count = count($subs);

        if ($index + 1 == $count) {
            if (isset($top[$subs[$index]])) {
                $top[$subs[$index]][$var] = $val;

                return true;
            }
        } else if (isset($top[$subs[$index]])) {
            return $this->_set($subs,$top[$subs[$index]],$var,$val, ++$index);
        }

        return false;
    }
    private function replaceDot($path) {
        return str_replace('.', '/', $path);
    }
}
