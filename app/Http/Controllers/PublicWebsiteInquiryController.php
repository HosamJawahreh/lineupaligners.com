<?php

namespace App\Http\Controllers;

use App\Mail\WebsiteInquiryConfirmationMail;
use App\Mail\WebsiteInquiryMail;
use App\Models\Setting;
use App\Models\WebsiteContactInquiry;
use App\Services\WebsiteContent;
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

        $inquiry = WebsiteContactInquiry::create([
            'name' => trim((string) $data['name']),
            'email' => trim((string) $data['email']),
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'message' => trim((string) $data['message']),
            'form_type' => $formType,
            'locale' => app()->getLocale(),
            'ip_address' => (string) $request->ip(),
        ]);

        $mailPayload = [
            'name' => $inquiry->name,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'subject' => $data['subject'] ?? null,
            'message' => $inquiry->message,
            'form_type' => $inquiry->form_type,
        ];

        $contact = $website->all(app()->getLocale())['contact'] ?? [];
        $recipient = filled($contact['email'] ?? null)
            ? (string) $contact['email']
            : (string) Setting::get('clinic_email', '');

        Log::info('Website inquiry received', [
            'id' => $inquiry->id,
            'email' => $inquiry->email,
            'form_type' => $inquiry->form_type,
        ]);

        if (filled($recipient)) {
            try {
                Mail::to($recipient)->send(new WebsiteInquiryMail(
                    inquiry: $mailPayload,
                    ipAddress: (string) $request->ip(),
                    locale: app()->getLocale(),
                ));
            } catch (\Throwable $e) {
                Log::warning('Website inquiry mail failed: '.$e->getMessage());
            }
        }

        if ($formType !== 'newsletter') {
            try {
                Mail::to($inquiry->email)->send(new WebsiteInquiryConfirmationMail($inquiry));
            } catch (\Throwable $e) {
                Log::warning('Website inquiry confirmation mail failed: '.$e->getMessage());
            }
        }

        return response()->json([
            'ok' => true,
            'message' => __('website.inquiry_success'),
        ]);
    }
}
