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
                    return !$obj->active;
                },
                'text' => __('texts.generic.disabled'),
            ],
            'hidden-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->active;
                },
                'text' => __('texts.products.bookable'),
            ],
        ];
    }

    public static function selective()
    {
        return [
            'th' => (object) [
                'text' => __('texts.generic.category'),
                'assign' => function ($obj) {
                    return ['hidden-cat-' . $obj->category_id];
                },
                'options' => function ($objs) {
                    $categories = $objs->pluck('category_id')->toArray();
                    $categories = array_unique($categories);

                    return Category::whereIn('id', $categories)->orderBy('name', 'asc')->get()->reduce(function ($carry, $item) {
                        $carry['hidden-cat-' . $item->id] = $item->name;

                        return $carry;
                    }, []);
                },
            ],
        ];
    }
}
