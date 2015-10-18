<?php
header('Content-Type: text/plain; charset=utf-8');

// simple dump functions
function pre($input, $exit = false){
    printf("%s\n", print_r($input, true));
    if ($exit) {
        exit;
    }
}
function prd($input, $exit = false){
    var_dump($input);
    if ($exit) {
        exit;
    }
}

// simple autoload
spl_autoload_register(function($name) {
    require(sprintf('%s/../%s.php', __dir__, ltrim(str_replace('\\', '/', $name), '/')));
});
