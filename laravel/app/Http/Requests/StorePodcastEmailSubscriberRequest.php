<?php

namespace App\Http\Requests;

use App\PodcastEmailSubscriber;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StorePodcastEmailSubscriberRequest extends FormRequest
{
    public function authorize()
    {
        //abort_if(Gate::denies('podcast_email_subscriber_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name'  => [
                'max:255',
            ],
            'email' => [
                'required',
                'unique:podcast_email_subscribers',
            ],
        ];
    }
}
