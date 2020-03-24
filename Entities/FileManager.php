<?php

namespace Modules\FileManager\Entities;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Intervention\Image\Facades\Image;

class FileManager
{
    protected $fillable = [];

    public static function upload($disk, $subPath, $config = [])
    {
        $rawFile = \Request::file('fileData');
        $fileName = time() . "_" . $rawFile->getClientOriginalName();

        if (!Storage::makeDirectory($subPath)) {
            return response()->json(['error' => "Unable to create directory. "], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fileUrl = $subPath . '/' . $fileName;

        $file = '';

        if (array_key_exists('resize', $config) &&  $config['resize']) {
            $file = Image::make($rawFile); // resize =true
            $size = intval(config('filemanager.image_resize', 1500));
            if ($file->width() > $size) {
                $file->widen($size);
            }
            if ($file->height() > $size) {
                $file->heighten($size);
            }
        }

        if (array_key_exists('watermark', $config) &&  $config['watermark']) {
            if (empty($file)) {
                $file = Image::make($rawFile);  //resize=false, watermark=true
            }
            $watermark_img = Image::make(config('filemanager.watermark_path'));
            $watermark_img->widen(intval($file->width() / 2));
            $watermark_img->opacity(50);
            $file->insert($watermark_img, 'center', 10, 10);
            $format = pathinfo($fileName, PATHINFO_EXTENSION);
            $file->encode($format);
        }

        if (empty($file)) {
            $file = File::get($rawFile);  //resize=false, watermark=false
        }

        $result = Storage::disk($disk)
            ->put($fileName, $file);

        if (!$result) {
            return response()->json(['error' => "Unable to upload file. "], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fileSize =  filesize($rawFile);
        return [
            'url' => Storage::disk($disk)->url($fileUrl),
            'file' => $fileUrl,
            'file_size' => $fileSize
        ];
    }


    public static function delete($disk, $file)
    {
        try {
            $deleted = Storage::delete($disk . $file);
            if (!$deleted) {
                return response()->json(['errors' => 'Request cannot be processed'], Response::HTTP_BAD_REQUEST);
            }
        } catch (Exception $e) {
            report($e);
            return response()->json(['errors' => 'Request cannot be processed'], Response::HTTP_BAD_REQUEST);
        }

        return $deleted ?: false;
    }
}
