<?php

namespace App\Http\Requests;

use App\PodcastEpisodeIndustryType;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdatePodcastEpisodeIndustryTypeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_episode_industry_type_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'max:255',
                'required',
                'unique:podcast_episode_industry_types,name,' . request()->route('podcast_episode_industry_type')->id,
            ],
            'slug' => [
                'max:255',
                'required',
                'unique:podcast_episode_industry_types,slug,' . request()->route('podcast_episode_industry_type')->id,
            ],
        ];
    }
}
