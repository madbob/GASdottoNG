<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait SluggableID
{
    public static function bootSluggableID()
    {
        static::creating(function($model) {
            if (empty($model->id)) {
                $id = trim($model->getSlugID());
                $model->id = $id;
            }
        });
    }

    private function computeBaseSlug()
    {
        $parts = $this->getSlugParts();
        if ($parts) {
            $ret = join('::', $parts);

            if (strlen($ret) > 185) {
                $hash = md5(join('::', array_slice($parts, 1)));
                $ret = sprintf('%s::%s', substr($parts[0], 0, (strlen($hash) + 2) * -1), $hash);
            }

            $ret = substr($ret, 0, 185);
        }
        else {
            // Non tutti i Model che usano questo trait hanno un name, ma hanno
            // comunque una propria implementazione di getSlugID() o
            // getSlugParts()
            // @phpstan-ignore-next-line
            $ret = substr(Str::slug($this->name), 0, 80);
        }

        return $ret;
    }

    public function getSlugID()
    {
        $append = '';
        $index = 1;
        $classname = get_class($this);
        $base = $this->computeBaseSlug();

        while (true) {
            $slug = $base . $append;

            /*
                Questo è per prevenire i (rari, ma possibili) casi in cui si
                cerca di creare una istanza con un nome vuoto. In tal caso anche
                l'ID sarebbe vuoto, cosa che spacca poi la costruzione degli URL
                e, di conseguenza, genera problemi apparentemente random nel
                frontend
            */
            if (blank($slug)) {
                $slug = Str::random(10);
            }

            if ($classname::tFind($slug) != null) {
                $append = '_' . $index;
                $index++;
            }
            else {
                break;
            }
        }

        return $slug;
    }

    protected function getSlugParts(): ?array
    {
        return null;
    }
}
