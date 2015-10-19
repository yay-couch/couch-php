<?php include('test.inc.php');

// define('COUCH_HOST', 'localhost');
// define('COUCH_PORT', 5984);
// define('COUCH_USERNAME', '');
// define('COUCH_PASSWORD', '');

$couch = new Couch\Couch();
// pre($couch);
$client = new Couch\Client($couch);
// pre($client);

// $r = $client->request('GET /');
// pre($r);

$server = new Couch\Object\Server($client);
// prd($server->ping());
// pre($server->info());
// pre($server->version());
// pre($server->getActiveTasks());
// pre($server->getAllDatabases());
// pre($server->getDatabaseUpdates());
// pre($server->getLogs());
// pre($server->replicate(['source' => 'foo', 'target' => 'foo_replica', 'create_target' => true]));
// prd($server->restart());
// pre($server->getStats());
// pre($server->getStats('/couchdb/request_time'));
// pre($server->getUuid(3));
// pre($server->getConfig());
// pre($server->getConfig('couchdb'));
// pre($server->getConfig('couchdb', 'uuid'));
// prd($server->setConfig('couchdb', 'foo', 'the foo!'));
// prd($server->removeConfig('couchdb', 'foo'));

$database = new Couch\Object\Database($client, 'foo');
// prd($database->ping());
// pre($database->info());
// prd($database->create());
// prd($database->remove());

// 667b0208441066a0954717b50c0008a9 83b5e0a0b3bd41d9a21cee7ae8000615
// pre($database->getDocument('667b0208441066a0954717b50c0008a9'));
// pre($database->getDocumentAll());
// pre($database->getDocumentAll(null, ['667b0208441066a0954717b50c0008a9','83b5e0a0b3bd41d9a21cee7ae8000615']));
// $offset = 0; $limit = 1; $skip = 0;
// pre($database->getDocumentAll(['offset' => $offset, 'limit' => $limit, 'skip' => $skip]));
// pre($skip = Couch\Util\Util::getSkip($offset, $limit));
// ++$offset; // and continue


// $document = new Couch\Object\Document();
// $document->test = 'the test 2';
// pre($database->createDocument($document));
// pre($database->createDocument(['test' => 'test 3']));
// pre($database->createDocumentAll([
//     ['test' => 'test 4'],
//     new Couch\Object\Document(null, null, ['test' => 'the test 5']),
// ]));
