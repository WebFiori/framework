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

use WebFiori\Mail\Email;
use WebFiori\Mail\Exceptions\SMTPException;
use WebFiori\Mail\SMTPAccount;
use webfiori\framework\exceptions\MissingLangException;
/**
 * A class that can be used to write HTML formatted Email messages.
 *
 * @author Ibrahim
 * @version 1.0.6
 */
class EmailMessage extends Email {
    /**
     *
     * @var Lang|null
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
        $acc = App::getConfig()->getSMTPConnection($sendAccountName);

        if ($acc instanceof SMTPAccount) {
            parent::__construct($acc);

            return;
        }
        throw new SMTPException('No SMTP connection was found which has the name "'.$sendAccountName.'".');
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
     * @return Lang|null The returned object labels will be based on the
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
     * @return Email The method will return same instance at which the method is
     * called on.
     *
     * @throws MissingLangException
     */
    public function setLang(string $lang = 'EN') : Email {
        if (parent::setLang($lang)) {
            $this->usingLanguage();
        }

        return $this;
    }

    /**
     * @throws MissingLangException
     */
    private function usingLanguage() {
        if ($this->getLang() !== null) {
            try {
                $this->tr = Lang::loadTranslation($this->getLang());
            } catch (MissingLangException $ex) {
                throw new MissingLangException($ex->getMessage());
            }
            $this->getDocument()->getBody()->setStyle([
                'direction' => $this->getTranslation()->getWritingDir()
            ], true);
        }
    }
}
