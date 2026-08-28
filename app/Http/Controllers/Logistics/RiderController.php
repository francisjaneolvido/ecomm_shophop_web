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
            [
                'id' => 1,
                'name' => 'Miguel Reyes',
                'vehicle' => 'Motorcycle',
                'plate_number' => 'NGA-2231',
                'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true],
                'doc_files' => [
                    'ID' => null,
                    'License' => null,
                    'OR/CR' => null,
                ],
                'last_name' => 'Reyes', 'first_name' => 'Miguel', 'middle_initial' => 'S',
                'sex' => 'Male', 'email' => 'miguel.reyes@example.com', 'contact_no' => '0917 123 4567',
                'birthday' => 'March 14, 1997', 'age' => 29,
                'province' => 'Cavite', 'municipality' => 'Bacoor', 'barangay' => 'Molino IV',
                'street' => 'Molino Blvd.', 'house_number' => '12',
                'submitted_at' => 'Aug 20, 2026',
            ],
            [
                'id' => 2,
                'name' => 'Angela Cruz',
                'vehicle' => 'Motorcycle',
                'plate_number' => 'KLM-8842',
                'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true],
                'doc_files' => [
                    'ID' => null,
                    'License' => null,
                    'OR/CR' => null,
                ],
                'last_name' => 'Cruz', 'first_name' => 'Angela', 'middle_initial' => 'M',
                'sex' => 'Female', 'email' => 'angela.cruz@example.com', 'contact_no' => '0918 234 5678',
                'birthday' => 'July 2, 1999', 'age' => 27,
                'province' => 'Cavite', 'municipality' => 'Imus', 'barangay' => 'Anabu I-A',
                'street' => 'Aguinaldo Hwy.', 'house_number' => '45',
                'submitted_at' => 'Aug 21, 2026',
            ],
            [
                'id' => 3,
                'name' => 'Jerome Delos Santos',
                'vehicle' => 'Tricycle',
                'plate_number' => 'TRC-0917',
                'docs' => ['ID' => true, 'License' => true, 'OR/CR' => false],
                'doc_files' => [
                    'ID' => null,
                    'License' => null,
                    'OR/CR' => null,
                ],
                'last_name' => 'Delos Santos', 'first_name' => 'Jerome', 'middle_initial' => 'P',
                'sex' => 'Male', 'email' => 'jerome.delossantos@example.com', 'contact_no' => '0919 345 6789',
                'birthday' => 'Nov 9, 1995', 'age' => 30,
                'province' => 'Cavite', 'municipality' => 'Dasmariñas', 'barangay' => 'Zone II',
                'street' => 'Congressional Rd.', 'house_number' => '78',
                'submitted_at' => 'Aug 19, 2026',
            ],
            [
                'id' => 4,
                'name' => 'Kaye Perez',
                'vehicle' => 'Motorcycle',
                'plate_number' => 'PQR-4470',
                'docs' => ['ID' => true, 'License' => true, 'OR/CR' => true],
                'doc_files' => [
                    'ID' => null,
                    'License' => null,
                    'OR/CR' => null,
                ],
                'last_name' => 'Perez', 'first_name' => 'Kaye', 'middle_initial' => 'L',
                'sex' => 'Female', 'email' => 'kaye.perez@example.com', 'contact_no' => '0920 456 7890',
                'birthday' => 'Jan 25, 1998', 'age' => 28,
                'province' => 'Cavite', 'municipality' => 'Bacoor', 'barangay' => 'Salinas III',
                'street' => 'Tirona Hwy.', 'house_number' => '9',
                'submitted_at' => 'Aug 22, 2026',
            ],
        ];

        $activeRiders = [
            [
                'id' => 11,
                'name' => 'Ramon Villanueva',
                'vehicle' => 'Motorcycle',
                'plate_number' => 'MNB-1123',
                'zone' => 'Bacoor, Cavite',
                'completion' => 98,
                'rating' => 4.9,
                'status' => 'active',
                'deliveries' => [
                    ['date' => '2026-08-20', 'order_id' => 'SH-10231', 'customer' => 'Liza Fernandez', 'status' => 'Delivered'],
                    ['date' => '2026-08-18', 'order_id' => 'SH-10198', 'customer' => 'Noel Aquino', 'status' => 'Delivered'],
                    ['date' => '2026-07-30', 'order_id' => 'SH-09877', 'customer' => 'Bea Santiago', 'status' => 'Delivered'],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => null],
                    ['label' => "Driver's License", 'url' => null],
                ],
                'warnings' => [],
            ],
            [
                'id' => 12,
                'name' => 'Carla Mendoza',
                'vehicle' => 'Motorcycle',
                'plate_number' => 'JKL-7765',
                'zone' => 'Imus, Cavite',
                'completion' => 96,
                'rating' => 4.8,
                'status' => 'active',
                'deliveries' => [
                    ['date' => '2026-08-21', 'order_id' => 'SH-10245', 'customer' => 'Mark Villareal', 'status' => 'Delivered'],
                    ['date' => '2026-08-15', 'order_id' => 'SH-10150', 'customer' => 'Grace Ong', 'status' => 'Delivered'],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => null],
                    ['label' => "Driver's License", 'url' => null],
                ],
                'warnings' => [],
            ],
            [
                'id' => 13,
                'name' => 'Paolo Ramos',
                'vehicle' => 'Tricycle',
                'plate_number' => 'TRC-4402',
                'zone' => 'Dasmariñas, Cavite',
                'completion' => 89,
                'rating' => 4.4,
                'status' => 'suspended',
                'deliveries' => [
                    ['date' => '2026-08-19', 'order_id' => 'SH-10220', 'customer' => 'Tessa Uy', 'status' => 'Failed'],
                    ['date' => '2026-08-10', 'order_id' => 'SH-10077', 'customer' => 'Rico Salazar', 'status' => 'Delivered'],
                    ['date' => '2026-07-22', 'order_id' => 'SH-09754', 'customer' => 'Jean Cabrera', 'status' => 'Delivered'],
                ],
                'documents' => [
                    ['label' => 'OR/CR', 'url' => null],
                    ['label' => "Driver's License", 'url' => null],
                ],
                'warnings' => [
                    ['type' => 'Late Delivery', 'date' => '2026-08-05', 'details' => 'Delivered 40 minutes past the committed window.', 'severity' => 'minor'],
                    ['type' => 'Customer Complaint', 'date' => '2026-07-28', 'details' => 'Customer reported rude behavior during handoff.', 'severity' => 'major'],
                ],
            ],
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

    public function warn(Request $request, int $rider): RedirectResponse
    {
        $request->validate([
            'type' => 'required|string|max:100',
            'severity' => 'required|in:minor,major',
            'details' => 'required|string|max:500',
        ]);

        // TODO: persist this to a rider_warnings table once the Rider model exists, e.g.
        // Rider::findOrFail($rider)->warnings()->create($request->only('type', 'severity', 'details'));

        return back()->with('status', 'Warning issued to rider.');
    }
}