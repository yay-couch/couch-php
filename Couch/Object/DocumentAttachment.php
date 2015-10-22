<?php
namespace Couch\Object;

class DocumentAttachment
{
    private $file, $fileName;
    private $data, $dataLength;
    private $contentType;

    public function __construct($file = null, $fileName = null) {
        if (!empty($file)) {
            $this->file = $file;
            if (!empty($fileName)) {
                $this->fileName = $fileName;
            } else {
                $this->fileName = basename($file);
            }
        }
    }
    public function __set($name, $value) {
        if (!property_exists($this, $name)) {
            throw new \Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }
        $this->{$name} = $value;
    }
    public function __get($name) {
        if (!property_exists($this, $name)) {
            throw new \Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }
        return $this->{$name};
    }

    public function toArray($encode = true) {
        $this->readFile($encode);

        $array = [];
        $array['data'] = $this->data;
        $array['content_type'] = $this->contentType;
        return $array;
    }

    public function toJson() {
        return json_encode($this->toArray());
    }

    public function readFile($encode = true) {
        $type = finfo_file(($info = finfo_open(FILEINFO_MIME_TYPE)), $this->file);
        finfo_close($info);
        if (!$type) {
            throw new Exception("Could not open file `{$this->file}`!");
        }
        $this->contentType = $type;

        $data = file_get_contents($this->file);
        if ($encode) {
            $this->data = base64_encode($data);
        } else {
            $this->data = $data;
        }
        $this->dataLength = strlen($data);
    }
}
