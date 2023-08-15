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

use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
/**
 * An interface which holds basic methods that any application configuration
 * class must have.
 *
 * @author Ibrahim
 *
 * @since 2.2.1
 */
interface Config {
    /**
     * Adds an email account.
     *
     * The developer can use this method to add new account during runtime.
     * The account will be removed once the program finishes.
     *
     * @param SMTPAccount $acc an object of type SMTPAccount.
     *
     * @since 1.0
     */
    public function addAccount(SMTPAccount $acc);
    /**
     * Adds new database connection or updates an existing one.
     *
     * @param ConnectionInfo $connectionInfo an object of type 'ConnectionInfo'
     * that will contain connection information.
     *
     * @since 1.0
     */
    public function addDbConnection(ConnectionInfo $connectionInfo);
    /**
     * Returns SMTP account given its name.
     *
     * The method will search for an account with the given name in the set
     * of added accounts. If no account was found, null is returned.
     *
     * @param string $name The name of the account.
     *
     * @return SMTPAccount|null If the account is found, The method
     * will return an object of type SMTPAccount. Else, the
     * method will return null.
     *
     * @since 1.0
     */
    public function getAccount(string $name);
    /**
     * Returns an associative array that contains all email accounts.
     *
     * The indices of the array will act as the names of the accounts.
     * The value of the index will be an object of type SMTPAccount.
     *
     * @return array An associative array that contains all email accounts.
     *
     * @since 1.0
     */
    public function getAccounts() : array ;
    /**
     * Returns the name of the theme that is used in admin control pages.
     *
     * @return string The name of the theme that is used in admin control pages.
     * This also can be the class name of the theme.
     *
     * @since 1.0
     */
    public function getAdminThemeName() : string ;
    /**
     * Returns the name of base theme that is used in website pages.
     *
     * Usually, this theme is used for the normally visitors of the website.
     *
     * @return string The name of base theme that is used in website pages.
     * This also can be the class name of the theme.
     *
     * @since 1.0
     */
    public function getBaseThemeName() : string ;
    /**
     * Returns the base URL that is used to fetch resources.
     *
     * The return value of this method is usually used by the tag 'base'
     * of website pages.
     *
     * @return string the base URL.
     *
     * @since 1.0
     */
    public function getBaseURL() : string;
    /**
     * Returns version number of the configuration file.
     *
     * This value can be used to check for the compatability of configuration file
     *
     * @return string The version number of the configuration file.
     *
     * @since 1.0
     */
    public function getConfigVersion() : string;
    /**
     * Returns database connection information given connection name.
     *
     * @param string $conName The name of the connection.
     *
     * @return ConnectionInfo|null The method will return an object of type
     * ConnectionInfo if a connection info was found for the given connection name.
     * Other than that, the method will return null.
     *
     * @since 1.0
     */
    public function getDBConnection(string $conName);
    /**
     * Returns an associative array that contain the information of database connections.
     *
     * The keys of the array will be the name of database connection and the
     * value of each key will be an object of type ConnectionInfo.
     *
     * @return array An associative array.
     *
     * @since 1.0
     */
    public function getDBConnections() : array;
    /**
     * Returns the global title of the website that will be
     * used as default page title.
     *
     * @param string $langCode Language code such as 'AR' or 'EN'.
     *
     * @return string|null If the title of the page
     * does exist in the given language, the method will return it.
     * If no such title, the method will return null.
     *
     * @since 1.0
     */
    public function getDefaultTitle(string $langCode);
    /**
     * Returns the global description of the website that will be
     * used as default page description.
     *
     * @param string $langCode Language code such as 'AR' or 'EN'.
     *
     * @return string|null If the description for the given language
     * does exist, the method will return it. If no such description, the
     * method will return null.
     *
     * @since 1.0
     */
    public function getDescription(string $langCode);
    /**
     * Returns an associative array which contains different website descriptions
     * in different languages.
     *
     * Each index will contain a language code and the value will be the description
     * of the website in the given language.
     *
     * @return array An associative array which contains different website descriptions
     * in different languages.
     *
     * @since 1.0
     */
    public function getDescriptions() : array;
    /**
     * Returns the home page URL of the website.
     *
     * @return string The home page URL of the website.
     *
     * @since 1.0
     */
    public function getHomePage() : string;
    /**
     * Returns the primary language of the website.
     *
     * @return string Language code of the primary language such as 'EN'.
     *
     * @since 1.0
     */
    public function getPrimaryLanguage() : string;
    /**
     * Returns the date at which the application was released at.
     *
     * @return string The method will return a string in the format
     * 'YYYY-MM-DD' that represents application release date.
     *
     * @since 1.0
     */
    public function getReleaseDate() : string;
    /**
     * Returns sha256 hash of the password which is used to prevent unauthorized
     * access to run the jobs or access scheduler web interface.
     *
     * The password should be hashed before using this method as this one should
     * return the hashed value. If no password is set, this method should return the
     * string 'NO_PASSWORD'.
     *
     * @return string Password hash or the string 'NO_PASSWORD' if there is no
     * password.
     */
    public function getSchedulerPassword() : string;
    /**
     * Returns an array that holds the default page title for different display
     * languages.
     *
     * @return array An associative array. The indices of the array are language codes
     * and the values are pages titles.
     *
     *
     * @since 1.0
     */
    public function getTitles() : array;
    /**
     * Returns the character (or string) that is used to separate page title from website name.
     *
     * @return string A string such as ' - ' or ' | '. Note that the method
     * will add the two spaces by default.
     *
     * @since 1.0
     */
    public function getTitleSep() : string;
    /**
     * Returns version number of the application.
     *
     * @return string The method should return a string in the
     * form 'x.x.x.x'.
     *
     * @since 1.0
     */
    public function getVersion() : string;
    /**
     * Returns a string that represents application release type.
     *
     * @return string The method will return a string such as
     * 'Stable', 'Alpha', 'Beta' and so on.
     *
     * @since 1.0
     */
    public function getVersionType() : string;
    /**
     * Returns the global website name.
     *
     * @param string $langCode Language code such as 'AR' or 'EN'.
     *
     * @return string|null If the name of the website for the given language
     * does exist, the method will return it. If no such name, the
     * method will return null.
     *
     * @since 1.0
     */
    public function getWebsiteName(string $langCode);
    /**
     * Returns an array which contains different website names in different languages.
     *
     * Each index will contain a language code and the value will be the name
     * of the website in the given language.
     *
     * @return array An array which contains different website names in different languages.
     *
     * @since 1.0
     */
    public function getWebsiteNames() : array;
}
