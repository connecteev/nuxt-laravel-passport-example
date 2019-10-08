<?php

namespace App\Http\Requests;

use App\PodcastEpisodeAudience;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPodcastEpisodeAudienceRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_audience_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:podcast_episode_audiences,id',
        ];
    }
}
