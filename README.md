webservice extension
===================
curl extension for Yii2:

 - POST
 - GET
 - HEAD
 - PUT
 - DELETE

Requirements
------------
- PHP 5.4+
- Curl and php-curl installed


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```bash
php composer.phar require --prefer-dist opensooq/webservice "*"
```


Usage
-----
 - GET

```php
//Init webservice
$response = HttpClient::get($url,$params,$header,$op);
```

 - POST

```php
//Init webservice
$response = HttpClient::post($url,$params,$header,$op);
```
