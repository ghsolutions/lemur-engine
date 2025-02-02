<?php

namespace App\Http\Requests;

use App\Models\Bot;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\BotProperty;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateBotPropertyRequest extends HiddenIdRequest
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
        $rules = BotProperty::$rules;

        $rules['name'] = [
            'required',
            Rule::unique('bot_properties')
                ->ignore($this->slug, 'slug')
                ->where('bot_id', $this->bot_id)
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
            'name.unique' => 'Duplicate record - this bot name has already been taken.',
        ];
    }
}
