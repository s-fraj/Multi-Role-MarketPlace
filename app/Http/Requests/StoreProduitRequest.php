<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isVendeur();
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:50'],
            'prix' => ['required', 'numeric', 'min:0'],
            'prix_barre' => ['nullable', 'numeric', 'min:0', 'gte:prix'],
            'stock' => ['required', 'integer', 'min:0'],
            'poids' => ['nullable', 'numeric', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:4096'],
            'attributs' => ['nullable', 'array'],
            'attributs.*.nom' => ['required_with:attributs', 'string', 'max:100'],
            'attributs.*.valeurs' => ['required_with:attributs', 'array', 'min:1'],
            'attributs.*.valeurs.*.libelle' => ['required', 'string', 'max:100'],
            'attributs.*.valeurs.*.photo' => ['nullable', 'image', 'max:2048'],
            'statut' => ['required', 'in:publier,brouillon'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'prix.required' => 'Le prix de vente est obligatoire.',
            'prix_barre.gte' => 'Le prix barré doit être supérieur ou égal au prix de vente.',
            'photos.*.image' => 'Chaque fichier doit être une image.',
            'photos.*.max' => 'Chaque image ne doit pas dépasser 4 Mo.',
        ];
    }
}
