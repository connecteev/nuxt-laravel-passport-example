<?php

namespace App\Http\Requests;

use App\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'email'             => [
                'required',
                'unique:users',
            ],
            'username'          => [
                'max:255',
                'required',
                'unique:users',
            ],
            'email_verified_at' => [
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'nullable',
            ],
            'password'          => [
                'required',
            ],
            'roles.*'           => [
                'integer',
            ],
            'roles'             => [
                'required',
                'array',
            ],
        ];
    }
}
