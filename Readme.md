# JSON FEED IMPORTER FOR OFFERS

[Demo Link](http://yadi.yunlishuju.com/)

Import json feed and parse to a list of offers and their attributes. Each row in the list must contain the offer name, its image and the cashback value in dollars.

## Getting Started

This project is for demo purpose, and technologies involve:

 ```
 php framework: symfony (components including: mvc, form, dependency injections, console command)

 third party bundles: KnpPaginatorBundle (handle pagination) 

 orm framework: doctrine orm, pessimistic locking, migration 

 message queue: symfony messager (memory transport dsn used by default)
 
 websocket: ratchet (localhost used by default)

 phpunit: demonstrate functional test, web test with test database
 ```

## Design


Import feed options:

```
Skip Error: Import process skips error offer data (offerId is numeric and > 0, cash back is > 0)
Force Update: Update existing offer data if offerId exists.
```

Import Process:

```
Small File: Import process happens right away
Large File: Import process happens on backend and will notify user after it's completed
Concurrency: Import concurrency is implemented with pessimistic locking
```

Small file import process:

```
1. add feed url 
2. feed is downloaded to server
3. import process starts for local file
4. imported and show offer list
```


Large file import process:

```
1. add feed url 
2. feed is downloading on message queue
3. feed is downloaded and notify user by websocket
4. start import process
5. import process starts for local file on message queue
6. imported and notify user by websocket
```

## Implementation

### Prerequisites

```
php: 7.3 + 
mysql: 5.5+
composer: 1.9+
apache: 2.0+
```

### Installing on unix (ubuntu, mac os, centos, etc.)

Quick run (Handle small feed file < 5000 bytes )

```
1. cp .env.dist to .env
2. replace DATABASE_URL to correct info
3. composer install
4. chmod -R 777 var && chmod -R 777 public/upload
5. create database tables
   php bin/console doctrine:migrations:migrate
6. symfony server:start
7. check http://127.0.0.1:8000/
```

Message Queue & Websocket Support (Handle large feed file )


```
1. start websocket server
php bin/console websocket-server

2. start message queue (memory)
php bin/console messenger:consume async

NOTICE: these scripts should be a deamon service, and for unix supervisor is recommanded
```


## Running the tests

Phpunit is used for functional test and web test

### Unit Test

```
1. Entity: test basic logic
2. Repository: test database queries
3. Service: test business logics
```

### Web Test

```
1. Controller: test looks and feel with xpath parser by symfony

OPTIMIZATION: codeception should be used to support js
```

### Run Tests

```
1. cp phpunit.xml.dist to .phpunit.xml
2. replace DATABASE_URL to test database info
3. ./vendor/bin/phpunit tests
```

## Deployment

Apache configuration

```
<VirtualHost *:80>
    ServerName [domain]
    ServerAlias [alias]
    DocumentRoot [project path]

    <Directory [project path]>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/[error log path]
    CustomLog ${APACHE_LOG_DIR}/[access log path] combined
</VirtualHost>
```

Enable Apache URL Rewrite

```
 composer require symfony/apache-pack
```



