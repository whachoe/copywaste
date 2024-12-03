<?php

namespace CopyWaste;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Waste
{
    public $id;
    private $uploads;
    private $message;

    function __construct($id=0)
    {
        if ($id === 0)
            $id = bin2hex(random_bytes(16));

        $this->id = $id;
        $this->uploads = null;
        $this->message = "";
        $this->generateQRCode();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function save()
    {
        $dir = $this->getDir();

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        file_put_contents($dir . '/message.txt', $this->message);
    }

    public function addUpload($file)
    {
        $uploaddir = $this->getUploadDir();

        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0744, true);
        }

        $target = $uploaddir . '/' . $file->getClientFilename();
        $file->moveTo($target);

        // Change mtime of message because we use this mtime to determine if the waste is expired
        touch($this->getDir(). '/message.txt');

        return $target;
    }

    public function deleteUpload($filename)
    {
        $upload = $this->getUploadDir() . '/' . $filename;

        if (file_exists($upload)) {
            unlink($upload);
        }

        // Reload the upload-list
        return $this->getUploads(true);
    }

    public function getUploads($force_reload = false)
    {
        $uploaddir = $this->getUploadDir();

        if (!file_exists($uploaddir)) {
            return [];
        }

        // Lazy load uploads
        if (is_null($this->uploads) || $force_reload) {
            $this->uploads = [];
            $root = scandir($uploaddir, SCANDIR_SORT_ASCENDING);
            foreach ($root as $value) {
                if ($value === '.' || $value === '..') {
                    continue;
                }

                if (is_file("$uploaddir/$value")) {
                    $this->uploads[] = "$uploaddir/$value";
                    continue;
                }

                foreach (find_all_files("$uploaddir/$value") as $value) {
                    $this->uploads[] = $value;
                }
            }
        }

        return $this->uploads;
    }

    private function getDir()
    {
        return WASTE_ROOT_DIR . "/$this->id";
    }

    private function getUploadDir()
    {
        return $this->getDir() . '/uploads';
    }

    static function load($id): ?Waste
    {
        $dir = WASTE_ROOT_DIR . "/$id";

        if (!file_exists($dir)) {
            return null;
        }

        $waste = new Waste();
        $waste->id = $id;
        $waste->setMessage(file_get_contents($dir . '/message.txt'));
        $waste->getUploads();
        return $waste;
    }

    private function generateQRCode()
    {
        $filename = QRCODE_DIR . '/'.$this->getId().'.png';

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);
        $host = $_SERVER['HTTP_HOST'];
        $id = $this->getId();
        $writer->writeFile("https://$host/waste/$id", $filename);
    }
}