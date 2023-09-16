<?php

namespace App\Printers\Components;

use PDF;

class Document implements Component
{
    private $children;
    private $format;

    public function __construct($format)
    {
        $this->children = [];
        $this->format = $format;
    }

    public function append($item)
    {
        $this->children[] = $item;
    }

    private function preparePdf()
    {
        $pdf = PDF::loadView('documents.formatted', [
            'document' => $this,
        ]);

        enablePdfPagesNumbers($pdf);
        return $pdf;
    }

    private function prepareCsv()
    {
        $ret = [];

        foreach($this->children as $child) {
            $c = $child->renderCsv();
            if ($c) {
                $ret = array_merge($ret, $c);
            }
        }

        return $ret;
    }

    public function renderHtml()
    {
        $ret = '';

        foreach($this->children as $child) {
            $ret .= $child->renderHtml();
        }

        return $ret;
    }

    public function renderCsv()
    {

    }

    public function download($filename)
    {
        switch($this->format) {
            case 'pdf':
                $pdf = $this->preparePdf();
                return $pdf->download($filename);

            case 'csv':
                $rows = $this->prepareCsv();

                return output_csv($filename, null, $rows, function($row) {
                    return $row;
                });
        }
    }

    public function save($path)
    {
        switch($this->format) {
            case 'pdf':
                $pdf = $this->preparePdf();
                $pdf->save($path);
                break;

            case 'csv':
                $rows = $this->prepareCsv();

                output_csv(null, null, $rows, function($row) {
                    return $row;
                }, $path);

                break;
        }
    }
}
