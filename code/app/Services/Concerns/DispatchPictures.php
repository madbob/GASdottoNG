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
    private function generateAvatar($obj)
    {
        $name = $obj->printableName();
        $path = Storage::disk('avatars')->path($name);

        if (file_exists($path) == false) {
            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->createImage(300, 300)->fill(colorFromText($name));
            $text = $obj->defaultPictureText();

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
            $path = $this->generateAvatar($obj);
            return response()->download($path);
        }
        else {
            return downloadFile($obj, 'picture');
        }
    }
}
