<?php

namespace App\Http\Requests;

use App\UserFollower;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserFollowerRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('user_follower_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'followed_id' => [
                'required',
                'integer',
            ],
            'follower_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
