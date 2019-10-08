<?php

namespace App\Http\Requests;

use App\PodcastEpisodeBusinessType;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeBusinessTypeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_business_type_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'business_type_name' => [
                'max:255',
                'required',
                'unique:podcast_episode_business_types',
            ],
            'business_type_slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_business_types',
            ],
        ];
    }
}
