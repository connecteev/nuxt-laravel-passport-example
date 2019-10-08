<?php

namespace App\Http\Controllers\Api\V1\Open;

use App\ContactForm;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactFormRequest;
use App\Http\Requests\UpdateContactFormRequest;
use App\Http\Resources\Admin\ContactFormResource;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactFormApiController extends Controller
{
    public function store(StoreContactFormRequest $request)
    {
        $contactForm = ContactForm::create($request->all());

        return (new ContactFormResource($contactForm))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

}
