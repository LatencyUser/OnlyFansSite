<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePostRequest extends FormRequest
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

        $rules = [];
        if((int)getSetting('feed.min_post_description') > 0){
            $rules['text'] = 'required|min:'.getSetting('feed.min_post_description');
        }
        else{
            $rules['attachments'] = 'required';
        }

        return $rules;
    }
}
