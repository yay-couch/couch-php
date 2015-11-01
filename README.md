##Couch##

Couch is a great library that makes all interactions with your CouchDB server providing a lot of tools for that.

Before beginning;

- Set your autoloader properly or use composer
- Use PHP >= 5.4 (cos it uses traits)
- Handle errors with try/catch blocks

Notice: See CouchDB's official documents before using this library.

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

#####Couch Object#####
```php
// init couch object
$couch = new Couch\Couch();
$couch->setConfig($config);
// or
$couch = new Couch\Couch(null, $config); // uses sock as http agent
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL);
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL, $config);
```

#####Client Object#####
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
$client->copy($uri, $uriParams, $headers);
$client->delete($uri, $uriParams, $headers);
// with body
$client->put($uri, $uriParams, $body, $headers);
$client->post($uri, $uriParams, $body, $headers);

// after request operations
$request  = $client->getRequest();
$response = $client->getResponse();
```

#####Server Object#####
```php
$server = new Couch\Server($client);

// methods
dump $server->ping();
dump $server->info();
dump $server->version();
dump $server->getActiveTasks();
dump $server->getAllDatabases();
dump $server->getDatabaseUpdates();
dump $server->getLogs();
dump $server->replicate(['source' => 'foo', 'target' => 'foo2', 'create_target' => true]);
dump $server->restart();
dump $server->getStats();
dump $server->getStats('/couchdb/request_time');
dump $server->getUuid(3);
dump $server->getConfig();
dump $server->getConfig('couchdb');
dump $server->getConfig('couchdb', 'uuid');
dump $server->setConfig('couchdb', 'foo', 'the foo!');
dump $server->removeConfig('couchdb', 'foo');
```

####Database Object####
```php
$db = new Couch\Database($client, 'foo');

// db methods
dump $db->ping();
dump $db->info();
dump $db->create();
dump $db->remove();
dump $db->replicate('foo2');
dump $db->getChanges();
dump $db->getChanges(null, ['e90636c398458a9d5969d2e71b04b0a4']);
dump $db->compact();
dump $db->ensureFullCommit();
dump $db->viewCleanup();
dump $db->getSecurity();
dump $db->setSecurity(['names' => ['superuser'], 'roles' => ['admins']],
                      ['names' => ['user1', 'user2'], 'roles' => ['developers']]);

dump $db->getRevisionLimit();
dump $db->setRevisionLimit(1000);

/** tmp view method  */
$db->viewTemp('function(doc){ if(doc.name){ emit(doc.name, null); }}');
$db->viewTemp('function(doc){ if(doc.name){ emit(doc.name, null); }}', $reduce='_count');

/** document methods  */

$db->purge('test_3', ['4-53348db493c7323e9d539e77df4fe3af']);
$db->getMissingRevisions('test_3',
    ['3-b06fcd1c1c9e0ec7c480ee8aa467bf3b', '3-0e871ef78849b0c206091f1a7af6ec41']);
$db->getMissingRevisionsDiff('a0ecb3e2bc442e7bd768ea78070349da',
    ['4-265c7f224875a6da3aa4ba79d01ee0b0']);

// get a document
dump $db->getDocument('667b0208441066a0954717b50c0008a9');
// get all documents
dump $db->getDocumentAll();
// get all documents by keys
dump $db->getDocumentAll($query=null,
    ['667b0208441066a0954717b50c0008a9','83b5e0a0b3bd41d9a21cee7ae8000615']);

// create a document
$doc = new Couch\Document();
$doc->test = 'the test 20';
// param as Couch\Document
dump $db->createDocument($doc);
// param as array
dump $db->createDocument(['test' => 'test 30']);

// update a document
$doc = new Couch\Document();
$doc->_id = 'e90636c398458a9d5969d2e71b04ad81';
$doc->_rev = '3-9aeefae43b9fad5df8cc87fe8bcc2718';
// param as Couch\Document
dump $db->updateDocument($doc);
// param as array
dump $db->updateDocument([
    ['_id'  => 'e90636c398458a9d5969d2e71b04b0a4',
     '_rev' => '1-afa338dcbc6870f1a1dd441557f79859',
     'test' => 'test 2 (update)']
]);

// delete a document
$doc = new Couch\Document(null, [
    '_id'  => 'e90636c398458a9d5969d2e71b04b0a4',
    '_rev' => '2-d4ef449903f67ee5559f1ee42bafcfcf',
]);
dump $db->deleteDocument($doc);

/** multiple CRUD */

$docs = [];
// all accepted, just fill the doc data
$docs[] = [/* doc data id etc (and rev for updade/delete) */];
$docs[] = new Couch\Document(null, [/* doc data id etc (and rev for updade/delete) */]);
$doc = new Couch\Document(); $doc->foo = ...
$docs[] = $doc;
$doc = new stdClass;         $doc->foo = ...
$docs[] = $doc;

// multiple create
dump $db->createDocumentAll($docs);
// multiple update
dump $db->updateDocumentAll($docs);
// multiple delete
dump $db->deleteDocumentAll($docs);
```
