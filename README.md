### Maketok site application [![Build Status](https://travis-ci.org/SlayerBirden/site.svg?branch=master)](https://travis-ci.org/SlayerBirden/site)
=========

About
---------

When approaching the creation of website we sometimes forget how exciting the process is. I wanted to create a site using best practices I know about the application building, and ended up with this.

The app is still in developmnent, but I'm making some progress and generally am pleased with how things are moving.

Site is bundled with different vendor packages ( which help a lot!). But I wanted to make something by myself as well. 

"self" code is bundled in Maketok namespace and consists of major areas:
- SubjectManager - Subscribers (yes for names we need to thank recently read BandOfFour)
- Installer. Most likely the biggest piece of code written in Maketok namespace. Aimed for installing DB DDL structure from config files.
- MVC (Front Controller). Created few routes and so far that's all I need.
- Few small Util classes (like StreamHandler and DirectoryHandler).
- Some refactoring of zendframework/db DDL package, which ended up as pull request: https://github.com/zendframework/zf2/pull/6556

This app is distributed under GPL 3.0 licence, and anyone can use it for his (or her) needs as he sees fit. I'm not providing any means of warranty, and may or may not address the issues that can be created in GitHub repo.(of course they won't be there, as this is hardly a kind of useful app)


### Tech
--------

Includes next components from vendors:
- zend-db (zf 2.*)
- zend-stdlib (zf 2.*)
- monolog (~1.0)
- phpunit (4.*)
- vfsStream (~1.0)
- symfony/http-foundation (2.*)
- symfony/dependency-injection (2.*)
- symfony/config (2.*)
- twig (~1)
- zend-uri (2.*)
- symfony/form (2.*)
- symfony/twig-bridge (2.*)
- symfony/validator (2.*)


### License
---------

[GPL 3.0](https://www.gnu.org/licenses/gpl-faq.html)
