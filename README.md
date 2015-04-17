Maketok site application
------------------------

master
[![Build Status](https://travis-ci.org/SlayerBirden/site.svg?branch=master)](https://travis-ci.org/SlayerBirden/site)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4edbf1c9-a4ff-4e8f-868f-05a22af434d8/mini.png)](https://insight.sensiolabs.com/projects/4edbf1c9-a4ff-4e8f-868f-05a22af434d8)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=master)
[![Latest Unstable Version](https://poser.pugx.org/maketok/site/v/unstable.svg)](https://packagist.org/packages/maketok/site) 
[![License](https://poser.pugx.org/maketok/site/license.svg)](https://packagist.org/packages/maketok/site)

dev
[![Build Status](https://travis-ci.org/SlayerBirden/site.svg?branch=dev)](https://travis-ci.org/SlayerBirden/site)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=dev)
[![Code Coverage](https://scrutinizer-ci.com/g/SlayerBirden/site/badges/coverage.png?b=dev)](https://scrutinizer-ci.com/g/SlayerBirden/site/?branch=dev)

About
-----

A package is aimed to provide a simple platform for site developer.
It includes independent components (bundles) from major vendors to handle low level operations.

This should be considered experimental until any stable release is out. So I wouldn't recommend it for any production instance.

App code is bundled in Maketok namespace and consists of major areas:
- **SubjectManager**. The most standard implementation.
- **Installer**. Consists of DDL and Data parts.
    - **DDL**. This is component gathers Data Structure map across all subscribers (clients) and creates appropriate directives for concrete Data Storage. It also handles conflicts and dependencies across clients.
    - **Data**. The Data part is not yet implemented. I should handle clients that update data inside the built Structure.
- **MVC** (Front Controller). Provides routing and helper classes for controllers.
- Few small **Util** classes
    - **StreamHandler** - basic IO operations with files
    - **DirectoryHandler** - basic IO operations with directories
    - **ExpressionParser** - parse variables in curly brackets
    - **ClosureComparer** - compare closures
    - **CallableHash** - gets static hash of any callable
    - **ArrayValueTrait** - safely gets the value from an array by key
    - **ConfigGetter** - loads PHP and YAML configs in given paths
    - **PriorityQueue** - wrapper around SPL \PriorityQueue to provide removal functional

This app is distributed under MIT licence, and anyone can use it for his (or her) needs as he sees fit. I'm not providing any means of warranty, and may or may not address any issues programmatic or of any other sort that are connected with or caused by the software.

Tech
----

Includes next major components from vendors:
- zend-db (zf 2.*) for database connection/model handling
- monolog (~1.0) for logging
- symfony/http-foundation (2.*) for http request handling
- symfony/dependency-injection (2.*) for ioc container
- symfony/form (2.*) for creating forms
- twig (~1) for templating


License
-------

[MIT](http://opensource.org/licenses/MIT)


Example Nginx Configuration
-------

```

server {

    listen	80;
    listen	443 ssl;
    
    ssl_certificate /PATH/TO/CERTS/server.crt;
    ssl_certificate_key /PATH/TO/CERTS/server.key;
    
    server_name SERVER_NAME;

    index index.php index.html index.htm;
    set $root_path '/PATH/TO/MAKETOK/ROOT/public';
    root $root_path;

    try_files $uri $uri/ @rewrite;

    location /admin {
        rewrite ^/(.*)$ /admin/index.php?_url=/$1 last;
    }
    
    location @rewrite {
        rewrite ^/(.*)$ /index.php?_url=/$1;
    }

    location ~ \.php {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index /index.php;
        fastcgi_read_timeout 86400;

        include /etc/nginx/fastcgi_params;

        fastcgi_split_path_info       ^(.+\.php)(/.+)$;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* ^/(css|img|js|flv|swf|download)/(.+)$ {
        root $root_path;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
