<?php

namespace App\Services\Concerns;

use Illuminate\Support\Facades\Storage;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Format;
use Intervention\Image\Alignment;

trait DispatchPictures
{
    /*
        https://gist.github.com/mrkmg/1607621
    */
    private function colorFromText($text, $min_brightness = 100, $spec = 10)
    {
        $hash = md5($text);
        $colors = array();
        for ($i = 0; $i < 3; $i++) {
            $colors[$i] = max(array(round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $min_brightness));
        }

        if ($min_brightness > 0) {
            while (array_sum($colors) / 3 < $min_brightness) {
                for ($i = 0; $i < 3; $i++) {
                    $colors[$i] += 10;
                }
            }
        }

        $output = '';

        for ($i = 0; $i < 3; $i++) {
            $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
        }

        return $output;
    }

    private function generateAvatar($name)
    {
        $path = Storage::disk('avatars')->path($name);

        if (file_exists($path) == false) {
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->createImage(300, 300)->fill($this->colorFromText($name));

            $tokens = explode(' ', $name);
            $tokens = array_values(array_filter($tokens, fn($t) => strlen($t) > 0 && preg_match('/^[a-zA-Z0-9]/', $t)));

            if (count($tokens) >= 2) {
                $text = mb_strtoupper(sprintf('%s%s', $tokens[0][0], $tokens[1][0]));
            }
            else {
                $text = mb_strtoupper(substr($tokens[0], 0, 2));
            }

            $image->text($text, 150, 150, function (FontFactory $font) {
                $font->filepath(base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'));
                $font->size(100);
                $font->color('FFF');
                $font->align(Alignment::CENTER, Alignment::CENTER);
            });

            $image->encodeUsingFormat(Format::JPEG, quality: 70)->save($path);
        }

        return $path;
    }

    public function picture($id)
    {
        $obj = $this->show($id);

        if (empty($obj->picture)) {
            $path = $this->generateAvatar($obj->printableName());
            return response()->download($path);
        }
        else {
            return downloadFile($obj, 'picture');
        }
    }
}
