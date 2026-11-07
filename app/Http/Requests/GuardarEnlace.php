<?php

namespace App\Http\Requests;

use App\Models\Enlace;
use App\Servicios\Slugs;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarEnlace extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Solo se normalizan los campos enviados: en un PATCH lo que no llega no se toca.
        $limpio = fn (mixed $valor): mixed => is_string($valor) ? (trim($valor) === '' ? null : trim($valor)) : $valor;
        $normalizados = [];
        foreach (['destino', 'slug', 'titulo', 'expira_en'] as $campo) {
            if ($this->exists($campo)) {
                $normalizados[$campo] = $limpio($this->input($campo));
            }
        }
        if (is_string($normalizados['slug'] ?? null)) {
            $normalizados['slug'] = strtolower($normalizados['slug']);
        }
        $this->merge($normalizados);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $enlace = $this->route('enlace');
        $parcial = $this->isMethod('PATCH');

        return [
            'destino' => [$parcial ? 'sometimes' : 'required', 'string', 'max:2048', 'url:http,https', $this->noApuntaAEsteSitio()],
            'slug' => [
                'nullable', 'string', 'min:2', 'max:64', 'regex:/'.Slugs::PATRON.'/',
                Rule::notIn(Slugs::RESERVADOS),
                Rule::unique('enlaces', 'slug')->ignore($enlace instanceof Enlace ? $enlace->id : null),
            ],
            'titulo' => ['nullable', 'string', 'max:160'],
            'expira_en' => ['nullable', 'date', 'after:now'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'destino.required' => 'Escribe la dirección a la que debe llevar el enlace.',
            'destino.url' => 'El destino debe ser una dirección completa que empiece con http:// o https://.',
            'destino.max' => 'El destino no puede pasar de 2048 caracteres.',
            'slug.regex' => 'El enlace corto solo lleva minúsculas, números y guiones, sin guion al inicio ni al final.',
            'slug.min' => 'El enlace corto lleva al menos 2 caracteres.',
            'slug.max' => 'El enlace corto no puede pasar de 64 caracteres.',
            'slug.not_in' => 'Ese enlace corto está reservado por el sistema.',
            'slug.unique' => 'Ese enlace corto ya existe. Elige otro.',
            'titulo.max' => 'El título no puede pasar de 160 caracteres.',
            'expira_en.date' => 'La fecha de vencimiento no es válida.',
            'expira_en.after' => 'La fecha de vencimiento debe ser futura.',
        ];
    }

    private function noApuntaAEsteSitio(): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar): void {
            $host = is_string($valor) ? parse_url($valor, PHP_URL_HOST) : null;
            if (is_string($host) && strcasecmp($host, $this->getHost()) === 0) {
                $fallar('El destino no puede ser otro enlace de este acortador.');
            }
        };
    }
}
