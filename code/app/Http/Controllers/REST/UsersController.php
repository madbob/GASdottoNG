<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Response;

use App\Services\UsersService;
use App\Http\Controllers\REST\BackedController;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class UsersController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\User',
            'endpoint' => 'users',
            'service' => $service,
            'json_wrapper' => 'user',
        ]);
    }

    public function search(Request $request)
    {
        $term = trim($request->input('term'));

        if (empty($term)) {
            throw new IllegalArgumentException('Search term is empty');
        }

        try {
            $users = $this->service->list($term);
            return response()->json(['users' => $users], 200);
        }
        catch (AuthException $e) {
            return response(null, $e->status());
        }
    }
}
