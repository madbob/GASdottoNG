<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Theme;

class MovementsController extends Controller
{
        public function index()
        {
                return Theme::view('pages.movements');
        }

        public function create()
        {
        //
        }

        public function store(Request $request)
        {
        //
        }

        public function show($id)
        {
        //
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
