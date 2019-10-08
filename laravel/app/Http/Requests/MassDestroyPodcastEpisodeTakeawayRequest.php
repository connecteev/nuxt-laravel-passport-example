<?php

namespace App\Http\Requests;

use App\PodcastEpisodeTakeaway;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPodcastEpisodeTakeawayRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_takeaway_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:podcast_episode_takeaways,id',
        ];
    }
}
