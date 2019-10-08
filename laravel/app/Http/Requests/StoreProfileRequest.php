<?php

namespace App\Http\Requests;

use App\Profile;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreProfileRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('profile_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'user_id'         => [
                'required',
                'integer',
            ],
            'full_name'       => [
                'max:255',
            ],
            'bio_headline'    => [
                'max:255',
            ],
            'website_url'     => [
                'max:255',
            ],
            'location'        => [
                'max:255',
            ],
            'education'       => [
                'max:255',
            ],
            'company_name'    => [
                'max:255',
            ],
            'company_url'     => [
                'max:255',
            ],
            'work_title'      => [
                'max:255',
            ],
            'inbox_chat_type' => [
                'required',
            ],
        ];
    }
}
