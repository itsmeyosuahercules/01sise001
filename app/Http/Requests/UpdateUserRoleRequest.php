<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $mahasiswa = $this->route('user');

        return $mahasiswa instanceof User
            && ($this->user()?->can('updateRole', $mahasiswa) ?? false);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $mahasiswa = $this->route('user');
                $role = $this->enum('role', UserRole::class);

                if (! $mahasiswa instanceof User || ! $role instanceof UserRole) {
                    return;
                }

                if ($mahasiswa->role !== UserRole::Km || $role === UserRole::Km) {
                    return;
                }

                $kmCount = User::query()->where('role', UserRole::Km)->count();

                if ($kmCount <= 1) {
                    $validator->errors()->add('role', 'Tidak bisa menurunkan KM terakhir. Tunjuk KM baru dulu.');
                }
            },
        ];
    }
}
