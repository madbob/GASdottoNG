<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Theme;
use App\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->gas->userHas('categories.admin') == false) {
            abort(503);
        }

        $categories = Category::where('parent_id', '=', null)->get();

        return Theme::view('categories.edit', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->gas->userHas('categories.admin') == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $category = new Category();
        $category->name = $request->input('name');

        $parent = $request->input('parent_id');
        if ($parent != 'null') {
            $category->parent_id = $parent;
        } else {
            $category->parent_id = null;
        }

        $category->save();

        return $this->successResponse([
            'id' => $category->id,
                        'parent' => $category->parent_id,
            'name' => $category->name,
        ]);
    }

    private function updateRecursive($data, $parent, &$accumulator)
    {
        foreach ($data as $category) {
            $c = Category::find($category['id']);
            if ($c == null) {
                $c = new Category();
            }

            $c->name = $category['name'];
            $c->parent_id = $parent;
            $c->save();
            $accumulator[] = $c->id;

            if (array_key_exists('children', $category)) {
                $this->updateRecursive($category['children'], $c->id, $accumulator);
            }
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->gas->userHas('categories.admin') == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $data = $request->input('serialized');
        $accumulator = [];

        $this->updateRecursive($data, null, $accumulator);
        Category::whereNotIn('id', $accumulator)->delete();

        return $this->successResponse();
    }
}
