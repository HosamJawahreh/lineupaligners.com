<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactRequestController extends Controller
{
    public function index(): View
    {
        $inquiries = WebsiteContactInquiry::query()
            ->contactRequests()
            ->latest()
            ->paginate(20);

        return view('admin.contact-requests.index', [
            'inquiries' => $inquiries,
            'unreadCount' => WebsiteContactInquiry::query()->contactRequests()->unread()->count(),
        ]);
    }

    public function show(WebsiteContactInquiry $contactRequest): View
    {
        abort_unless(in_array($contactRequest->form_type, ['contact', 'appointment'], true), 404);

        $contactRequest->markRead((int) auth()->id());

        return view('admin.contact-requests.show', [
            'inquiry' => $contactRequest->fresh(),
        ]);
    }

    public function markRead(WebsiteContactInquiry $contactRequest): RedirectResponse
    {
        abort_unless(in_array($contactRequest->form_type, ['contact', 'appointment'], true), 404);

        $contactRequest->markRead((int) auth()->id());

        return back()->with('success', 'Contact request marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        WebsiteContactInquiry::query()
            ->contactRequests()
            ->unread()
            ->update([
                'read_at' => now(),
                'read_by' => auth()->id(),
            ]);

        return back()->with('success', 'All contact requests marked as read.');
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => WebsiteContactInquiry::query()->contactRequests()->unread()->count(),
        ]);
    }
}
