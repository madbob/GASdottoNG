<?php

namespace App\View\Icons;

use App\Category;

class Product extends IconsMap
{
    public static function commons($user)
    {
        return [
            'slash-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->active == false;
                },
                'text' => _i('Disabilitato'),
            ],
            'hidden-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->active == true;
                },
                'text' => _i('Attivo'),
            ],
        ];
    }

    public static function selective()
    {
        return [
            'th' => (object) [
                'text' => _i('Categoria'),
                'assign' => function ($obj) {
                    return ['hidden-cat-' . $obj->category_id];
                },
                'options' => function($objs) {
                    $categories = $objs->pluck('category_id')->toArray();
                    $categories = array_unique($categories);

                    return Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->reduce(function($carry, $item) {
                        $carry['hidden-cat-' . $item->id] = $item->name;
                        return $carry;
                    }, []);
                }
            ]
        ];
    }
}
