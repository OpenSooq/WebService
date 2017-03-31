webservice extension
===================

This is extension for Yii Framework (http://www.yiiframework.com/), which can easy add RESTful API to existing web application.

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
 - POST

```php
//Init webservice
$webservice = new Webservice();
$response = $webservice->setData(['myPostField' => 'value'])->post('http://example.com/');

 - GET
 
```php
//Init webservice
$webservice = new Webservice();
$response = $webservice->setData(['myPostField' => 'value'])->get('http://example.com/');
