<?php


namespace app\models;


use RuntimeException;
use Nette\Http\FileUpload as FileUpload;

class UploadManager
{
    /**
     * @param $values
     *
     * @return bool|array
     */
    public static function UploadMultipleImages($values)
    {
        $filenames = array();
        $sanitizedFileNames = array();
        try {
            foreach ($values as $file) {
                /**
                 * @var FileUpload $file
                 */
                switch ($file->getImageFileExtension()) {
                    case"jpeg":
                        $ext = "jpg";
                        break;
                    default:
                        $ext = $file->getImageFileExtension();
                        break;
                }
                //Filename with extension
                array_push($filenames, ($filename = hash("sha256", $file->getTemporaryFile()) . "." . $ext));
                //File name with sanitized name
                array_push($sanitizedFileNames, $file->getSanitizedName());
                //Filename with target directory
                $filenameWDir = sprintf(
                    'images/fullView/%s',
                    $filename
                );

                if (!move_uploaded_file(
                    $file->getTemporaryFile(),
                    $filenameWDir
                )) {
                    throw new RuntimeException();
                }
                ImageManager::defaultImage($filenameWDir);
                ImageManager::makeThumbnail($filenameWDir);
            }
        } catch (RuntimeException $exception) {
            if (!empty($filenames)) {
                foreach ($filenames as $filename) {
                    unlink("images/fullView/" . $filename);
                    unlink("images/thumbnail/" . $filename);
                }
            }
            return false;
        }
        return ["filenames" => $filenames, "sanitizedFileNames" => $sanitizedFileNames];
    }

    public static function UploadSingleImage($file)
    {
        $filename = "";
        try {
            /**
             * @var FileUpload $file
             */
            switch ($file->getImageFileExtension()) {
                case"jpeg":
                    $ext = "jpg";
                    break;
                default:
                    $ext = $file->getImageFileExtension();
                    break;
            }
            //Filename
            $filename = hash("sha256", $file->getTemporaryFile()) . "." . $ext;
            //File name with sanitized name
            $sanitizedFileName = $file->getSanitizedName();
            //Filename with target directory
            $filenameWDir = sprintf(
                'images/fullView/%s',
                $filename
            );

            if (!move_uploaded_file(
                $file->getTemporaryFile(),
                $filenameWDir
            )) {
                throw new RuntimeException();
            }
            ImageManager::defaultImage($filenameWDir);
            ImageManager::makeThumbnail($filenameWDir);
            return ["filename" => $filename, "sanitizedFileName" => $sanitizedFileName];
        } catch (RuntimeException $exception) {
            @unlink("images/fullView/" . $filename);
            @unlink("images/thumbnail/" . $filename);
            return false;
        }
    }
}