<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\User;
use App\Circle;
use App\Group;

class CirclesService extends BaseService
{
    public function show($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);
        return Circle::findOrFail($id);
    }

    public function store(array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $c = new Circle();
        $this->setIfSet($c, $request, 'name');
        $this->setIfSet($c, $request, 'description');

        $group_id = $request['group_id'];
        $c->group_id = $group_id;
        $group = Group::find($group_id);

        if ($group->circles()->count() == 0) {
            $c->is_default = true;
            $was_first = true;
        }
        else {
            $was_first = false;
        }

        $c->save();

        if ($was_first) {
            if ($group->context == 'user') {
                foreach(User::topLevel()->get() as $user) {
                    $user->circles()->attach($c->id);
                }
            }
        }

        return $c;
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $c = Circle::findOrFail($id);
        $this->setIfSet($c, $request, 'name');
        $this->setIfSet($c, $request, 'description');
        $this->boolIfSet($c, $request, 'is_default');

        if ($c->is_default) {
            $c->group->circles()->where('circles.id', '!=', $c->id)->update(['is_default' => false]);
        }

        $c->save();

        return $c;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        DB::beginTransaction();
        $c = $this->show($id);

        if ($c->is_default) {
            $other = $c->group->circles()->where('id', '!=', $c->id)->get();
            if ($other->isEmpty() == false) {
                $new_default = $other->first();
                $new_default->is_default = true;
                $new_default->save();

                $users = User::topLevel()->whereHas('circles', function($query) use ($c) {
                    $query->where('circles.id', $c->id);
                })->get();

                foreach($users as $user) {
                    $user->circles()->attach($new_default->id);
                }
            }
        }

        $c->delete();
        DB::commit();

        return $c;
    }
}
