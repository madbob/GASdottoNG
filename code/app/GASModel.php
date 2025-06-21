<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

use URL;
use Schema;

use App\Models\Concerns\Iconable;
use App\Models\Concerns\ManagesInnerCache;

trait GASModel
{
    use Iconable, ManagesInnerCache;

    /*
        Funzione di comodo, funge come find() ma se la classe è soft-deletable
        cerca anche tra gli elementi cancellati
    */
    public static function tFind($id, $fail = false)
    {
        $class = get_called_class();

        if (hasTrait($class, SoftDeletes::class)) {
            $ret = $class::where('id', $id)->withoutGlobalScopes()->withTrashed()->first();
        }
        else {
            $ret = $class::find($id);
        }

        if ($ret == null && $fail === true) {
            abort(404);
        }

        return $ret;
    }

    /*
        Funzione di comodo, funge come all() ma se la classe è soft-deletable
        cerca anche tra gli elementi cancellati
    */
    public static function tAll()
    {
        $class = get_called_class();

        if (hasTrait($class, SoftDeletes::class)) {
            return $class::withTrashed()->get();
        }
        else {
            return $class::all();
        }
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

    private function relatedController()
    {
        $class = get_class($this);
        $tokens = explode('\\', $class);

        return Str::plural($tokens[1]).'Controller';
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
        catch (\Exception $e) {
            return null;
        }
    }

    public function testAndSet($request, $name, $field = null)
    {
        if (is_null($field)) {
            $field = $name;
        }

        if ($request->has($name)) {
            $this->$field = $request->input($name);
        }
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

    public function scopeSorted($query)
    {
        if (Schema::hasColumn($this->table, 'name')) {
            return $query->orderBy('name', 'asc');
        }
        elseif (Schema::hasColumn($this->table, 'lastname')) {
            return $query->orderBy('lastname', 'asc');
        }
        else {
            return $query->orderBy('id', 'asc');
        }
    }

    public static function easyCreate($params)
    {
        // Non usare questa funzione per allocare un AggregateBooking (che non è
        // un vero model, non essendo salvato sul DB)
        // @phpstan-ignore-next-line
        $obj = new self();

        foreach ($params as $name => $value) {
            $obj->$name = $value;
        }

        $obj->save();

        return $obj;
    }
}
