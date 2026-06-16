<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\ProductCategory;
use App\Models\ProductList;
use Illuminate\Http\JsonResponse;

class ProductListController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search         = request('search');
        $filterCategory = request('category_id');
        $categories     = ProductCategory::query()->orderBy('name')->get();
        $sort           = in_array(request('sort'), ['p.id', 'p.product_name', 'pc.name', 'p.acquisition_date', 'p.warranty_expiry'])
                          ? request('sort') : 'p.id';
        $direction      = request('direction') === 'asc' ? 'asc' : 'desc';

        $query = ProductList::query()
            ->from('product_list as p')
            ->leftJoin('product_categories as pc', 'p.category_id', '=', 'pc.id')
            ->select('p.*', 'pc.name as category_name')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('p.id',             'like', "%{$search}%")
                ->orWhere('p.product_name', 'like', "%{$search}%")
                ->orWhere('pc.name',        'like', "%{$search}%")
                ->orWhere('p.brand',        'like', "%{$search}%")
                ->orWhere('p.model',        'like', "%{$search}%")
                ->orWhere('p.serial_number','like', "%{$search}%")
                ->orWhere('p.asset_tag',    'like', "%{$search}%")
                ->orWhere('p.batch_no',     'like', "%{$search}%")
                ->orWhere('p.supplier',     'like', "%{$search}%")
            ))
            ->when($filterCategory, fn ($q) => $q->where('p.category_id', $filterCategory))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('product-list.csv',
                ['ID', 'Category', 'Product Name', 'Brand', 'Model', 'Serial No', 'Asset Tag', 'Batch No', 'Purchase Order', 'Rental SKU', 'Supplier', 'Grade', 'Acquired', 'Warranty Expiry', 'Notes'],
                $query->get(),
                fn ($r) => [$r->id, $r->category_name, $r->product_name, $r->brand, $r->model, $r->serial_number, $r->asset_tag, $r->batch_no, $r->purchase_order, $r->rental_sku, $r->supplier, $r->condition_grade, $r->acquisition_date, $r->warranty_expiry, $r->notes]
            );
        }

        $products = $query->paginate(15)->appends(request()->only(['search', 'category_id', 'sort', 'direction']));

        return view('portal.product-list.index', compact('products', 'categories'), [
            'sort'           => $sort, 'direction'      => $direction, 'filterCategory' => $filterCategory,
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = ProductList::create($request->validated());

        return response()->json(['success' => true, 'id' => $product->id]);
    }

    public function update(UpdateProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $id   = $data['id'];
        unset($data['id']);

        ProductList::query()->where('id', $id)->update($data);

        return response()->json(['success' => true]);
    }
}
