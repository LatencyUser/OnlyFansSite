<?php

namespace App\Rules;

use App\Providers\GenericHelperServiceProvider;
use Illuminate\Contracts\Validation\Rule;
use Str;

class MaxLengthMarkdown implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($value && strlen(trim(strip_tags(GenericHelperServiceProvider::parseProfileMarkdownBio($value)))) > getSetting('site.max_profile_bio_length')){
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The bio may not be greater than :chars characters.',['chars'=>  getSetting('site.max_profile_bio_length')]);
    }
}
