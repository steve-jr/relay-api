<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCreatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:8'],
            'consent' => ['required', 'accepted'],

            // Creator profile
            'display_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'size:2'],
            'primary_niche' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],

            // Industries
            'industries' => ['required', 'array', 'min:1'],
            'industries.*' => ['required', 'string', 'max:100'],
            'other_industries' => ['nullable', 'string', 'max:255'],

            // Social profiles
            'instagram_handle' => ['nullable', 'string', 'max:100'],
            'tiktok_handle' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function role(): string
    {
        return 'creator';
    }
}
