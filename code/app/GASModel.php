<?php

namespace App;

use Illuminate\Support\Str;

use Auth;
use URL;
use Log;
use Schema;

trait GASModel
{
    use Iconable;

    private $inner_runtime_cache;

    /*
        Funzione di comodo, funge come find() ma se la classe è soft-deletable
        cerca anche tra gli elementi cancellati
    */
    public static function tFind($id, $fail = false)
    {
        $class = get_called_class();

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
            $ret = $class::where('id', $id)->withoutGlobalScopes()->withTrashed()->first();
        else
            $ret = $class::find($id);

        if ($ret == null && $fail == true)
            abort(404);

        return $ret;
    }

    public function printableName()
    {
        return $this->name;
    }

    public function getPrintableNameAttribute()
    {
        return $this->printableName();
    }

    public function printableHeader()
    {
        return $this->printableName() . $this->headerIcons();
    }

    public function printableDate($name)
    {
        return printableDate($this->$name);
    }

    protected function innerCache($name, $function)
    {
        if (!isset($this->inner_runtime_cache[$name])) {
            $this->inner_runtime_cache[$name] = $function($this);
        }

        return $this->inner_runtime_cache[$name];
    }

    protected function setInnerCache($name, $value)
    {
        $this->inner_runtime_cache[$name] = $value;
    }

    protected function emptyInnerCache($name = null)
    {
        if (is_null($name))
            $this->inner_runtime_cache = [];
        else
            unset($this->inner_runtime_cache[$name]);
    }

    private function relatedController()
    {
        $class = get_class($this);
        list($namespace, $class) = explode('\\', $class);

        return Str::plural($class).'Controller';
    }

    public function getDisplayURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@index', $controller);

        return URL::action($action).'#'.$this->id;
    }

    public function getShowURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@show', $controller);

        return URL::action($action, $this->id);
    }

    public function getROShowURL()
    {
        $controller = $this->relatedController();
        $action = sprintf('%s@show_ro', $controller);

        try {
            return URL::action($action, $this->id);
        }
        catch(\Exception $e) {
            return null;
        }
    }

    public function testAndSet($request, $name, $field = null)
    {
        if (is_null($field))
            $field = $name;

        if ($request->has($name))
            $this->$field = $request->input($name);
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public static function commonClassName()
    {
        return 'Oggetto';
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function getPermissionsProxies()
    {
        return null;
    }

    /*
        Questa va all'occorrenza sovrascritta
    */
    public function scopeEnabled($query)
    {
        return $query->whereNotNull('id');
    }

    public function scopeSorted($query)
    {
        if (Schema::hasColumn($this->table, 'name'))
            return $query->orderBy('name', 'asc');
        else if (Schema::hasColumn($this->table, 'lastname'))
            return $query->orderBy('lastname', 'asc');
        else
            return $query->orderBy('id', 'asc');
    }
}
