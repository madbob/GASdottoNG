<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;
use Auth;

use App\Category;
use App\Product;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\Category'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->can('categories.admin', $user->gas) == false) {
            abort(503);
        }

        $categories = Category::where('id', '!=', 'non-specificato')->where('parent_id', '=', null)->get();

        return view('categories.edit', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('categories.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
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
            $c = null;

            if (empty($category['name']))
                continue;

            if (isset($category['id'])) {
                if (in_array($category['id'], $accumulator)) {
                    Log::error('Itero ricorsivamente categoria già gestita, salto');
                    continue;
                }

                $c = Category::find($category['id']);
            }

            if (is_null($c))
                $c = new Category();

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
        if ($user->can('categories.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $data = $request->input('serialized');
        $accumulator = ['non-specificato'];

        $this->updateRecursive($data, null, $accumulator);
        Product::whereNotIn('category_id', $accumulator)->update(['category_id' => 'non-specificato']);
        Category::whereNotIn('id', $accumulator)->delete();

        return $this->successResponse();
    }
}
