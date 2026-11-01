<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Dispute;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status', 'all');
        $type = $request->get('type', 'all');
        $search = trim((string) $request->get('search', ''));

        $query = Dispute::with([
            'complainant.buyer', 'complainant.seller', 'complainant.logisticsPartner',
            'respondent.buyer', 'respondent.seller', 'respondent.logisticsPartner',
        ])->latest();

        if (array_key_exists($status, Dispute::STATUSES)) {
            $query->where('status', $status);
        }

        if (array_key_exists($type, Dispute::TYPES)) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $needle = "%{$search}%";
            $query->where(function ($q) use ($needle, $search) {
                $q->where('case_no', 'like', $needle)
                    ->orWhere('subject', 'like', $needle)
                    ->orWhereHas('complainant', fn ($u) => $u->where('email', 'like', $needle))
                    ->orWhereHas('respondent', fn ($u) => $u->where('email', 'like', $needle));
                if (ctype_digit($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }

        $disputes = $query->paginate(10)->withQueryString();

        $avgHours = Dispute::whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at'])
            ->avg(fn ($d) => $d->created_at->diffInHours($d->resolved_at));

        $counts = [
            'open' => Dispute::where('status', 'open')->count(),
            'under_review' => Dispute::where('status', 'under_review')->count(),
            'awaiting_response' => Dispute::where('status', 'awaiting_response')->count(),
            'resolved' => Dispute::whereIn('status', Dispute::CLOSED)->count(),
            'all' => Dispute::count(),
            'avg_hours' => $avgHours === null ? null : round($avgHours, 1),
        ];

        return view('admin.complaints-disputes', compact('disputes', 'counts', 'status', 'type', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Dispute::TYPES))],
            'priority' => ['required', Rule::in(array_keys(Dispute::PRIORITIES))],
            'subject' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'complainant_email' => ['nullable', 'email', 'exists:users,email'],
            'respondent_email' => ['nullable', 'email', 'exists:users,email'],
        ], [
            'complainant_email.exists' => 'No user found with that complainant email.',
            'respondent_email.exists' => 'No user found with that respondent email.',
            'order_id.exists' => 'That order number does not exist.',
        ]);

        $dispute = Dispute::create([
            'type' => $data['type'],
            'priority' => $data['priority'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'order_id' => $data['order_id'] ?? null,
            'filed_by' => $this->userIdByEmail($data['complainant_email'] ?? null),
            'against_user_id' => $this->userIdByEmail($data['respondent_email'] ?? null),
            'status' => 'open',
        ]);

        $dispute->update(['case_no' => 'DSP-'.str_pad((string) $dispute->id, 5, '0', STR_PAD_LEFT)]);

        return redirect()->route('admin.disputes.show', $dispute)
            ->with('status', "Case {$dispute->case_no} was created.");
    }

    public function show(Dispute $dispute): View
    {
        $dispute->load([
            'complainant.buyer', 'complainant.seller', 'complainant.logisticsPartner',
            'respondent.buyer', 'respondent.seller', 'respondent.logisticsPartner',
            'resolver', 'messages.author',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function updateStatus(Request $request, Dispute $dispute): RedirectResponse
    {
        if ($dispute->is_closed) {
            return back()->withErrors(['status' => 'This case is closed. Reopen it first.']);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'under_review', 'awaiting_response'])],
        ]);

        $dispute->update(['status' => $data['status']]);
        $this->log($dispute, 'Status changed to '.Dispute::STATUSES[$data['status']].'.');

        return back()->with('status', 'Status updated.');
    }

    public function message(Request $request, Dispute $dispute): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $dispute->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_internal' => true,
        ]);

        return back()->with('status', 'Note added.');
    }

    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        if ($dispute->is_closed) {
            return back()->withErrors(['resolution_note' => 'This case is already closed.']);
        }

        $data = $request->validate([
            'resolution_outcome' => ['required', Rule::in(array_keys(Dispute::OUTCOMES))],
            'resolution_note' => ['required', 'string', 'max:3000'],
        ]);

        $dispute->update([
            'status' => $data['resolution_outcome'] === 'rejected' ? 'rejected' : 'resolved',
            'resolution_outcome' => $data['resolution_outcome'],
            'resolution_note' => $data['resolution_note'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $this->log($dispute, 'Case closed: '.Dispute::OUTCOMES[$data['resolution_outcome']].'.');

        return back()->with('status', 'Case closed.');
    }

    public function reopen(Dispute $dispute): RedirectResponse
    {
        abort_unless($dispute->is_closed, 404);

        $dispute->update([
            'status' => 'under_review',
            'resolution_outcome' => null,
            'resolution_note' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);
        $this->log($dispute, 'Case reopened.');

        return back()->with('status', 'Case reopened.');
    }

    private function userIdByEmail(?string $email): ?int
    {
        return $email ? User::where('email', $email)->value('id') : null;
    }

    private function log(Dispute $dispute, string $text): void
    {
        $dispute->messages()->create([
            'user_id' => auth()->id(),
            'body' => '[System] '.$text,
            'is_internal' => true,
        ]);
    }
}