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
