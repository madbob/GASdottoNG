<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\GroupsService;

use App\User;

class GroupsController extends BackedController
{
    public function __construct(GroupsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Group',
            'service' => $service,
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function () use ($id) {
            $g = $this->service->show($id);

            return view('groups.edit', ['group' => $g]);
        });
    }

    /*
        Mostra la griglia di assegnazione massiva delle Aggregazioni con
        context = User
    */
    public function matrix()
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        return view('groups.matrix');
    }

    /*
        Per salvare le modifiche sulla griglia di assegnazione massiva
    */
    public function saveMatrix(Request $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $users = $request->input('users', []);
        $users = User::topLevel()->whereIn('id', $users)->get();

        $request = $request->all();
        foreach ($users as $user) {
            $user->readCircles($request);
        }

        return $this->successResponse();
    }
}
