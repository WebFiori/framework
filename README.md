# WebFiori Framework
<p align="center">
<img width="90px" hight="90px" src="https://programmingacademia.com/webfiori/themes/webfiori/images/favicon.png">
</p>
<p align="center">
  <a href="https://travis-ci.org/usernane/webfiori">
    <img src="https://travis-ci.org/usernane/webfiori.svg?branch=master">
  </a>
  <a href="https://codecov.io/gh/usernane/webfiori">
    <img src="https://codecov.io/gh/usernane/webfiori/branch/master/graph/badge.svg" />
  </a>
  <a href="https://sonarcloud.io/dashboard?id=usernane_webfiori">
      <img src="https://sonarcloud.io/api/project_badges/measure?project=usernane_webfiori&metric=alert_status" />
  </a>
  <a href="https://github.com/usernane/webfiori/releases">
      <img src="https://img.shields.io/github/release/usernane/webfiori.svg?label=latest" />
  </a>
  <img src="https://img.shields.io/packagist/dt/webfiori/framework?color=light-green">
</p>
What is WebFiori Framework?

WebFiori Framework is a mini web development framework which is built using PHP language. The framework is fully object oriented (OOP). It semi-MVC model but it does not force it. The framework comes with many features which can help in making your website or web application up and running in no time.

## Key Features
* Provide minimum utilities to setup a small web application.
* Theming and the ability to create multiple UIs for the same web page using any CSS or JavaScript framework.
* Building and manipulating the DOM of a web page using PHP language.
* Basic template engine.
* Fast routing system that makes the ability of creating search-engine-friendly links an easy task.
* Creation of web services (or APIs) that supports JSON with data filtering and validation.
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

## Problems Solved
One of the things that any developer cares about any software project is the problem or problems it solves. As for WebFiori framework, It can help in solving the following problems:
* It helps in making any one with Java background to write PHP code.
* The ability to create a customized links to web pages as needed using routing.
* No need for touching HTML to play with the DOM. Only use PHP.
* Run PHP code as a CRON task through HTTP protocol or through terminal.
* Changing whole user interface by changing one line of code.
* Ability to move the source code of the web application without having to do a lot of re-configuration.
* Sending HTML email messages with attachments without having to write a lot of code.
* Solved the issues which are found in default PHP session management implementation.

## Getting Started 
To learn the basics of how to use the framework, please head on to https://webfiori.com/learn. You can also read the docs which can be found in [docs repo](https://github.com/usernane/wf-docs). In addition to that, you can read the API docs of the framework at [the official website](https://webfiori.com/docs).

## Supported Environments 
| PHP | AMD64 (Targeted)     | ppc64le              | s390x                | arm64                |
| --- | -------------------- | -------------------- | -------------------- | -------------------- |
| 5.6 | [![Build01][01]][0]  | [![Build02][05]][0]  | Not Applicable       | Not Applicable       |
| 7.2 | [![Build05][02]][0]  | [![Build06][06]][0]  | [![Build07][09]][0]  | [![Build08][12]][0]  |
| 7.3 | [![Build09][03]][0]  | [![Build10][07]][0]  | [![Build11][10]][0]  | [![Build12][13]][0]  |
| 7.4 | [![Build13][04]][0]  | [![Build14][08]][0]  | [![Build15][11]][0]  | [![Build16][14]][0]  |

[0]: https://travis-ci.org/usernane/webfiori
[01]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/1
[02]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/2
[03]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/3
[04]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/4
[05]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/5
[06]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/6
[07]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/7
[08]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/8
[09]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/9
[10]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/10
[11]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/11
[12]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/12
[13]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/13
[14]: https://travis-matrix-badges.herokuapp.com/repos/usernane/webfiori/branches/master/14


## Setup
### Local Development Environment
If you plan to test the framework on your local machine, you have to download AMP (Apache, MySQL and PHP) stack first. 
We suggest to use the ones that are offered by Bitnami. You can go to https://bitnami.com/stacks/infrastructure to check 
the available options.

After installing AMP stack, you can ethier use composer to install the framework or download it throgh https://webfiori.com/download. If you plan to use composer, then you must first download it from their website: https://getcomposer.org/download/. Once downloaded, place the `.phar` file in the folder `htdocs` or your server root. Once you do that, run the terminal in `htdocs` and run the following command: 

```
php composer.phar create-project --prefer-dist webfiori/framework my-site
```
This command will create new folder with the name `my-site` and install the framework inside it. 


## Notes
* This project is a hoppy project. 
* The main aim of this project is learning.
* The project is reletivly new and for sure has issues.
* If you think that there is a better way of doing things or wants new feature, feel free to drop an issue.
* To report security vulnerabilities, please send an email to [ibrahim@webfiori.com](mailto:ibrahim@webfiori.com).

## License
The project is licensed under MIT license.
