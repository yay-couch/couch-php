## Couch

Couch is a great library that makes all interactions with your CouchDB server providing a lot of tools for that.

Before beginning;

- Set your autoloader properly or use composer
- Use PHP >= 5.4 (cos it uses traits)
- Handle errors with try/catch blocks
- On README, `dump` means `var_dump`

Notice: See CouchDB's official documents before using this library.

## In a Nutshell
```php
// create a fresh document
$doc = new Couch\Document($db);
$doc->name = 'The Doc!';
$doc->save();

// append an attachment to the same document above
$doc->setAttachment(new Couch\DocumentAttachment($doc, './file.txt'));
$doc->save();
```

## Autoload

```php
// composer
{"require": {"yay-couch/couch": "dev-master"}}

// manual
$autoload = include('./vendor/yay-couch/couch/Couch/Autoload.php');
$autoload->register();
```

## Configuration

Configuration is optional but you can provide all these options;

```php
/** client **/
// default=localhost
$config['host'] = 'couchdb_host';
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

## Objects

### Couch Object
```php
// init couch object
$couch = new Couch\Couch();
$couch->setConfig($config);
// or
$couch = new Couch\Couch(null, $config); // uses sock as http agent
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL);
$couch = new Couch\Couch(Couch\Couch::HTTP_AGENT_CURL, $config);
```

### Client Object
```php
// used in Server and Database objects
$client = new Couch\Client($couch);
```

If you need any direct request for any reason, you can use the methods below.

```php
// direct request
$data = $client->request('GET /<URI>',
   $uriParams=['group' => true], $body=null, $headers=[])->getData();

// args
$uri       = '/<URI>';
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

### Server Object
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

### Database Object
```php
$db = new Couch\Database($client, 'foo');

// db methods
dump $db->ping();
dump $db->info();
dump $db->create();
dump $db->remove();
dump $db->replicate('foo2');
dump $db->getChanges();
dump $db->getChanges(null, ['abc']);
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

$db->purge('test_3', ['4-abc']);
$db->getMissingRevisions('test_3', ['3-abc', '3-abd']);
$db->getMissingRevisionsDiff('abc', ['4-abc']);

// get a document
dump $db->getDocument('abc');
// get all documents
dump $db->getDocumentAll();
// get all documents by keys
dump $db->getDocumentAll($query=null, ['abc','abd']);

// create a document
$doc = new Couch\Document();
$doc->test = 'the test 20';
// param as Couch\Document
dump $db->createDocument($doc);
// param as array
dump $db->createDocument(['test' => 'test 30']);

// update a document
$doc = new Couch\Document();
$doc->_id = 'abc';
$doc->_rev = '3-abc';
// param as Couch\Document
dump $db->updateDocument($doc);
// param as array
dump $db->updateDocument([
   ['_id'  => 'abc',
    '_rev' => '1-abc',
    'test' => 'test 2 (update)']
]);

// delete a document
$doc = new Couch\Document(null, [
   '_id'  => 'abc',
   '_rev' => '2-abc',
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

### Document Object
```php
$doc = new Couch\Document($db);
// set props (so data)
$doc->_id = 'abc';
$doc->_rev = '2-abc';

// checker methods
dump $doc->ping();
dump $doc->isExists();
dump $doc->isNotModified();

// CRUD methods
dump $doc->find();
dump $doc->save(); // create or update
dump $doc->remove();

// copies
dump $doc->copy('test_copy3');
dump $doc->copyFrom('test_copy3_1');
dump $doc->copyTo('test_copy3_1', '1-abc');

// delete
dump $doc->remove();

// find revisions
dump $doc->findRevisions();
dump $doc->findRevisionsExtended();

// find attachments
dump $doc->findAttachments();
dump $doc->findAttachments(true, ['2-abc']);

// add attachments
$doc->_attachments = [['file' => './attc1.txt']];
$doc->_attachments = [['file' => './attc1.txt', 'file_name' => 'attc1']];
// or
$doc->setAttachment(['file' => './attc1.txt', 'file_name' => 'attc1']);
dump $doc->save();

// to json
dump json_encode($doc);
```

### DocumentAttachment Object
```php
$attc = new Couch\DocumentAttachment($doc);

// ping attachment
dump $attc->ping();

// find an attachment
$attc->fileName = 'attc_1';
dump $attc->find();

// find an attachment by digest
$attc->fileName = 'attc_1';
$attc->digest   = 'U1p5BLvdnOZVRyR6YrXBoQ==';
dump $attc->find();

// add an attachment to document
$attc->file     = 'attc.txt';
$attc->fileName = 'attc_2';
dump $attc->save();

// remove an attachment from document
$attc->fileName = 'attc_2';
dump $attc->remove();

// to json
dump $attc->toJson();
dump json_encode($attc);
```

### DocumentDesign Object
```php
// @todo
```

## Uuid
```php
// create uuid
$uuid = new Couch\Uuid('docid'); // set given value
$uuid = new Couch\Uuid(true);    // auto-generate randomly using mcrypt
$uuid = new Couch\Uuid($server); // triggers $server->getUuid() method

// also setValue & getValue methods available
$uuid = new Couch\Uuid();
$uuid->setValue(...);

// print
print $uuid;

// generate method
$uuidValue = Couch\Uuid::generate(
   $method=Couch\Uuid::METHOD_RANDOM, $algo=Couch\Uuid::HASH_ALGO_MD5);

// available methods
METHOD_RANDOM // default
METHOD_TIMESTAMP
METHOD_TIMESTAMP_HEXED

// available algos (also you can provide any valid "hash algo")
HASH_ALGO_MD5 // default
HASH_ALGO_SHA1
HASH_ALGO_CRC32B
```

## Query
```php
// init query with db
$query = new Couch\Query($db);
// or
$query->setDatabase($db);

// add params
$query->set('conflicts', true)
   ->set('stale', 'ok')
   ->skip(1)
   ->limit(2)
;

// get as string
dump $query; // print
dump $query->toString();

// actually run is only to get documents with given query
dump $query->run();
// as same as
dump $db->getDocumentAll($query);
```

## Request / Response
```php
// after any http stream (server ping, database ping, document save etc)

// ie.
$client->request('GET /');

// get raw stuffs
dump $client->getRequest()->toString();
dump $client->getResponse()->toString();

/*
GET / HTTP/1.0
Host: localhost:5984
Connection: close
Accept: application/json
Content-Type: application/json
User-Agent: Couch/v1.0.4 (+http://github.com/yay-couch/couch-php)

HTTP/1.0 200 OK
Server: CouchDB/1.5.0 (Erlang OTP/R16B03)
Date: Sun, 01 Nov 2015 18:04:42 GMT
Content-Type: application/json
Content-Length: 127
Cache-Control: must-revalidate

{"couchdb":"Welcome","uuid":"5a660f4695a5fa9ab2cd22722bc01e96", ...
*/

// get response body
dump $client->getResponse()->getBody();

// get response data
dump $client->getResponse()->getData();
dump $client->getResponse()->getData('vendor');
```

## Error Handling

Couch will not throw any server response error, such as `409 Conflict` etc. It only throws library-related errors ie. "timeout" or wrong usages of the library (ie. when `_id` is required for some action but you did not provide it).

```php
// create issue
$doc = new Couch\Document($db);
$doc->_id = 'an_existing_docid';

// no error will be thrown
$doc->save();

// but could be so
if (201 != $client->getResponse()->getStatusCode()) {
   print 'nö!';
   // or print response error data
   $data = $client->getResponse()->getData();
   print $data['error'];
   print $data['reason'];
}

// this will throw error ie. timed out
$db = new Couch\Database(
       new Couch\Client(
         new Couch\Couch(null, ['timeout' => 0])), 'foo2');

try {
   $db->ping();
} catch (Couch\Http\Exception $e) {
   print $e->getMessage();
}
```
