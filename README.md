WebService extension
====================

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

Either run
```bash
php composer.phar require --prefer-dist opensooq/webservice
```

or add
```php
"opensooq/web-service": "*"
```

Usage
-----
 - GET

```php
//Create GET request
$url = 'https://apilayer.net/api/convert';
$getParams = ['from'=>'USD','to'=>'EUR','amount'=>1];
$options   = [CURLOPT_TIMEOUT=>40];
$headers   = ['Content-Type'=>'application/json'];

// GET
$response   = HttpClient::get($url, $getParams, $headers,$options);
```
```php
OUTPUT:
Array
(
    [info] => Array
        (
            [code] => 200
            [Content-Type] => application/json; Charset=UTF-8
            [curl-error] =>
            [curl-header] => GET /api/convert?from=USD&to=EUR&amount=1 HTTP/1.1
                            Host: apilayer.net
                            User-Agent: OpenSooqClient 1.0
                            Accept: */*
                            Content-Type: application/json
                            Content-Length: 2


        )

    [response] => stdClass Object
        (
            [success] =>
            [error] => stdClass Object
                (
                    [code] => 101
                    [type] => missing_access_key
                    [info] => You have not supplied an API Access Key. [Required format: access_key=YOUR_ACCESS_KEY]
                )

        )

)
```
 - POST

```php
// Make sure you fill
$response = HttpClient::post($url,$postParams,$getParams, $headers, $options)
```

- General Request
```php
//Init webservice
$verb = 'PUT';
$response = HttpClient::request($verb,$url,$getParams,null, $headers, $options)
```
