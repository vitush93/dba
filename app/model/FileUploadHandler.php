<?php

namespace App\Model;

use Nette\Http\FileUpload;
use Nette\Object;

class FileUploadHandler extends Object
{

    /**
     * Get hardcoded upload directory.
     *
     * @return string
     */
    private static function getUploadDir()
    {
        return WWW_DIR . '/upload/';
    }

    /**
     * Process form file upload from FileUpload wrapper class.
     *
     * @param FileUpload $file
     * @return string
     */
    public static function upload(FileUpload $file)
    {
        $filename = $file->getSanitizedName();
        $folder = date('Y');

        $file->move(self::getUploadDir() . $folder . '/' . $filename);

        return $folder . '/' . $filename;
    }

    /**
     * Delete file by its name (including path after www/).
     *
     * @param string $filename filename from database (returned from FileUploadHandler.upload() method)
     */
    public static function delete($filename)
    {
        $path = self::getUploadDir() . $filename;
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }
    }

    /**
     * @param string $old_filename path to old file
     * @param FileUpload $new_file new file uploaded via Nette Form.
     * @return string
     */
    public static function replace($old_filename, FileUpload $new_file)
    {
        $new = self::upload($new_file);
        self::delete($old_filename);

        return $new;
    }

}