<?php

namespace App\Http\Requests;

use App\UserNotificationSetting;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyUserNotificationSettingRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('user_notification_setting_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:user_notification_settings,id',
        ];
    }
}
