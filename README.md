# WebFiori Framework

<p align="center">
<img width="90px" hight="90px" src="https://webfiori.com/assets/images/favicon.png">
</p>

<p align="center">
  <a href="https://github.com/WebFiori/framework/actions"><img src="https://github.com/WebFiori/framework/actions/workflows/php84.yml/badge.svg?branch=main"></a>
  <a href="https://codecov.io/gh/WebFiori/framework">
    <img src="https://codecov.io/gh/WebFiori/framework/branch/main/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=WebFiori_framework">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=WebFiori_framework&metric=alert_status" />
  </a>
  <a href="https://github.com/WebFiori/framework/releases">
      <img src="https://img.shields.io/github/release/WebFiori/framework.svg?label=latest" />
  </a>
  <a href="https://packagist.org/packages/webfiori/framework">
      <img src="https://img.shields.io/packagist/dt/webfiori/framework?color=light-green">
  </a>
</p>

> Note: This repo contains the core of the framework. Application template can be found in the repo [`webfiori/app`](https://github.com/webfiori/app)

## What is WebFiori Framework?

WebFiori Framework is a mini web development framework which is built using PHP language. The framework is fully object-oriented (OOP). It uses semi-MVC model, but it does not force it. The framework comes with many features which can help in making your website or web application up and running in no time.

## Supported PHP Versions
| Build Status |
|:-----------:|
|<a target="_blank" href="https://github.com/WebFiori/framework/actions/workflows/php80.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php80.yml/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/framework/actions/workflows/php81.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php81.yml/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/framework/actions/workflows/php82.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php82.yml/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/framework/actions/workflows/php83.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php83.yml/badge.svg?branch=main"></a>|
|<a target="_blank" href="https://github.com/WebFiori/framework/actions/workflows/php84.yml"><img src="https://github.com/WebFiori/framework/actions/workflows/php84.yml/badge.svg?branch=main"></a><br>|


## Key Features

* Provide minimum utilities to setup a small/medium web application.
* Theming and the ability to create multiple UIs for the same web app using any CSS or JavaScript framework.
* Building and manipulating the DOM of a web page inside PHP.
* Basic template engine.
* Fast and simple routing engine.
* Creation of web services (or APIs) that supports JSON with data filtering and validation.
* Middleware support for HTTP requests filtering before reaching application level.
* Basic support for MySQL and MSSQL schema and query building.
* Lightweight. The total size of framework core files is less than 3 megabytes.
* Access management by assigning system user a set of privileges.
* Customized sessions manager.
* Support for creating and sending nice-looking HTML emails.
* Autoload of user defined classes (loading composer packages also supported).
* Ability to create background tasks and let them run in specific time using CRON.
* Well-defined file upload and file handling sub-system.
* Basic support for creating CLI Applications.

## Standard Libraries

Following table shows build status of the standard libraries that the framework is composed of. The build is based on the latest stable PHP release. 

| Library | Build | Latest |
|----|----|----|
| [HTTP](https://github.com/WebFiori/http) | <a href="https://github.com/WebFiori/http/actions"><img src="https://github.com/WebFiori/http/actions/workflows/php83.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/http/releases"><img src="https://img.shields.io/github/release/WebFiori/http.svg" /></a> |
| [Cache](https://github.com/WebFiori/cache) | <a href="https://github.com/WebFiori/cache/actions"><img src="https://github.com/WebFiori/cache/actions/workflows/php84.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/cache/releases"><img src="https://img.shields.io/github/release/WebFiori/cache.svg" /></a> |
| [File](https://github.com/WebFiori/file) | <a href="https://github.com/WebFiori/file/actions"><img src="https://github.com/WebFiori/file/actions/workflows/php83.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/file/releases"><img src="https://img.shields.io/github/release/WebFiori/file.svg" /></a> |
| [Json](https://github.com/WebFiori/json) | <a href="https://github.com/WebFiori/json/actions"><img src="https://github.com/WebFiori/json/actions/workflows/php83.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/json/releases"><img src="https://img.shields.io/github/release/WebFiori/json.svg" /></a> |
| [UI](https://github.com/WebFiori/ui) | <a href="https://github.com/WebFiori/ui/actions"><img src="https://github.com/WebFiori/ui/actions/workflows/php83.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/ui/releases"><img src="https://img.shields.io/github/release/WebFiori/ui.svg" /></a> |
| [Collections](https://github.com/WebFiori/collections) | <a href="https://github.com/WebFiori/collections/actions"><img src="https://github.com/WebFiori/collections/actions/workflows/php83.yml/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/collections/releases"><img src="https://img.shields.io/github/release/WebFiori/collections.svg" /></a> |
| [Database](https://github.com/WebFiori/database) | <a href="https://github.com/WebFiori/database/actions"><img src="https://github.com/WebFiori/database/actions/workflows/php83.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/database/releases"><img src="https://img.shields.io/github/release/WebFiori/database.svg" /></a> |
| [CLI](https://github.com/WebFiori/cli) | <a href="https://github.com/WebFiori/cli/actions"><img src="https://github.com/WebFiori/cli/actions/workflows/php83.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/cli/releases"><img src="https://img.shields.io/github/release/WebFiori/cli.svg" /></a> |
| [Mailer](https://github.com/WebFiori/mail) | <a href="https://github.com/WebFiori/mail/actions"><img src="https://github.com/WebFiori/mail/actions/workflows/php83.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/mail/releases"><img src="https://img.shields.io/github/release/WebFiori/mail.svg" /></a> |
| [Errors and Exceptions Handler](https://github.com/WebFiori/err) | <a href="https://github.com/WebFiori/err/actions"><img src="https://github.com/WebFiori/err/actions/workflows/php83.yml/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/err/releases"><img src="https://img.shields.io/github/release/WebFiori/err.svg" /></a> |

## Problems Solved

One of the things that any developer cares about any software project is the problem or problems it solves. As for WebFiori framework, It can help in solving the following problems:
* The ability to create a customized links to web pages as needed by using [Routing](https://webfiori.com/learn/routing).
* No need for touching HTML to play with the DOM by using [UI Library](https://webfiori.com/learn/ui-package) of the framework.
* Run PHP code as a CRON task through HTTP protocol or through terminal as a [Background Job](https://webfiori.com/learn/background-tasks).
* Changing whole user interface by changing one line of code through [Theming](https://webfiori.com/learn/themes).
* Ability to move the source code of the web application without having to do a lot of re-configuration.
* [Sending HTML email](https://webfiori.com/learn/sending-emails) messages with attachments without having to write a lot of code.
* Solved the issues which are found in default PHP session management implementation by implementing a custom [Sessions Management System](https://webfiori.com/learn/sessions-management).
* Reduce the number of dependencies at which a developer need to set up a web application.

## Getting Started 

To learn the basics of how to use the framework, please head on to https://webfiori.com/learn. You can also read same docs which can be found in [docs repo](https://github.com/usernane/wf-docs). In addition to that, you can read the API docs of the framework at [the official website](https://webfiori.com/docs).


## Setup

### Local Development Environment

If you plan to test the framework on your local machine, the recommended way is to have AMP stack (Apache, MySQL and PHP). There are many available online. We suggest to use the ones that are offered by Bitnami. You can go to https://bitnami.com/stacks/infrastructure to check  the available options.

After installing AMP stack, you can either use composer to install the framework or install it manually by download it through https://webfiori.com/download. If you plan to use composer, then you must first download composer from their website: https://getcomposer.org/download/. Once downloaded, place the `.phar` file in the folder `htdocs` or your server root. Once you do that, run the terminal in `htdocs` and run the following command: 

```
php composer.phar create-project --prefer-dist webfiori/app my-site
```
This command will create new folder with the name `my-site` and install the framework inside it. 

For more information about how to set up the framework, [check here](https://webfiori.com/learn/installation).


## Contribution

For information on how to contribute to the project, [check here](https://webfiori.com/contribute).

## Notes
* If you think that there is a better way of doing things or wants new feature, feel free to [drop an issue](https://github.com/WebFiori/framework/issues/new).
* To report security vulnerabilities, please email [ibrahim@webfiori.com](mailto:ibrahim@webfiori.com).

## License

The project is licensed under MIT license.
