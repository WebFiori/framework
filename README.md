# WebFiori Framework

<p align="center">
<img width="90px" hight="90px" src="https://webfiori.com/assets/images/favicon.png">
</p>

<p align="center">
  <a href="https://github.com/WebFiori/framework/actions"><img src="https://github.com/WebFiori/framework/workflows/Build%20PHP%207,8/badge.svg?branch=master"></a>
  <a href="https://codecov.io/gh/WebFiori/framework">
    <img src="https://codecov.io/gh/WebFiori/framework/branch/master/graph/badge.svg" />
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

What is WebFiori Framework?

WebFiori Framework is a mini web development framework which is built using PHP language. The framework is fully object oriented (OOP). It uses semi-MVC model but it does not force it. The framework comes with many features which can help in making your website or web application up and running in no time.

## Key Features

* Provide minimum utilities to setup a small web application.
* Theming and the ability to create multiple UIs for the same web page using any CSS or JavaScript framework.
* Building and manipulating the DOM of a web page using PHP language.
* Basic template engine.
* Fast routing system that makes the ability of creating search-engine-friendly URLs an easy task.
* Creation of web services (or APIs) that supports JSON with data filtering and validation.
* Middleware support which can help in filtering HTTP requests before reaching application level.
* Basic support for MySQL schema and query building.
* Lightweight. The total size of framework core files is less than 3 megabytes.
* Access management by assigning system user a set of privileges.
* Simple sesstions manager.
* Support for creating and sending nice-looking HTML emails in a simple way by using SMTP protocol.
* Autoloading of user defined classes (loading composer packages also supported).
* The ability to create background tasks and let them run in specific time using CRON.
* Well-defined file upload and file handling sub-system.
* Basic support for running the framework throgh CLI.
* Ability to implement custom CLI commands.

## Standard Libraries

| Library | Build | Latest |
|----|----|----|
| [HTTP](https://github.com/WebFiori/http) | <a href="https://github.com/WebFiori/http/actions"><img src="https://github.com/WebFiori/http/workflows/Build%20PHP%207,8/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/http/releases"><img src="https://img.shields.io/github/release/WebFiori/restEasy.svg" /></a> |
| [Json](https://github.com/WebFiori/json) | <a href="https://github.com/WebFiori/json/actions"><img src="https://github.com/WebFiori/json/workflows/Build%20PHP%207,8/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/json/releases"><img src="https://img.shields.io/github/release/WebFiori/json.svg" /></a> |
| [UI](https://github.com/WebFiori/ui) | <a href="https://github.com/WebFiori/ui/actions"><img src="https://github.com/WebFiori/ui/workflows/Build%20PHP%207,8/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/ui/releases"><img src="https://img.shields.io/github/release/WebFiori/ui.svg" /></a> |
| [Collections](https://github.com/WebFiori/collections) | <a href="https://github.com/WebFiori/collections/actions"><img src="https://github.com/WebFiori/collections/workflows/Build%20PHP%207,8/badge.svg?branch=master"></a> | <a href="https://github.com/WebFiori/collections/releases"><img src="https://img.shields.io/github/release/WebFiori/collections.svg" /></a> |
| [Database](https://github.com/WebFiori/database) | <a href="https://github.com/WebFiori/database/actions"><img src="https://github.com/WebFiori/database/workflows/Build%20PHP%207,8/badge.svg?branch=main"></a> | <a href="https://github.com/WebFiori/database/releases"><img src="https://img.shields.io/github/release/WebFiori/database.svg" /></a> |

## Problems Solved

One of the things that any developer cares about any software project is the problem or problems it solves. As for WebFiori framework, It can help in solving the following problems:
* The ability to create a customized links to web pages as needed ([Routing](https://webfiori.com/learn/routing)).
* No need for touching HTML to play with the DOM ([Building UI](https://webfiori.com/learn/ui-package)).
* Run PHP code as a CRON task through HTTP protocol or through terminal ([Background Jobs](https://webfiori.com/learn/background-tasks)).
* Changing whole user interface by changing one line of code ([Theming](https://webfiori.com/learn/themes)).
* Ability to move the source code of the web application without having to do a lot of re-configuration.
* Sending HTML email messages with attachments without having to write a lot of code ([Sending Emails](https://webfiori.com/learn/sending-emails)).
* Solved the issues which are found in default PHP session management implementation ([Sessions Management](https://webfiori.com/learn/sessions-management)).
* Reduce the number of dependencies at which a developer need to setup a web application.

## Getting Started 

To learn the basics of how to use the framework, please head on to https://webfiori.com/learn. You can also read samedocs docs which can be found in [docs repo](https://github.com/usernane/wf-docs). In addition to that, you can read the API docs of the framework at [the official website](https://webfiori.com/docs).


## Setup

### Local Development Environment

If you plan to test the framework on your local machine, the recomended way is to have AMP stack (Apache, MySQL and PHP). There are many available online. We suggest to use the ones that are offered by Bitnami. You can go to https://bitnami.com/stacks/infrastructure to check  the available options.

After installing AMP stack, you can ethier use composer to install the framework or install it manually by download it throgh https://webfiori.com/download. If you plan to use composer, then you must first download composer from their website: https://getcomposer.org/download/. Once downloaded, place the `.phar` file in the folder `htdocs` or your server root. Once you do that, run the terminal in `htdocs` and run the following command: 

```
php composer.phar create-project --prefer-dist webfiori/framework my-site
```
This command will create new folder with the name `my-site` and install the framework inside it. 

For more information about how to setup the framework, [check here](https://webfiori.com/learn/installation).


## Contribution

For information on how to contribute to the project, [check here](https://webfiori.com/contribute).

## Notes
* This project is a hoppy project. 
* The main aim of this project is learning.
* If you think that there is a better way of doing things or wants new feature, feel free to [drop an issue](https://github.com/WebFiori/framework/issues/new).
* To report security vulnerabilities, please send an email to [ibrahim@webfiori.com](mailto:ibrahim@webfiori.com).

## License

The project is licensed under MIT license.
