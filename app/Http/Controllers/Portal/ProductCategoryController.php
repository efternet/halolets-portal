<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Models\ProductCategory;
use App\Models\ProductList;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->from('product_categories as pc')
            ->leftJoin('product_list as pl', 'pc.id', '=', 'pl.category_id')
            ->select('pc.*', DB::raw('COUNT(pl.id) as product_count'))
            ->groupBy('pc.id')
            ->orderBy('pc.name')
            ->get();

        return view('portal.product-categories.index', compact('categories'));
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $category = ProductCategory::create([
            'name' => $request->validated('name'),
        ]);

        return response()->json(['success' => true, 'id' => $category->id, 'name' => $category->name]);
    }

    public function destroy(int $id): JsonResponse
    {
        $count = ProductList::query()->where('category_id', $id)->count();

        ProductList::query()->where('category_id', $id)->update(['category_id' => null]);
        ProductCategory::query()->where('id', $id)->delete();

        return response()->json(['success' => true, 'unlinked' => $count]);
    }
}
