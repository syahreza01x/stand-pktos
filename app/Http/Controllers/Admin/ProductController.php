<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $products = Product::query()
            ->when($search, function ($q, $search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->withCount('transactionItems')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $product = Product::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditLogService::log(
            module: 'product',
            action: 'create',
            description: "Menambahkan produk baru: {$product->name} (Rp".number_format($product->price, 0, ',', '.').')',
            payload: ['product_id' => $product->id, 'price' => $product->price]
        );

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $oldPrice = $product->price;
        $product->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        AuditLogService::log(
            module: 'product',
            action: 'update',
            description: "Memperbarui produk {$product->name} (Harga lama: Rp".number_format($oldPrice, 0, ',', '.').' -> Baru: Rp'.number_format($product->price, 0, ',', '.').')',
            payload: ['product_id' => $product->id, 'old_price' => $oldPrice, 'new_price' => $product->price]
        );

        return redirect()->route('admin.products.index')->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->is_active = ! $product->is_active;
        $product->save();

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogService::log(
            module: 'product',
            action: 'update',
            description: "Produk {$product->name} {$status}.",
            payload: ['product_id' => $product->id, 'is_active' => $product->is_active]
        );

        return back()->with('success', "Produk {$product->name} berhasil {$status}.");
    }
}
