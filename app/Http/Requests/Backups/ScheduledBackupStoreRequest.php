<?php

namespace App\Http\Requests\Backups;

use Illuminate\Foundation\Http\FormRequest;

class ScheduledBackupStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'schedule' => 'required|array',
            'startTime' => 'required|int',
            'fullEveryNDays' => 'required|int',
            'enabled' => 'required|bool',
            'scheduledBackupSteps' => 'required|array',
        ];
    }
}
