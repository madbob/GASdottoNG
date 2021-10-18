<?php

/*
    Qui vengono gestite le icone che accompagnano i diversi oggetti nella
    visualizzazione
*/

namespace App;

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

    private static function myIconsBox($class = null)
    {
        if ($class == null) {
            $class = static::class;
            list($namespace, $class) = explode('\\', $class);
        }

        $final_class = sprintf('App\View\Icons\%s', $class);

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

        $box = static::myIconsBox();
        if ($box) {
            $user = Auth::user();

            foreach ($box->commons($user) as $icon => $condition) {
                $t = $condition->test;

                if (is_null($group) == false) {
                    if (isset($condition->group) == false) {
                        continue;
                    }

                    if ($condition->group != $group) {
                        continue;
                    }
                }

                if ($t($this)) {
                    $ret[] = $icon;
                }
            }

            foreach ($box->selective() as $icon => $condition) {
                $assign = $condition->assign;
                $ret = array_merge($ret, $assign($this));
            }
        }

        return $ret;
    }

    public static function iconsLegend($class, $contents = null)
    {
        $ret = [];

        $box = static::myIconsBox($class);
        if (is_null($box) == false) {
            $user = Auth::user();

            foreach ($box->commons($user) as $icon => $condition) {
                $ret[$icon] = $condition->text;
            }

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
