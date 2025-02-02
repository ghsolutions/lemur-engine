<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\Normalization;

class CreateNormalizationRequest extends HiddenIdRequest
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

        $rules = Normalization::$rules;

        $rules['original_value'] = [
            'required',
            Rule::unique('normalizations')
                ->where('language_id', $this->language_id)
                ->whereNull('deleted_at')
        ];
        return $rules;
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'original_value.unique' => 'Duplicate record - this value has already been normalized for this language.',
        ];
    }
}
