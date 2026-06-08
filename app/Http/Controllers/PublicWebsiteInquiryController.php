<?php



namespace App\Http\Controllers;



use App\Mail\WebsiteInquiryMail;

use App\Models\Setting;

use App\Services\WebsiteContent;

use App\Services\WebsiteLocale;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;

use Illuminate\Validation\ValidationException;



class PublicWebsiteInquiryController extends Controller

{

    public function store(Request $request, WebsiteContent $website): JsonResponse

    {

        if (filled($request->input('website_hp'))) {

            return response()->json(['ok' => true, 'message' => __('website.inquiry_success')]);

        }



        if (! ($website->all()['published'] ?? false)) {

            return response()->json(['message' => __('website.inquiry_error')], 403);

        }



        $formType = $request->input('form_type', 'contact');



        if ($formType === 'newsletter') {

            $data = $request->validate([

                'email' => ['required', 'email', 'max:190'],

                'form_type' => ['nullable', 'string', 'in:newsletter'],

            ]);

            $data['name'] = __('website.newsletter_subscriber');

            $data['message'] = __('website.newsletter_request');

            $data['subject'] = __('website.newsletter_title');

            $data['phone'] = null;

        } else {

            $data = $request->validate([

                'name' => ['required', 'string', 'max:120'],

                'email' => ['required', 'email', 'max:190'],

                'phone' => ['nullable', 'string', 'max:40'],

                'subject' => ['nullable', 'string', 'max:190'],

                'message' => ['required', 'string', 'max:5000'],

                'form_type' => ['nullable', 'string', 'in:contact,appointment,newsletter'],

            ]);

        }



        $contact = $website->all(app()->getLocale())['contact'] ?? [];

        $recipient = filled($contact['email'] ?? null)

            ? (string) $contact['email']

            : (string) Setting::get('clinic_email', '');



        Log::info('Website inquiry received', [

            'email' => $data['email'],

            'form_type' => $data['form_type'] ?? 'contact',

        ]);



        if (filled($recipient)) {

            try {

                Mail::to($recipient)->send(new WebsiteInquiryMail(

                    inquiry: $data,

                    ipAddress: (string) $request->ip(),

                    locale: app()->getLocale(),

                ));

            } catch (\Throwable $e) {

                Log::warning('Website inquiry mail failed: '.$e->getMessage());

            }

        }



        return response()->json([

            'ok' => true,

            'message' => __('website.inquiry_success'),

        ]);

    }

}

