# IMDB Engine

[![Version](https://img.shields.io/badge/latest-1.0.5-green.svg)](https://github.com/aalfiann/imdb-engine)
[![Total Downloads](https://poser.pugx.org/aalfiann/imdb-engine/downloads)](https://packagist.org/packages/aalfiann/imdb-engine)
[![License](https://poser.pugx.org/aalfiann/imdb-engine/license)](https://github.com/aalfiann/imdb-engine/blob/HEAD/LICENSE.md)

An IMDB Scrapper like you have your own imdb engine.

*This script is a proof of concept. It’s working, but you shouldn’t use it. IMDb doesn’t allow this method of data fetching. I do not use or promote this script. You’re responsible for using it.*

The technique used is called “web scraping.” Which means, if IMDb changes any of their HTML, the script is going to fail. I won’t update this on a regular basis, so don’t count on it to be working all the time.

## Installation

Install this package via [Composer](https://getcomposer.org/).
```
composer require "aalfiann/imdb-engine:^1.0"
```

## Get Movies by ID

```php
require_once ('vendor/autoload.php');
use \aalfiann\IMDB;

$imdb = new IMDB();
$imdb->query    = 'tt3829266';      // Movie title {required}
echo var_dump($imdb->find()->getMovie());
```

Make output into JSON
```php
header('Content-Type: application/json');
echo $imdb->find()->getMovieJson(JSON_PRETTY_PRINT);
```

## Search Movies

```php
require_once ('vendor/autoload.php');
use \aalfiann\IMDB;

$imdb = new IMDB();
$imdb->query    = 'The Predator';   // Movie title {not required}. If blank then will show the new popular movies. The title must be don't include year or only conjunctions.
$imdb->genres   = '';               // You can filter by input multiple genres with commas separated
$imdb->userid   = '';               // You can filter by input user id. Ex. Jackie Chan user id is >> nm0000329
$imdb->page     = 1;                // Page number for navigation in many results.
$imdb->itemsperpage = 50;           // You can change this with 50, 100 and 250. Default is 50.
echo var_dump($imdb->search()->getList());
```

Make output into JSON
```php
header('Content-Type: application/json');
echo $imdb->search()->getListJson(JSON_PRETTY_PRINT);
```

## Search Artist to get user id

```php
require_once ('vendor/autoload.php');
use \aalfiann\IMDB;

$imdb = new IMDB();
$imdb->query = 'Audrey Hepburn';    // Artist name {required}
$imdb->start = 1;                   // Start number for navigation in many results. 
$imdb->itemsperpage = 50;           // You can change this with 50, 100 and 250. Default is 50.
echo var_dump($imdb->searchArtist()->getArtistList());
```

Make output into JSON
```php
header('Content-Type: application/json');
echo $imdb->searchArtist()->getArtistListJson(JSON_PRETTY_PRINT);
```

## Use Proxy

If your ip address has blocked by IMDB, you can make request with proxy.  
Just add this line:
```php
$imdb->proxy = '12.62.65.30:8124';  // IP:Port server proxy
$imdb->proxyauth = 'user:pass';     // Proxy authentication if any
```