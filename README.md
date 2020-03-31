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
  <a href="https://paypal.me/IbrahimBinAlshikh">
    <img src="https://img.shields.io/endpoint.svg?url=https%3A%2F%2Fprogrammingacademia.com%2Fwebfiori%2Fapis%2Fshields-get-dontate-badget">
  </a>
</p>
What is WebFiori Framework?

WebFiori Framework is a web framework which is built using PHP language. The framework is fully object oriented (OOP). It allows the use of the famous model-view-controller (MVC) model but it does not force it. The framework comes with many features which can help in making your website or web application up and running in no time.

## Key Features
* Theming and the ability to create multiple UIs for the same web page using any CSS or JavaScript framework.
* Support for routing that makes the ability of creating search-engine-friendly links an easy task.
* Creation of web APIs that supports JSON, data filtering and validation.
* Basic support for MySQL schema and query building.
* Lightweight. The total size of framework core files is less than 3 megabytes.
* Access management by assigning system user a set of privileges.
* The ability to create and manage multiple sessions at once.
* Support for creating and sending nice-looking emails in a simple way by using SMTP protocol.
* Autoloading of user defined classes.
* The ability to create automatic tasks and let them run in specific time using CRON.
* Well-defined file upload and file handling sub-system.
* Building and manipulating the DOM of a web page using PHP language.
* Basic support for running the framework throgh CLI.

## Problems Solved
One of the things that any developer cares about any software project is the problem or problems it solves. As for WebFiori framework, It can help in solving the following problems:
* It helps in making any one with Java background to write PHP code.
* The ability to create a customized links to web pages as needed using routing.
* No need for touching HTML to play with the DOM. Only use PHP.
* Run PHP code as a CRON task through HTTP protocol or through CLI.
* Changing whole user interface by changing one line of code.
* Ability to move the source code of the web application without having to do a lot of re-configuration.
* Sending HTML email messages with attachments without having to write a lot of code.

## Getting Started 
To learn the basics of how to use the framework, please head on to https://programmingacademia.com/webfiori/learn. Also, you can head on to the following playlist in YouTube which contains a good set of videos which can help: https://www.youtube.com/playlist?list=PLeU-QhqUhxjkACpXiPTRM9fH_zw1KF1UD.

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

## API Docs
To read API docs of the framework, please head on to https://programmingacademia.com/webfiori/docs/webfiori.

## Setup
### Local Development Environment
If you plan to test the framework on your local machine, you have to download AMP (Apache, MySQL and PHP) stack first. 
We suggest to use the ones that are offered by Bitnami. You can go to https://bitnami.com/stacks/infrastructure to check 
the available options.

Once downloaded and installed, download the latest release of the framework from https://programmingacademia.com/webfiori/download. 
After downloading, extract all files inside the folder '/htdocs'. The folder will be inside the place where you installed AMP
stack.
### Hosting on the Web
If you plan to use the framework for all of your domain, then simply upload all framework files to the root folder of your website (usually has the name "public_html"). If you plan to use it in specific part of your website, then upload framework files to the folder that the part of your website will point to.
