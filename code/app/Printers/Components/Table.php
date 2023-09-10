<?php

namespace App\Printers\Components;

class Table implements Component
{
    private $headers;
    private $contents;

    public function __construct($headers, $contents)
    {
        $this->headers = $headers;
        $this->contents = $contents;
    }

    public function renderHtml()
    {
        $ret = '<table border="1" style="width: 100%" cellpadding="5">';

        if (empty($this->headers) == false) {
            $cellsize = round(100 / count($this->headers), 3);
        }
        else {
            $cellsize = round(100 / count(($this->contents[0] ?? [])), 3);
        }

        if (empty($this->headers) == false) {
            $ret .= '<thead><tr>';

            foreach ($this->headers as $header) {
                $ret .= sprintf('<th width="%s%%"><strong>%s</strong></th>', $cellsize, $header);
            }

            $ret .= '</tr></thead>';
        }

        $ret .= '<tbody>';

        foreach ($this->contents as $row) {
            $ret .= '<tr>';

            foreach ($row as $cell) {
                $ret .= sprintf('<td>%s</td>', $cell);
            }

            $ret .= '</tr>';
        }

        $ret .= '</tbody></table>';
        return $ret;
    }

    public function renderCsv()
    {
        return array_merge([$this->headers], $this->contents);
    }
}
