<?php

namespace App\Http\Requests;

use App\PodcastEpisodeIndustryType;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPodcastEpisodeIndustryTypeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_industry_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:podcast_episode_industry_types,id',
        ];
    }
}
