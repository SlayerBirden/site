### Maketok site application

master
[![Build Status](https://travis-ci.org/SlayerBirden/site.svg?branch=master)](https://travis-ci.org/SlayerBirden/site)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4edbf1c9-a4ff-4e8f-868f-05a22af434d8/mini.png)](https://insight.sensiolabs.com/projects/4edbf1c9-a4ff-4e8f-868f-05a22af434d8)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=master)

dev
[![Build Status](https://travis-ci.org/SlayerBirden/site.svg?branch=dev)](https://travis-ci.org/SlayerBirden/site)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=dev)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/coverage.png?b=dev)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=dev)

About
---------

When approaching the creation of website we sometimes forget how exciting the process is. I wanted to create a site using best practices I know about the application building, and ended up with this.

The app is still in development, but I'm making some progress and generally am pleased with how things are moving.

Site is bundled with different vendor packages ( which helps a lot!). But I wanted to make something by myself as well. 

"self" code is bundled in Maketok namespace and consists of major areas:
- SubjectManager - Subscribers (yes for names we need to thank recently read BandOfFour)
- Installer. Most likely the biggest piece of code written in Maketok namespace. Aimed for installing DB DDL structure from config files.
- MVC (Front Controller). Created few routes and so far that's all I need.
- Few small Util classes (like StreamHandler and DirectoryHandler).
- Some refactoring of zendframework/db DDL package, which ended up as pull request: https://github.com/zendframework/zf2/pull/6556

This app is distributed under GPL 3.0 licence, and anyone can use it for his (or her) needs as he sees fit. I'm not providing any means of warranty, and may or may not address the issues that can be created in GitHub repo.


### Tech
--------

Includes next major components from vendors:
- zend-db (zf 2.*) for database connection
- monolog (~1.0) for logging
- symfony/http-foundation (2.*) for http request handling
- symfony/dependency-injection (2.*) for DI
- symfony/form (2.*) for creating forms
- twig (~1) for templating


### License
---------

[GPL 3.0](https://www.gnu.org/licenses/gpl-faq.html)
