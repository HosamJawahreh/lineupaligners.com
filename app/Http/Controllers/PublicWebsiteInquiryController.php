<?php

namespace App\Http\Controllers;

use App\Mail\WebsiteInquiryConfirmationMail;
use App\Mail\WebsiteInquiryMail;
use App\Models\Setting;
use App\Models\WebsiteContactInquiry;
use App\Services\WebsiteContent;
use App\Support\LineUpMailBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                'phone' => ['required', 'string', 'max:40'],
                'message' => ['required', 'string', 'max:5000'],
                'form_type' => ['nullable', 'string', 'in:contact,appointment,newsletter'],
            ]);
        }

        $inquiryAttributes = [
            'name' => trim((string) $data['name']),
            'email' => trim((string) $data['email']),
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'message' => trim((string) $data['message']),
            'form_type' => $formType,
            'locale' => app()->getLocale(),
            'ip_address' => (string) $request->ip(),
        ];

        $inquiry = new WebsiteContactInquiry($inquiryAttributes);

        try {
            $inquiry->save();
        } catch (\Throwable $e) {
            Log::error('Website inquiry save failed: '.$e->getMessage(), [
                'email' => $inquiryAttributes['email'],
                'form_type' => $formType,
            ]);

            return response()->json(['message' => __('website.inquiry_error')], 500);
        }

        $mailPayload = [
            'name' => $inquiryAttributes['name'],
            'email' => $inquiryAttributes['email'],
            'phone' => $inquiryAttributes['phone'],
            'subject' => $data['subject'] ?? null,
            'message' => $inquiryAttributes['message'],
            'form_type' => $inquiryAttributes['form_type'],
        ];

        $contact = $website->all(app()->getLocale())['contact'] ?? [];
        $recipient = filled($contact['email'] ?? null)
            ? (string) $contact['email']
            : (string) Setting::get('clinic_email', '');

        Log::info('Website inquiry received', [
            'id' => $inquiry->id,
            'email' => $inquiryAttributes['email'],
            'form_type' => $formType,
        ]);

        $this->dispatchInquiryMail(
            mailPayload: $mailPayload,
            recipient: $recipient,
            clientEmail: $inquiryAttributes['email'],
            inquirerName: $inquiryAttributes['name'],
            formType: $formType,
            locale: app()->getLocale(),
            ipAddress: (string) $request->ip(),
        );

        return response()->json([
            'ok' => true,
            'message' => __('website.inquiry_success'),
        ]);
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null, subject?: string|null, message: string, form_type?: string|null}  $mailPayload
     */
    protected function dispatchInquiryMail(
        array $mailPayload,
        string $recipient,
        string $clientEmail,
        string $inquirerName,
        string $formType,
        string $locale,
        string $ipAddress,
    ): void {
        dispatch(function () use ($mailPayload, $recipient, $clientEmail, $inquirerName, $formType, $locale, $ipAddress) {
            LineUpMailBranding::applyGlobalConfig();

            if (filled($recipient)) {
                try {
                    Mail::to($recipient)->send(new WebsiteInquiryMail(
                        inquiry: $mailPayload,
                        ipAddress: $ipAddress,
                        locale: $locale,
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Website inquiry mail failed: '.$e->getMessage(), [
                        'recipient' => $recipient,
                        'form_type' => $formType,
                    ]);
                }
            }

            if ($formType !== 'newsletter') {
                try {
                    Mail::to($clientEmail)->send(new WebsiteInquiryConfirmationMail(
                        inquirerName: $inquirerName,
                        inquiryMessage: (string) $mailPayload['message'],
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Website inquiry confirmation mail failed: '.$e->getMessage(), [
                        'email' => $clientEmail,
                        'form_type' => $formType,
                    ]);
                }
            }
        })->afterResponse();
    }
}
