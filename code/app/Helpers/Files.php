<?php

function saveFile($file, $obj, $field)
{
    $filename = str_random(30);
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
            Log::error('File non trovato: ' . $path);
            $obj->$field = '';
            $obj->save();
        }
    }

    return '';
}
