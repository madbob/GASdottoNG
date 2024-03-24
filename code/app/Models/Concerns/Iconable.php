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

        if (!empty($icons)) {
            $ret .= '<div class="float-end">';

            foreach ($icons as $i) {
                $ret .= '<i class="bi-' . $i . '"></i>';
                if (substr($i, 0, 6) != 'hidden') {
                    $ret .= '&nbsp;';
                }
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

    public function icons($group = null)
    {
        $ret = [];

        $box = self::myIconsBox();
        if ($box) {
            $user = Auth::user();
            $obj = $this;

            $ret = array_keys(array_filter($box->commons($user), function($condition, $icon) use ($obj, $group) {
                if (is_null($group) == false && (isset($condition->group) == false || $condition->group != $group)) {
                    return false;
                }

                $t = $condition->test;
                return $t($obj);
            }, ARRAY_FILTER_USE_BOTH));

            $ret = array_reduce($box->selective(), function($ret, $condition) use ($obj) {
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
        if (is_null($box) == false) {
            $user = Auth::user();

            $ret = array_map(function($condition) {
                return $condition->text;
            }, $box->commons($user));

            if ($contents != null) {
                foreach ($box->selective() as $icon => $condition) {
                    $options = $condition->options;
                    $options = $options($contents);
                    if (!empty($options)) {
                        $description = (object) [
                            'label' => $condition->text,
                            'items' => $options
                        ];
                        $ret[$icon] = $description;
                    }
                }
            }
        }

        return $ret;
    }
}
