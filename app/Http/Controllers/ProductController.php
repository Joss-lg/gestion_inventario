<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Muestra el catálogo de productos con sus categorías.
     */
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Método para servir la imagen del producto de forma segura.
     */
    public function showImage($path)
    {
        $path = urldecode($path);
        $path = str_replace("\0", '', $path);
        $path = ltrim($path, '/');
        $path = str_replace('\\', '/', $path);

        if (strpos($path, '..') !== false) {
            abort(404);
        }

        if (! str_starts_with($path, 'products/')) {
            $path = 'products/' . $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $full = Storage::disk('public')->path($path);
        $productsDir = realpath(storage_path('app/public/products'));
        $real = realpath($full);

        if ($productsDir === false || $real === false || strpos($real, $productsDir) !== 0) {
            abort(404);
        }

        return response()->file($real);
    }

    /**
     * Mostrar formulario de creación cargando las categorías disponibles.
     */
    public function create()
    {
        $categories = Category::all();

        return view('products.create', compact('categories'));
    }

    /**
     * Guardar un nuevo producto con su imagen.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    /**
     * Mostrar un producto específico.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Actualizar producto y reemplazar imagen.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Eliminar producto y su imagen del almacenamiento.
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
