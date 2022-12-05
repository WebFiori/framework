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

use webfiori\email\exceptions\SMTPException;
use webfiori\email\SMTPAccount;
use webfiori\framework\exceptions\MissingLangException;
use webfiori\framework\Language;
use webfiori\framework\WebFioriApp;

/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.6
 */
class EmailMessage extends \webfiori\email\EmailMessage {
    /**
     *
     * @var Language|null 
     * 
     * @since 1.0.5
     */
    private $tr;
    /**
     * Creates new instance of the class.
     * 
     * @param string $sendAccountName The name of SMTP connection that will be 
     * used to send the message. It must exist in the class 'AppConfig'. Default 
     * value is 'no-reply'.
     * 
     * @throws SMTPException If the given SMTP connection does not exist.
     * 
     * @since 1.0
     */
    public function __construct(string $sendAccountName = 'no-reply') {
        $acc = WebFioriApp::getAppConfig()->getAccount($sendAccountName);

        if ($acc instanceof SMTPAccount) {
            parent::__construct($acc);

            return;
        }
        throw new SMTPException('No SMTP account was found which has the name "'.$sendAccountName.'".');
    }
    public function get(string $label) {
        $langObj = $this->getTranslation();

        if ($langObj !== null) {
            return $langObj->get($label);
        }

        return $label;
    }
    /**
     * Returns an object which holds i18n labels.
     * 
     * @return Language|null The returned object labels will be based on the 
     * language of the email. If no translation is loaded, the method will 
     * return null.
     * 
     * @since 1.0.5
     */
    public function getTranslation() {
        return $this->tr;
    }
    
    /**
     * Sets the display language of the email.
     * 
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * 
     * @param string $lang a two digit language code such as AR or EN. Default 
     * value is 'EN'.
     * 
     * @since 1.0.5
     */
    public function setLang(string $lang = 'EN') : bool {
        if (parent::setLang($lang)) {
            $this->usingLanguage();
            return true;
        }
        return false;
    }
    
    private function usingLanguage() {
        if ($this->getLang() !== null) {
            try {
                $this->tr = Language::loadTranslation($this->getLang());
            } catch (MissingLangException $ex) {
                throw new MissingLangException($ex->getMessage());
            }
            $this->getDocument()->getBody()->setStyle([
                'direction' => $this->getTranslation()->getWritingDir()
            ], true);
        }
    }
}
