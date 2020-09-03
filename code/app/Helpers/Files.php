<?php

function localFilePath($obj, $field)
{
    if (!empty($obj->$field)) {
        $path = gas_storage_path($obj->$field);
        if (file_exists($path)) {
            return $path;
        }
    }
    return null;
}

function saveFile($file, $obj, $field)
{
    $filename = \Illuminate\Support\Str::random(30);
    $file->move(gas_storage_path('app'), $filename);
    $obj->$field = sprintf('app/%s', $filename);
    $obj->save();
}

function downloadFile($obj, $field)
{
    if (!empty($obj->$field)) {
        $path = gas_storage_path($obj->$field);
        if (file_exists($path)) {
            return response()->download($path);
        }
        else {
            Log::error('File non trovato in fase di download: ' . $path);
            $obj->$field = '';
            $obj->save();
        }
    }

    return '';
}

function serverMaxUpload()
{
    return min(humanSizeToBytes(ini_get('post_max_size')), humanSizeToBytes(ini_get('upload_max_filesize')));
}
