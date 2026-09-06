<?php

namespace App\Http\Requests;

use App\Models\SaturdayAttendance;
use App\Support\SaturdayAttendanceWindow;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaturdayAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SaturdayAttendance::class) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $config = config('kelas.attendance');

        return [
            'photo' => ['required', 'image', 'max:'.$config['max_kilobytes'], 'mimes:'.implode(',', $config['mimes'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:5000'],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! SaturdayAttendanceWindow::isOpen()) {
                    $validator->errors()->add('photo', 'Hadir hanya bisa dikirim hari Sabtu pada jam buka ('.SaturdayAttendanceWindow::openWindowLabel().').');
                }
            },
        ];
    }
}
