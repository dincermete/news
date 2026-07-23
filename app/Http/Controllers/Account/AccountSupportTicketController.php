<?php

namespace App\Http\Controllers\Account;

use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSupportTicketController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();
        $statusFilter = $request->string('status')->toString();
        $search = $request->string('q')->trim()->toString();

        $base = SupportTicket::query()->where('user_id', $user->id);

        $counts = [
            'all' => (clone $base)->count(),
            'open' => (clone $base)->where('status', SupportTicketStatus::Open)->count(),
            'answered' => (clone $base)->where('status', SupportTicketStatus::InProgress)->count(),
            'closed' => (clone $base)->where('status', SupportTicketStatus::Closed)->count(),
        ];

        $tickets = SupportTicket::query()
            ->where('user_id', $user->id)
            ->when($statusFilter === 'open', fn ($q) => $q->where('status', SupportTicketStatus::Open))
            ->when($statusFilter === 'answered', fn ($q) => $q->where('status', SupportTicketStatus::InProgress))
            ->when($statusFilter === 'closed', fn ($q) => $q->where('status', SupportTicketStatus::Closed))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($inner) use ($search): void {
                    $inner->where('subject', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('account.support-tickets', [
            'meta' => $seo->forDefault(),
            'tickets' => $tickets,
            'counts' => $counts,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        SupportTicket::query()->create([
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => SupportTicketStatus::Open,
            'source' => SupportTicketSource::Manual,
        ]);

        return redirect()
            ->route('account.support-tickets')
            ->with('status', 'Destek talebiniz oluşturuldu.');
    }
}
