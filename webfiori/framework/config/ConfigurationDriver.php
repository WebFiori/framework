<?php
namespace webfiori\framework\config;

use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
/**
 * An interface which holds base methods for implementing application configuration storage.
 * 
 * The main aim of this class is to provide developers with the ability to create
 * custom drivers for storing application configuration variables.
 *
 * @author Ibrahim
 */
interface ConfigurationDriver {
    /**
     * Returns SMTP account given its name.
     * 
     * The method should be implemented in a way that it searches
     * for an account with the given name in the set
     * of added accounts. If no account was found, null is returned.
     * 
     * @param string $name The name of the account.
     * 
     * @return SMTPAccount|null If the account is found, The method
     * should return an object of type SMTPAccount. Else, the
     * method should return null.
     * 
     */
    public function getSMTPAccount(string $name);
    public function addEnvVar(string $name, $value, string $description = null);
    public function getEnvVars() : array;
    public function getPrimaryLanguage() : string;
    public function getTitleSeparator() : string;
    public function getTitle() : string;
    /**
     * Returns an associative array that contains all added SMTP accounts.
     * 
     * The method should be implemented in a way that it returns an associative array.
     * The indices of the array should act as the names of the accounts,
     * and the value of the index should be an object of type SMTPAccount.
     * 
     * @return array An associative array that contains all added SMTP accounts.
     * 
     */
    public function getSMTPAccounts() : array;
    /**
     * Returns the name of the theme that will be used as default theme for
     * all web pages.
     * 
     * @return string The name of the theme that is used in admin control pages.
     * This also can be the class name of the theme.
     */
    public function getTheme() : string;
    /**
     * Sets the base URL of the web application.
     * 
     * @param string $url
     */
    public function setBaseURL(string $url);
    public function initialize();
    public function getAppVersion();
    public function getAppVersionType();
    public function getAppReleaseDate();
    public function getHomePage();
    /**
     * Returns the base URL that is used to fetch resources.
     * 
     * The return value of this method is usually used by the tag 'base'
     * of website pages.
     * 
     * @return string the base URL.
     */
    public function getBaseURL() : string;
    /**
     * Returns sha256 hash of the password which is used to prevent unauthorized
     * access to run background tasks or access scheduler web interface.
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
     * Returns database connection information given connection name.
     * 
     * @param string $conName The name of the connection.
     * 
     * @return ConnectionInfo|null The method should return an object of type
     * ConnectionInfo if a connection info was found for the given connection name.
     * Other than that, the method should return null.
     * 
     * @since 1.0
     */
    public function getDBConnection(string $conName);
    public function getDescription(string $langCode);
    /**
     * Returns an associative array that contain the information of database connections.
     * 
     * The method should be implemented in a way that it returns an associative array.
     * The keys of the array should be the name of database connection and the
     * value of each key should be an object of type ConnectionInfo.
     * 
     * @return array An associative array.
     */
    public function getDBConnections() : array;
    /**
     * Adds new database connections information or update existing connections.
     *
     *
     * @param ConnectionInfo $dbConnectionsInfo An object which holds connection information.
     */
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo);
    /**
     * Removes SMTP account if it exists.
     *
     * @param string $accountName The name of the email account (such as 'no-reply').
     *
     */
    public function removeSMTPAccount(string $accountName);
    /**
     * Removes all stored database connections.
     */
    public function removeAllDBConnections();
    /**
     * Removes database connection given its name.
     *
     * This method will search for a connection which has the given database
     * name. Once it found, it will remove the connection.
     *
     * @param string $connectionName The name of the connection.
     *
     */
    public function removeDBConnection(string $connectionName);
    /**
     * Sets or updates the name of the application for specific display language.
     * 
     * @param string $name The name of the application.
     * 
     * @param string $langCode The language code at which the name of the application will
     * be updated for.
     */
    public function setAppName(string $name, string $langCode);
    /**
     * Returns application name.
     * 
     * @param string $langCode Language code such as 'AR' or 'EN'.
     * 
     * @return string|null If the name of the application
     * does exist in the given language, the method should return it.
     * If no such name, the method should return null.
     */
    public function getAppName(string $langCode);
    /**
     * Update application version information.
     *
     * @param string $vNum Version number such as 1.0.0.
     *
     * @param string $vType Version type such as 'Beta', 'Alpha' or 'RC'.
     *
     * @param string $releaseDate The date at which the version was released on.
     *
     */
    public function setAppVersion(string $vNum, string $vType, string $releaseDate);
    /**
     * Updates the password which is used to protect tasks from unauthorized
     * execution.
     *
     * @param string $newPass The new password. Note that provided value
     * must be hashed using SHA256 algorithm.
     *
     */
    public function setSchedulerPassword(string $newPass);
    /**
     * Adds new SMTP account or Updates an existing one.
     * 
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     */
    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount);
    /**
     * Sets the main display language of the website/application.
     * 
     * @param string $langCode The main display language code such as 'AR' or 'EN'.
     */
    public function setPrimaryLanguage(string $langCode);
    /**
     * Sets the string which is used to separate application name from page name.
     * 
     * @param string $separator A character or a string that is used
     * to separate application name from web page title. Two common
     * values are '-' and '|'.
     */
    public function setTitleSeparator(string $separator);
    /**
     * Sets the home page of the application.
     * 
     * 
     * @param string $url The URL of the home page of the website. For example,
     * This page is served when the user visits the domain without specifying a path.
     */
    public function setHomePage(string $url);
    /**
     * Sets the default theme which will be used to style web pages.
     * 
     * @param string $theme The name of the theme that will be used to style
     * website UI. This can also be class name of the theme.
     */
    public function setTheme(string $theme);
    /**
     * Sets or update default description of the application that will be used
     * by web pages.
     * 
     * @param string $description The default description.
     * 
     * @param string $langCode The code of the language at which the description
     * will be updated for.
     */
    public function setDescription(string $description, string $langCode);
}
