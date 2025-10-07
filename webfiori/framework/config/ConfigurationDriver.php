<?php
namespace webfiori\framework\config;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Mail\SMTPAccount;
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
     * Adds application environment variable to the configuration.
     *
     * The variables which are added using this method will be defined as
     * a named constant at run time using the function 'define'. This means
     * the constant will be accesaable anywhere within the application's environment.
     * Additionally, it will be added as environment variable using 'putenv()'.
     *
     * @param string $name The name of the named constant such as 'MY_CONSTANT'.
     *
     * @param mixed|null $value The value of the constant.
     *
     * @param string $description An optional description to describe the porpuse
     * of the constant.
     */
    public function addEnvVar(string $name, mixed $value = null, ?string $description = null);
    /**
     * Adds new database connections information or update existing connections.
     *
     *
     * @param ConnectionInfo $dbConnectionsInfo An object which holds connection information.
     */
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo);
    /**
     * Adds new SMTP account or Updates an existing one.
     *
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     */
    public function addOrUpdateSMTPAccount(SMTPAccount $emailAccount);
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
     * Returns an array that holds different names for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the name.
     *
     */
    public function getAppNames() : array;
    /**
     * Returns a string that represents the date at which the version of
     * the application was released at.
     *
     * @return string A string in the format 'YYYY-MM-DD'.
     */
    public function getAppReleaseDate() : string;
    /**
     * Returns version number of the application.
     *
     * @return string The method should return a string in the format 'x.x.x' if
     * semantic versioning is used.
     */
    public function getAppVersion() : string;
    /**
     * Returns a string that represents the type of application version.
     *
     * @return string A string such as 'alpha', 'beta' or 'rc'.
     */
    public function getAppVersionType() : string;
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
     * Returns database connection information given connection name.
     *
     * @param string $conName The name of the connection.
     *
     * @return ConnectionInfo|null The method should return an object of type
     * ConnectionInfo if a connection info was found for the given connection name.
     * Other than that, the method should return null.
     *
     */
    public function getDBConnection(string $conName);
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
     * Returns application description on specific display language.
     *
     * This description is used by web pages  as default in case no description is set for the page.
     *
     * @param string $langCode Language code such as 'AR' or 'EN'.
     *
     * @return string|null If the description of the application
     * does exist in the given language, the method should return it.
     * If no such description, the method should return null.
     */
    public function getDescription(string $langCode);
    /**
     * Returns an array that holds different descriptions for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the description.
     */
    public function getDescriptions() : array;
    /**
     * Returns an associative array of application constants.
     *
     * @return array The indices of the array are names of the constants and
     * values are sub-associative arrays. Each sub-array must have two indices,
     * 'value' and 'description'.
     */
    public function getEnvVars() : array;
    /**
     * Returns a string that represents the URL of home page of the application.
     *
     * @return string
     */
    public function getHomePage() : string;
    /**
     * Returns a two-letters string that represents primary language of the application.
     *
     * @return string A two-letters string that represents primary language of the application.
     */
    public function getPrimaryLanguage() : string;
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
     * Returns SMTP connection given its name.
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
    public function getSMTPConnection(string $name);
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
    public function getSMTPConnections() : array;
    /**
     * Returns the name of the theme that will be used as default theme for
     * all web pages.
     *
     * @return string The name of the theme that is used in admin control pages.
     * This also can be the class name of the theme.
     */
    public function getTheme() : string;
    /**
     * Returns the default title at which a web page will use in case no title
     * is specified.
     *
     * @param string $lang A two-letter string that represents language code.
     * The returned value will be specific to selected language.
     *
     * @return string The default title at which a web page will use in case no title
     * is specified.
     */
    public function getTitle(string $lang) : string;
    /**
     * Returns an array that holds different page titles for the web application
     * on different languages.
     *
     * @return array The indices of the array are language codes such as 'AR' and
     * the value of the index is the title.
     *
     */
    public function getTitles() : array;
    /**
     * Returns a string that represents the value which is used to separate the
     * title of a web page from the name of the application.
     *
     * @return string
     */
    public function getTitleSeparator() : string;
    /**
     * Initialize configuration driver.
     *
     * This method should be used to create application configuration and
     * pubulate it with default values if needed.
     *
     * @param bool $reCreate If the configuration is exist and this one is set
     * to true, the method should remove existing configuration and re-create it
     * using default values.
     */
    public function initialize(bool $reCreate = false);
    /**
     * Removes all configuration variables.
     */
    public function remove();
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
     * Removes specific application environment variable given its name.
     *
     * @param string $name The name of the variable.
     */
    public function removeEnvVar(string $name);
    /**
     * Removes SMTP account if it exists.
     *
     * @param string $accountName The name of the email account (such as 'no-reply').
     *
     */
    public function removeSMTPAccount(string $accountName);
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
     * Sets the base URL of the web application.
     *
     * @param string $url
     */
    public function setBaseURL(string $url);
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
    /**
     * Sets the home page of the application.
     *
     *
     * @param string $url The URL of the home page of the website. For example,
     * This page is served when the user visits the domain without specifying a path.
     */
    public function setHomePage(string $url);
    /**
     * Sets the main display language of the website/application.
     *
     * @param string $langCode The main display language code such as 'AR' or 'EN'.
     */
    public function setPrimaryLanguage(string $langCode);
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
     * Sets the default theme which will be used to style web pages.
     *
     * @param string $theme The name of the theme that will be used to style
     * website UI. This can also be class name of the theme.
     */
    public function setTheme(string $theme);
    /**
     * Sets or updates default web page title for a specific display language.
     *
     * @param string $title The title that will be set.
     *
     * @param string $langCode The display language at which the title will be
     * set or updated for.
     */
    public function setTitle(string $title, string $langCode);
    /**
     * Sets the string which is used to separate application name from page name.
     *
     * @param string $separator A character or a string that is used
     * to separate application name from web page title. Two common
     * values are '-' and '|'.
     */
    public function setTitleSeparator(string $separator);
}
