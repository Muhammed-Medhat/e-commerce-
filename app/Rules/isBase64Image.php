<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class isBase64Image implements Rule
{
    public $allowedExtensions;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($allowedExtensions = ['png', 'jpg', 'jpeg'])
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    public function is_base64_image($s)
    {
        $image = $s;

        // check if the data is empty
        if (empty($image)) {
            return (bool) false;
        }

        // check base64 format
        $explode = explode(',', $image);
        if (count($explode) !== 2) {
            return (bool) false;
        }
        //https://stackoverflow.com/a/11154248/4830771
        if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])) {
            return (bool) false;
        }

        // check if type is allowed
        $format = str_replace(
            ['data:image/', ';', 'base64'],
            ['', '', '',],
            $explode[0]
        );
        if (!in_array($format, $this->allowedExtensions)) {
            return (bool) false;
        }
        return (bool) true;
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
        return $this->is_base64_image($value);
    }
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The :attribute must be image with this allowed extensions " . json_encode($this->allowedExtensions) . ".";
    }
}