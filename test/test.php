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

$server = $client->serverObject();
prd($server->ping());
pre($server->info());





// $s = fsockopen('ssl://localhost', 5984, $ec, $et);
// prd(error_get_last());
// prd($s);
// prd($ec);
// prd($et);
// @fclose($s);
