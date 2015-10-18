<?php
namespace Couch\Http;

abstract class Stream
{
    protected $body;
    protected $headers = [];

    abstract public function setBody($body);
    abstract public function setHeader($key, $value);
}
