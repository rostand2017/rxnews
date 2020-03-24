<?php
/**
 * Created by PhpStorm.
 * User: Ross
 * Date: 3/21/2020
 * Time: 9:48 AM
 */

namespace App\Services;


use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    const CATEGORY_TYPE = "category/big/";
    const CATEGORY_SMALL = "category/small/";
    const NEWS_TYPE = "news/big/";
    const NEWS_SMALL = "news/small/";

    private $parentDirectory;

    public function __construct(string $parentDirectory)
    {
        $this->parentDirectory = $parentDirectory;
    }

    public function upload(string $type, UploadedFile $file):string {
        $fileName = uniqid().'.'.$file->guessExtension();
        try {
            $file->move($this->getParentDirectory().$type, $fileName);
        } catch (FileException $e) {
            throw $e;
        }

        return $fileName;
    }

    public function deleteFile(string $type, string $fileName):string {
        return unlink($this->getParentDirectory().$type.$fileName);
    }

    public function getParentDirectory(): string {
        return $this->parentDirectory;
    }
}