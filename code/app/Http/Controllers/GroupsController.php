<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\GroupsService;

class GroupsController extends BackedController
{
    public function __construct(GroupsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Group',
            'service' => $service
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function() use ($id) {
            $g = $this->service->show($id);
            return view('groups.edit', ['group' => $g]);
        });
    }
}
