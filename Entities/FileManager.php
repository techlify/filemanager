<?php

namespace Modules\FileManager\Entities;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class FileManager
{
    protected $fillable = [];

    public static function upload($disk, $subPath)
    {
        $rawFile = \Request::file('fileData');
        $fileName = time() . "_" . $rawFile->getClientOriginalName();

        if (!Storage::makeDirectory($subPath)) {
            return response()->json(['error' => "Unable to create directory. "], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fileUrl = $subPath . '/' . $fileName;

        $result = Storage::disk($disk)
            ->put($fileName, \Illuminate\Support\Facades\File::get($rawFile));

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
