<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function show()
    {
        return view('admin.platform-settings', ['setting' => PlatformSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:80'],
            'description'         => ['nullable', 'string', 'max:255'],
            'support_email'       => ['nullable', 'email', 'max:160'],
            'support_phone'       => ['nullable', 'string', 'max:40'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ]);
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');

        PlatformSetting::current()->update($data);

        return back()->with('status', 'Platform settings saved.');
    }
}