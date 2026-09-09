<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del producto que se está editando desde la ruta
        $productId = $this->route('product')->id;

        return [
            'sku' => 'required|string|max:255|unique:products,sku,'.$productId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Este SKU ya pertenece a otro producto.',
            'price.min' => 'El precio no puede ser negativo.',
        ];
    }
}
