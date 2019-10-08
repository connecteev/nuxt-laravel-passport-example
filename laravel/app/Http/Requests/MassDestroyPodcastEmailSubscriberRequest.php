<?php

namespace App\Http\Requests;

use App\PodcastEmailSubscriber;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyPodcastEmailSubscriberRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('podcast_email_subscriber_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:podcast_email_subscribers,id',
        ];
    }
}
