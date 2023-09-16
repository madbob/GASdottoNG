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

function handleFileUpload($request, $obj, $field)
{
    if (isset($request[$field])) {
        saveFile($request[$field], $obj, $field);
    }
    else {
        $deleting = sprintf('delete_image_%s', $field);
        if (isset($request[$deleting])) {
            $path = gas_storage_path($obj->$field);
            @unlink($path);
            $obj->$field = '';
            $obj->save();
        }
    }
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

function zipAll($path, $files)
{
    $archive = \ezcArchive::open($path, \ezcArchive::ZIP);

    foreach($files as $f) {
        $archive->append([$f], '');
        unlink($f);
    }
}

function humanSizeToBytes($size)
{
    $suffix = strtoupper(substr($size, -1));
    if (!in_array($suffix, array('P', 'T', 'G', 'M', 'K'))) {
        return (int) $size;
    }

    $val = (float) substr($size, 0, -1);

    switch ($suffix) {
        case 'P':
            $val *= 1024;
        case 'T':
            $val *= 1024;
        case 'G':
            $val *= 1024;
        case 'M':
            $val *= 1024;
        case 'K':
            $val *= 1024;
            break;
    }

    return (int)$val;
}

function serverMaxUpload()
{
    return min(humanSizeToBytes(ini_get('post_max_size')), humanSizeToBytes(ini_get('upload_max_filesize')));
}
