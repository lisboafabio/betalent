<?php

namespace App\Http\Requests;

use App\Domain\User\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::enum(UserRoleEnum::class)],
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $requestedRole = $this->input('role');

            if ($user->role === UserRoleEnum::MANAGER->value && $requestedRole === UserRoleEnum::ADMIN->value) {
                $validator->errors()->add('role', 'Managers cannot create Admin users.');
            }
        });
    }
}
