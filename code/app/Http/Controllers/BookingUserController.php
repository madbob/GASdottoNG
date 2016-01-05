<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use App\Aggregate;

class BookingUserController extends Controller
{
        public function index()
        {

        }

        public function create()
        {
        //
        }

        public function store(Request $request)
        {
        //
        }

        public function show(Request $request, $aggregate_id, $user_id)
        {
                /*
                        TODO    Verificare permessi
                */
                $aggregate = Aggregate::findOrFail($aggregate_id);
                $user = User::findOrFail($user_id);
                return view('booking.edit', ['aggregate' => $aggregate, 'user' => $user]);
        }

        public function edit($id)
        {
        //
        }

        public function update(Request $request, $id)
        {
        //
        }

        public function destroy($id)
        {
        //
        }
}
