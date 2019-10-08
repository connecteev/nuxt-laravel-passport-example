<?php

namespace App\Http\Requests;

use App\ContactForm;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreContactFormRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('contact_form_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'         => [
                'max:255',
                'required',
            ],
            'email'        => [
                'required',
            ],
            'contact_type' => [
                'required',
            ],
            'message'      => [
                'required',
            ],
        ];
    }
}
