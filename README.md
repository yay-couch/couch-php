##Couch##

Couch is a great tool that makes all interactions with your CouchDB server.


Before beginning;

- Set your autoloader properly or use composer
- Use PHP >= 5.4 (cos it uses traits)
- Handle errors with try/catch blocks

##Autoload##

```php
// composer
{"require": {"qeremy/couch": "dev-master"}}

// manual
$autoload = require('path/to/Couch/Autoload.php');
$autoload->register();
```

##Configuration##

Configuration is optional but you can provide all this options;

```php
/** client **/
// default=localhost
$config['host'] = 'couchdbhost';
// default=5984
$config['port'] = 1234;
// default=NULL
$config['username'] = 'couchdb_user';
// default=NULL
$config['password'] = '************';

/** agent **/
// default=5 (used in sock & curl)
$config['timeout'] = 10;
// default=1 (used in sock)
$config['blocking'] = 0;
```

##Usage##

######Couch Object######
```php
// init couch object
$couch = new Couch\Couch();
$couch->setConfig($config);
// or
$couch = new Couch\Couch(null, $config); // uses sock as http agent
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL);
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL, $config);
```

######Client Object######
```php
// used in Server and Database objects
$client = new Couch\Client($couch);
```

If you need any direct request for any reason, you can use the methods below.

```php
// direct request
$data = $client->request('GET /<DB>/_design/<DDOC>/_view/<DDOC>',
    $uriParams=['group' => true], $body=null, $headers=[])->getData();

// args
$uri       = '/<DB>';
$uriParams = ['param_name' => 'param_value'];
$headers   = ['X-Foo' => 'The foo!'];
$body      = null; // array or string etc

// shortcut methods that handle HEAD, GET, POST, PUT, COPY, DELETE
$client->head($uri, $uriParams, $headers);
$client->get($uri, $uriParams, $headers);
$client->post($uri, $uriParams, $body, $headers)
$client->put($uri, $uriParams, $body, $headers)
$client->copy($uri, $uriParams, $headers)
$client->delete($uri, $uriParams, $headers)
```
