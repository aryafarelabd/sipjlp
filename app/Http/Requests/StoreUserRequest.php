<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.manage');
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:users,username|regex:/^[a-z0-9._]+$/',
            'nip'      => 'nullable|string|digits_between:1,30|unique:users,nip',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
            'unit'     => 'nullable|in:security,cleaning,all',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'Username sudah digunakan.',
            'username.regex'  => 'Username hanya boleh berisi huruf kecil, angka, titik, dan underscore.',
            'nip.digits_between' => 'NIP hanya boleh berisi angka (maksimal 30 digit).',
            'nip.unique'         => 'NIP sudah digunakan oleh user lain.',
        ];
    }
}
