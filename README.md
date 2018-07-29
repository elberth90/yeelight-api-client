yeelight-api-client
=======================
[![Build Status](https://travis-ci.org/elberth90/yeelight-api-client.svg?branch=master)](https://travis-ci.org/elberth90/yeelight-api-client)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/elberth90/yeelight-api-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/elberth90/yeelight-api-client/?branch=master)

Yeelight-api-client is a PHP client that makes it easy to manage and handle Yeelight bulbs.

Installation
------------
Installation is possible using [Composer](https://getcomposer.org/).

Then install the library:

    composer require elberth90/yeelight-api-client
    
Getting started
---------------
Create `YeelightClient` instance
```php
use Yeelight\YeelightClient;
$client = new \Yeelight\YeelightClient();
```

Search for bulbs in your local network
```php
$bulbList = $client->search();
```

Once you have list of available bulbs, you can perform on each bulb some actions like for example getting property 
of each bulb
```php
foreach ($bulbList as $bulb) {
    $promise = $bulb->getProp([\Yeelight\Bulb\BulbProperties::COLOR_TEMPERATURE]);
}
```

Each action performed on bulb return [Promise](https://github.com/reactphp/promise), so you can perform on it 
`then()` or `done()` operation.
```php
$promise->done(function (\Yeelight\Bulb\Response $response) {
    // do something with response
}, function (\Yeelight\Bulb\Exceptions\Exception $exception) {
    // log exception or whatever...
});
```

Full documentation for an API can be found [here](http://www.yeelight.com/download/Yeelight_Inter-Operation_Spec.pdf)
    
Contributing
------------

See [CONTRIBUTING.md](CONTRIBUTING.md) for more information about contributing and developing yeelight-api-client.
