<?php

namespace App\Http\Requests;

use App\PodcastEpisodeAudience;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdatePodcastEpisodeAudienceRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_audience_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'audience_name' => [
                'max:255',
                'required',
                'unique:podcast_episode_audiences,audience_name,' . request()->route('podcast_episode_audience')->id,
            ],
            'audience_slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_audiences,audience_slug,' . request()->route('podcast_episode_audience')->id,
            ],
        ];
    }
}
