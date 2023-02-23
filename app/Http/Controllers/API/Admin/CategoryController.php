<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function getById($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category was not found!'
            ], 404);
        }
        $category->category;

        return response()->json([
            'category' => $category
        ], 200);
    }

    public function create(CreateCategoryRequest $request)
    {
        $validation = $request->validated();

        $category = Category::create([
            'name' => $validation['name']
        ]);

        $res = [
            'category' => $category,
            'message' => 'Category was created successfully!'
        ];
        return response()->json($res, 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category was not found!'
            ], 404);
        }

        $request->validate([
            'name' => [Rule::unique('categories', 'name')->ignore($id)]
        ]);

        $category->update($request->all());

        return response()->json([
            'category' => $category
        ], 200);
    }

    public function destroy($id)
    {
        return Category::destroy($id);
    }
}
