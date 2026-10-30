<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** Session-authenticated Web scanner pages for approved Branch/Hub roles. */
class WebScannerController extends Controller
{
    public function branch(): View
    {
        return view('logistics.web-scanner', [
            'scannerLayout' => 'branch.layout.app',
            'scannerRole' => 'branch',
            'scannerPrefix' => 'branch.scanner.api.',
        ]);
    }

    public function hub(): View
    {
        return view('logistics.web-scanner', [
            'scannerLayout' => 'hub.layouts.console',
            'scannerRole' => 'hub',
            'scannerPrefix' => 'logistics.hub.scanner.api.',
        ]);
    }
}
