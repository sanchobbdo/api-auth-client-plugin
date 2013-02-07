Guzzle Api Auth client plugin
=============================

[Guzzle](http://guzzlephp.org/) authentication plugin for mgomes' Ruby [api_auth](https://github.com/mgomes/api_auth) gem.

Installing
----------

Create a composer.json file in the project root:

```json
{
    "require": {
      "sanchobbdo/api-auth-client-plugin": "~1.0",
      "guzzle/guzzle": "~3.1"
    }
}
```

Then download composer.phar and run the install command:

```bash
curl -s http://getcomposer.org/installer | php && ./composer.phar install
```

Usage
-----

```php
<?php

// Include composer's autoload.
require 'vendor/autoload.php';

// Use required Guzzle classess.
use Guzzle\Http\Client;

// Use the api_auth plugin.
use SanchoBBDO\Guzzle\Plugin\ApiAuth\ApiAuthPlugin;

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

License
-------

Licensed under the [MIT License](http://opensource.org/licenses/MIT).
