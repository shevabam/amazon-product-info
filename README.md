# Amazon Product Information

Amazon Product Information is a PHP library for fetching simple products information from Amazon API.

Provides simple information about a product from its ASIN number.

The information returned is:

* product name
* access URL
* main image (different dimensions)
* price



## Requirements

You have to set up an [Amazon Associates](https://affiliate-program.amazon.com/) account that has been reviewed and received final acceptance in to the Amazon Associates Program.

You'll need an **access key**, **secret key**, and **partner tag** *(Tools > Product Advertising API)*.

More information [here](https://webservices.amazon.com/paapi5/documentation/register-for-pa-api.html).

Amazon Product Information requires PHP 8.0+.


## Installation

With Composer, run this command:

	composer require shevabam/amazon-product-info


It downloads the [Product Advertising API PHP SDK](https://github.com/thewirecutter/paapi5-php-sdk/) provided by [Amazon](https://webservices.amazon.com/paapi5/documentation/index.html).


## Usage

First, include the library in your code using the Composer autoloader and then create an AmazonProductInfo object with your Amazon Associates credentials.

```php
require 'vendor/autoload.php';

$Amz = new \AmazonProductInfo\AmazonProductInfo([
    'access_key'  => '',
    'secret_key'  => '',
    'partner_tag' => '',
    'lang'        => 'fr'
]);
```


### Locale

This library supports all [Product Advertising API locales](https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region).

Default: us.

| lang | country              |
|------|----------------------|
| au   | Australia            |
| br   | Brazil               |
| ca   | Canada               |
| fr   | France               |
| de   | Germany              |
| in   | India                |
| it   | Italy                |
| jp   | Japan                |
| mx   | Mexico               |
| nl   | Netherlands          |
| sg   | Singapore            |
| sa   | South Arabia         |
| es   | Spain                |
| sw   | Sweden               |
| tr   | Turkey               |
| ae   | United Arab Emirates |
| uk   | United Kingdom       |
| us   | United States        |


### Search item(s) by ASIN

You can search items with ASIN number (must be an array):

```php
$getResults = $Amz->searchByAsin(["B084J4MZK6", "B07ZZVWB4L"]);
```


## Full example

```php
require 'vendor/autoload.php';

$Amz = new \AmazonProductInfo\AmazonProductInfo([
    'access_key'  => '',
    'secret_key'  => '',
    'partner_tag' => '',
    'lang'        => 'fr'
]);

$getResults = $Amz->searchByAsin(["B084J4MZK6", "B07ZZVWB4L"]);
```

Result:

```
Array
(
    [error] => 
    [datas] => Array
    (
        [B084J4MZK6] => Array
        (
            [title] => Nouvel Echo Dot (4e génération), Enceinte connectée avec Alexa, Blanc
            [url] => https://www.amazon.fr/dp/B084J4MZK6?tag=¤¤&linkCode=ogi&th=1&psc=1
            [images] => Array
            (
                [primary] => Array
                (
                    [small] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/51Jb6AQdGcL._SL75_.jpg
                        [width] => 75
                        [height] => 75
                    )
                    [medium] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/51Jb6AQdGcL._SL160_.jpg
                        [width] => 160
                        [height] => 160
                    )
                    [large] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/51Jb6AQdGcL.jpg
                        [width] => 500
                        [height] => 500
                    )
                )
            )
            [price] => 29,99 €
        )
        [B07ZZVWB4L] => Array
        (
            [title] => Découvrez Fire TV Stick Lite avec télécommande vocale Alexa | Lite (sans boutons de contrôle de la TV), Streaming HD, Modèle 2020
            [url] => https://www.amazon.fr/dp/B07ZZVWB4L?tag=¤¤&linkCode=ogi&th=1&psc=1
            [images] => Array
            (
                [primary] => Array
                (
                    [small] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/318TG3aNKpL._SL75_.jpg
                        [width] => 75
                        [height] => 75
                    )
                    [medium] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/318TG3aNKpL._SL160_.jpg
                        [width] => 160
                        [height] => 160
                    )
                    [large] => Array
                    (
                        [url] => https://m.media-amazon.com/images/I/318TG3aNKpL.jpg
                        [width] => 500
                        [height] => 500
                    )
                )
            )
            [price] => 29,99 €
        )
    )
)
```
