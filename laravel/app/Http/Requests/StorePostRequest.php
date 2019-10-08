<?php

namespace App\Http\Requests;

use App\Post;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('post_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'title'                  => [
                'min:10',
                'max:255',
                'required',
            ],
            'slug'                   => [
                'min:10',
                'max:255',
                'required',
                'unique:posts',
            ],
            'description'            => [
                'max:255',
            ],
            'body'                   => [
                'required',
            ],
            'featured_image_caption' => [
                'max:255',
            ],
            'reading_time_minutes'   => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'status'                 => [
                'required',
            ],
            'num_comments'           => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'num_likes'              => [
                'required',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'user_id'                => [
                'required',
                'integer',
            ],
        ];
    }
}
