<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UsersService;

class FriendsController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\User',
            'service' => $service,
        ]);
    }

    public function store(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $subject = $this->service->storeFriend($request->all());

            return $this->commonSuccessResponse($subject);
        });
    }
}
