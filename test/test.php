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

$db = new Couch\Object\Database($client, 'foo');
// prd($db->ping());
// pre($db->info());
// prd($db->create());
// prd($db->remove());
// pre($db->replicate('foo2'));

// 667b0208441066a0954717b50c0008a9 83b5e0a0b3bd41d9a21cee7ae8000615
// pre($db->getDocument('667b0208441066a0954717b50c0008a9'));
// pre($db->getDocumentAll());
// pre($db->getDocumentAll(null, ['667b0208441066a0954717b50c0008a9','83b5e0a0b3bd41d9a21cee7ae8000615']));
// $offset = 0; $limit = 1; $skip = 0;
// pre($db->getDocumentAll(['offset' => $offset, 'limit' => $limit, 'skip' => $skip]));
// pre($skip = Couch\Util\Util::getSkip($offset, $limit));
// ++$offset; // and continue


// $doc = new Couch\Object\Document();
// $doc->test = 'the test 2';
// pre($db->createDocument($doc));
// pre($db->createDocument(['test' => 'test 3']));
// pre($db->createDocumentAll([
//     ['test' => 'test 4'],
//     new Couch\Object\Document(null, ['test' => 'the test 5']),
// ]));

// $doc = new Couch\Object\Document();
// $doc->_id = 'e90636c398458a9d5969d2e71b04ad81';
// $doc->_rev = '3-9aeefae43b9fad5df8cc87fe8bcc2718';
// pre($db->updateDocument($doc));
// pre($db->updateDocumentAll([
//     ['_id' => 'e90636c398458a9d5969d2e71b04b0a4',
//      '_rev' => '1-afa338dcbc6870f1a1dd441557f79859',
//      'test' => 'test 2 (update)'],
//     new Couch\Object\Document(null, [
//         '_id' => 'e90636c398458a9d5969d2e71b04b2e4',
//         '_rev' => '1-186677ba2134699278e769e075f772f6',
//         'test' => 'the test 3 (update)']),
// ]));

// $doc = new Couch\Object\Document(null, [
//     '_id' => 'e90636c398458a9d5969d2e71b04b0a4',
//     '_rev' => '2-d4ef449903f67ee5559f1ee42bafcfcf',
// ]);
// $db->deleteDocument($doc);

// pre($db->getChanges());
// pre($db->getChanges(null, ['e90636c398458a9d5969d2e71b04b0a4']));

// pre($db->compact());
// pre($db->ensureFullCommit());
// pre($db->viewCleanup());
// pre($db->getSecurity());
// pre($db->setSecurity(['names' => ['superuser'], 'roles' => ['admins']],
//                      ['names' => ['user1', 'user2'], 'roles' => ['developers']]));

$db = new Couch\Object\Database($client, 'foo2');
// pre($db->getChanges());

// $db->viewTemp('function(doc){ if(doc.name) { emit(doc.name, null); }}');
// $db->purge('test_3', ['4-53348db493c7323e9d539e77df4fe3af']);
// $db->getMissingRevisions('test_3', ['3-b06fcd1c1c9e0ec7c480ee8aa467bf3b', '3-0e871ef78849b0c206091f1a7af6ec41']);
// $db->getMissingRevisionsDiff('a0ecb3e2bc442e7bd768ea78070349da', ['4-265c7f224875a6da3aa4ba79d01ee0b0']);

// pre($db->getRevisionLimit());
// pre($db->setRevisionLimit(1001));

// $doc = new Couch\Object\Document($db);
// $doc->_id = 'e90636c398458a9d5969d2e71b04b2e4';
// $doc->_rev = '2-393dbbc2cca7eea546a3c750ebeddd70';
// prd($doc->ping());
// prd($doc->isExists());
// prd($doc->isNotModified());
// pre($doc->get());

$doc = new Couch\Object\Document($db);
$doc->_id = 'test';
pre($doc);
