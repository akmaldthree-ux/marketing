<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $brand  = $request->get('brand', 'all');

        $products = Product::when($search, fn($q) => $q->where('product_sku', 'like', "%{$search}%")
                ->orWhere('canonical_name', 'like', "%{$search}%"))
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->orderBy('brand')->orderBy('product_sku')
            ->get();

        return view('products.index', compact('products', 'search', 'brand'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_sku'    => 'required|string|max:100|unique:products,product_sku',
            'canonical_name' => 'required|string|max:255',
            'brand'          => 'nullable|string|max:50',
            'category'       => 'nullable|string|max:100',
        ]);
        Product::create($data);
        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'product_sku'    => 'required|string|max:100|unique:products,product_sku,' . $product->id,
            'canonical_name' => 'required|string|max:255',
            'brand'          => 'nullable|string|max:50',
            'category'       => 'nullable|string|max:100',
            'is_active'      => 'boolean',
        ]);
        $product->update($data);
        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function importProducts(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);
        try {
            $rows   = (new ImportController)->parseFilePublic($request->file('file'));
            $errors = [];
            $count  = 0;
            foreach ($rows as $i => $row) {
                $row = array_change_key_case($row, CASE_LOWER);
                $first = trim(reset($row));
                if (str_starts_with($first, '#') || $first === '') continue;
                $sku  = trim($row['sku'] ?? $row['product_sku'] ?? '');
                $name = trim($row['canonical name'] ?? $row['canonical_name'] ?? $row['nama produk'] ?? $row['nama'] ?? '');
                if (!$sku || !$name) { $errors[] = "Baris " . ($i+2) . ": SKU atau nama kosong."; continue; }
                Product::updateOrCreate(
                    ['product_sku' => $sku],
                    [
                        'canonical_name' => $name,
                        'brand'    => trim($row['brand'] ?? ''),
                        'category' => trim($row['category'] ?? $row['kategori'] ?? ''),
                        'is_active'=> true,
                    ]
                );
                $count++;
            }
            $msg = "Berhasil import {$count} produk.";
            if ($errors) $msg .= ' ' . count($errors) . ' baris dilewati.';
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function templateProducts()
    {
        $rows = [
            ['SKU', 'Canonical Name', 'Brand', 'Category'],
            ['DTH-001', 'Gamis Syar\'i Premium', 'DTHREE', 'Gamis'],
            ['HRM-001', 'Sarimbit Couple Batik Premium', 'HURIM', 'Sarimbit'],
            ['ASF-001', 'Gamis Anak Asfara', 'ASFARA', 'Anak'],
        ];
        $rows[] = [];
        $rows[] = ['# PETUNJUK:'];
        $rows[] = ['# - SKU harus unik (primary key produk)'];
        $rows[] = ['# - Canonical Name adalah nama resmi produk yang dipakai di seluruh sistem'];
        $rows[] = ['# - Brand: DTHREE / HURIM / ASFARA'];
        $rows[] = ['# - Jika SKU sudah ada, nama akan diupdate'];

        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_products.csv"',
        ]);
    }
}
