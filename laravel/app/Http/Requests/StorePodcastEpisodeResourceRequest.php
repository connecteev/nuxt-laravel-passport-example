<?php

namespace App\Http\Requests;

use App\PodcastEpisodeResource;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEpisodeResourceRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_resource_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'resource'           => [
                'required',
            ],
            'podcast_episode_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
