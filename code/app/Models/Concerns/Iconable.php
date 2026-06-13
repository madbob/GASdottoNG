<?php

/*
    Qui vengono gestite le icone che accompagnano i diversi oggetti nella
    visualizzazione
*/

namespace App\Models\Concerns;

use Auth;

trait Iconable
{
    protected function formatIcons($icons)
    {
        $ret = '';

        if (! empty($icons)) {
            $visible = array_filter($icons, fn($i) => substr($i, 0, 6) != 'hidden');
            if (!empty($visible)) {
                $color = $this->dominantColor();
                $ret .= '<div class="header-icons text-bg-' . $color . '">';
            }
            else {
                $ret .= '<div>';
            }

            foreach ($icons as $i) {
                $ret .= '<i class="bi-' . $i . '"></i>';
            }

            $ret .= '</div>';
        }

        return $ret;
    }

    protected function headerIcons()
    {
        $icons = $this->icons();

        return $this->formatIcons($icons);
    }

    private static function myIconsBox()
    {
        $class = static::class;
        $tokens = explode('\\', $class);
        $final_class = sprintf('App\View\Icons\%s', $tokens[1]);

        if (class_exists($final_class)) {
            return new $final_class();
        }
        else {
            return null;
        }
    }

    private function dominantColor()
    {
        $box = self::myIconsBox();
        return $box->dominantColor($this);
    }

    public function icons($group = null)
    {
        $ret = [];

        $box = self::myIconsBox();
        if ($box) {
            $user = Auth::user();
            $obj = $this;

            $ret = array_keys(array_filter($box->commons($user), function ($condition) use ($obj, $group) {
                if (is_null($group) === false && (isset($condition->group) === false || $condition->group != $group)) {
                    return false;
                }

                $t = $condition->test;

                return $t($obj);
            }));

            $ret = array_reduce($box->selective(), function ($ret, $condition) use ($obj) {
                $assign = $condition->assign;

                return array_merge($ret, $assign($obj));
            }, $ret);
        }

        return $ret;
    }

    public static function iconsLegend($contents = null)
    {
        $ret = [];

        $box = self::myIconsBox();
        if ($box) {
            $user = Auth::user();

            $icons = $box->commons($user);
            $icons = array_filter($icons, fn($i) => isset($i->explicit) == false || $i->explicit);

            $ret = array_map(function ($condition) {
                return $condition->text;
            }, $icons);

            if ($contents != null) {
                foreach ($box->selective() as $icon => $condition) {
                    $options = $condition->options;
                    $options = $options($contents);
                    if (! empty($options)) {
                        $description = (object) [
                            'label' => $condition->text,
                            'items' => $options,
                        ];
                        $ret[$icon] = $description;
                    }
                }
            }
        }

        return $ret;
    }

    public static function selectiveIconsGroup($contents, $group)
    {
        $box = self::myIconsBox();

        foreach ($box->selective() as $icon => $condition) {
            if ($icon == $group) {
                $options = $condition->options;
                $options = $options($contents);

                return (object) [
                    'label' => $condition->text,
                    'items' => $options,
                ];
            }
        }

        return null;
    }
}
