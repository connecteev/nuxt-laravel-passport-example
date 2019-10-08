<?php

namespace App\Http\Requests;

use App\PodcastEpisodeTakeaway;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdatePodcastEpisodeTakeawayRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_takeaway_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'takeaway'           => [
                'required',
            ],
            'podcast_episode_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
