<?php

namespace App\Http\Requests\Goals;

use Illuminate\Foundation\Http\FormRequest;

class GoalStoreRequest extends FormRequest
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
            'title' => 'string|required',
            'verb' => 'string|required',
            'lengthOfTime' => 'string|required',
            'equality' => 'string|required',
            'expectedAmount' => 'int|required',
            'unit' => 'string|required',
        ];
    }
}
