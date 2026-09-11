<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Muestra el catálogo de productos con búsqueda, ordenamiento y filtros de categoría.
     */
    public function index(Request $request): View
    {
        // 1. Iniciar la consulta cargando la relación de categoría
        $query = Product::with('category');

        // 2. Aplicar filtro de búsqueda por nombre o SKU
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // 3. Aplicar filtro por categoría si se especificó una
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category_id', $request->input('category'));
        }

        // 4. Obtener productos paginados ordenados por fecha de creación (últimos agregados)
       $products = $query->latest()->get();
       
        // 5. Obtener categorías activas/existentes con el conteo de sus productos
        $categories = Category::withCount('products')
            ->orderBy('name', 'asc')
            ->get();

        // 6. Retornar la vista pasando los datos
        // Nota: Asegúrate de que la carpeta se llame 'catalogo' o 'catalog' en resources/views
        return view('catalogo.index', compact('products', 'categories'));
    }
}