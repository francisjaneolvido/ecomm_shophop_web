<?php

namespace App\Http\Controllers;

use App\Models\Admin\Dispute;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HelpCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canReport = $user && $user->account_type === 'buyer' && $user->status === 'approved' && $user->email_verified_at;
        $helpRequests = $canReport
            ? Dispute::query()->where('filed_by', $user->id)->latest()->orderByDesc('id')->paginate(5, ['*'], 'requests_page')
            : null;
        $settings = PlatformSetting::current();
        $supportEmail = filter_var($settings->support_email, FILTER_VALIDATE_EMAIL) ? $settings->support_email : null;
        $supportPhone = $settings->support_phone;
        return response()->view('buyer.help.index', compact('canReport', 'helpRequests', 'supportEmail', 'supportPhone'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['account', 'delivery', 'product', 'other'])],
            'subject' => ['required', 'string', 'min:3', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:4000'],
        ]);
        $report = DB::transaction(function () use ($request, $data) {
            // Buyer identity and administrative state always come from the server.
            $report = Dispute::create($data + ['filed_by' => $request->user()->id, 'status' => 'open', 'priority' => 'normal']);
            $report->update(['case_no' => 'DSP-'.str_pad((string) $report->id, 5, '0', STR_PAD_LEFT)]);
            return $report;
        });
        return redirect()->to(route('buyer.help').'#support-requests')
            ->with('help_status', 'Your report '.$report->case_no.' was saved. Check its status below.');
    }
}
