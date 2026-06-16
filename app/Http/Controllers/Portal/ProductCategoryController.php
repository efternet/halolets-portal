<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('product_categories as pc')
            ->leftJoin('product_list as pl', 'pc.id', '=', 'pl.category_id')
            ->select('pc.*', DB::raw('COUNT(pl.id) as product_count'))
            ->groupBy('pc.id')
            ->orderBy('pc.name')
            ->get();

        return view('portal.product-categories.index', compact('categories'));
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $id = DB::table('product_categories')->insertGetId([
            'name'       => $request->validated('name'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = DB::table('product_categories')->where('id', $id)->first();

        return response()->json(['success' => true, 'id' => $id, 'name' => $category->name]);
    }

    public function destroy(int $id): JsonResponse
    {
        $count = DB::table('product_list')->where('category_id', $id)->count();

        DB::table('product_list')->where('category_id', $id)->update(['category_id' => null]);
        DB::table('product_categories')->where('id', $id)->delete();

        return response()->json(['success' => true, 'unlinked' => $count]);
    }
}
