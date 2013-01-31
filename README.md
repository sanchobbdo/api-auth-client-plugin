Guzzle Api Auth client plugin
=============================

[Guzzle](http://guzzlephp.org/) authentication plugin for Ruby's [api_auth](https://github.com/mgomes/api_auth) gem.

Usage
-----

```php
<?php

// Include composer's autoload.
require '../vendor/autoload.php';

// Use required Guzzle classess.
use Guzzle\Http\Client;

// Use the api_auth plugin.
use Sancho\Guzzle\Plugin\ApiAuth\ApiAuthPlugin;

// Set up the Guzzle Client.
$client = new Client('http://your-host.com');

// Set up the api_auth plugin. Include your access id and secret key.
$apiAuthPlugin = new ApiAuthPlugin(array(
    'accessId' =>  'your-access-id',
    'secretKey' =>  'your-secret-key'
));

// Subscribe the plugin to client's events.
$client->addSubscriber($apiAuthPlugin);

// Enjoy!
```
