<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderController extends Controller
{
    public function index(): View
    {
        // TODO: replace with real queries, e.g.
        // $applications = $partner->riders()->pending()->get();
        // $activeRiders = $partner->riders()->active()->get();

        $applications = [
            ['id' => 1, 'name' => 'Miguel Reyes', 'vehicle' => 'Motorcycle · NGA-2231', 'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true]],
            ['id' => 2, 'name' => 'Angela Cruz', 'vehicle' => 'Motorcycle · KLM-8842', 'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true]],
            ['id' => 3, 'name' => 'Jerome Delos Santos', 'vehicle' => 'Tricycle · TRC-0917', 'docs' => ['ID' => true, 'License' => true, 'OR/CR' => false]],
            ['id' => 4, 'name' => 'Kaye Perez', 'vehicle' => 'Motorcycle · PQR-4470', 'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true]],
        ];

        $activeRiders = [
            ['id' => 11, 'name' => 'Ramon Villanueva', 'vehicle' => 'Motorcycle · MNB-1123', 'zone' => 'Bacoor, Cavite', 'completion' => 98, 'rating' => 4.9, 'status' => 'active'],
            ['id' => 12, 'name' => 'Carla Mendoza', 'vehicle' => 'Motorcycle · JKL-7765', 'zone' => 'Imus, Cavite', 'completion' => 96, 'rating' => 4.8, 'status' => 'active'],
            ['id' => 13, 'name' => 'Paolo Ramos', 'vehicle' => 'Tricycle · TRC-4402', 'zone' => 'Dasmariñas, Cavite', 'completion' => 89, 'rating' => 4.4, 'status' => 'suspended'],
        ];

        return view('logistics.riders.index', compact('applications', 'activeRiders'));
    }

    public function approve(Request $request, int $rider): RedirectResponse
    {
        // TODO: mark the rider application approved, notify by email.
        return back()->with('status', 'Rider application approved.');
    }

    public function disapprove(Request $request, int $rider): RedirectResponse
    {
        // TODO: mark the rider application disapproved, notify by email.
        return back()->with('status', 'Rider application disapproved.');
    }

    public function suspend(Request $request, int $rider): RedirectResponse
    {
        // TODO: suspend the rider's account.
        return back()->with('status', 'Rider suspended.');
    }

    public function activate(Request $request, int $rider): RedirectResponse
    {
        // TODO: reactivate the rider's account.
        return back()->with('status', 'Rider activated.');
    }
}
