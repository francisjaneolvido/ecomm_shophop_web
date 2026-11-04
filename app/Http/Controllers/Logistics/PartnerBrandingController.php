<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\LogisticsPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Only the approved, authenticated company owner manages its public Rider-card identity. */
class PartnerBrandingController extends Controller
{
    public function edit(Request $request): View
    {
        $partner = LogisticsPartner::query()
            ->where('user_id', $request->user()->id)->firstOrFail();

        return view('logistics.branding.edit', ['partner' => $partner]);
    }

    public function update(Request $request): RedirectResponse
    {
        $partner = LogisticsPartner::query()
            ->where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=2400,max_height=2400'],
            'remove_logo' => ['sometimes', 'boolean'],
            'public_description' => ['nullable', 'string', 'max:280'],
            'accepting_riders' => ['required', 'boolean'],
        ]);

        $oldLogo = $partner->logo_path;
        $newLogo = null;

        if ($request->hasFile('logo')) {
            // Public logos are separate from private business permits and IDs.
            $newLogo = $request->file('logo')->store('logistics-partner-logos', 'public');
            if (! $newLogo) {
                return back()->withErrors(['logo' => 'Logo could not be uploaded. Please try again.']);
            }
        }

        $partner->public_description = trim((string) ($data['public_description'] ?? '')) ?: null;
        $partner->accepting_riders = (bool) $data['accepting_riders'];
        if ($newLogo !== null) {
            $partner->logo_path = $newLogo;
        } elseif ((bool) ($data['remove_logo'] ?? false)) {
            $partner->logo_path = null;
        }

        try {
            $partner->saveOrFail();
        } catch (\Throwable $e) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }
            throw $e;
        }

        if ($oldLogo && $oldLogo !== $partner->logo_path) {
            Storage::disk('public')->delete($oldLogo);
        }

        return back()->with('status', 'Public Rider application profile updated.');
    }
}
