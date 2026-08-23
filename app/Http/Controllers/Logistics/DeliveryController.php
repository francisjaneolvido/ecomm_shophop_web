<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function board(): View
    {
        // TODO: replace with real order/delivery queries scoped to this partner.

        $columns = [
            'new' => [
                'label' => 'New pickup requests',
                'items' => [
                    ['id' => 'SH-48213', 'seller' => 'ShopHop Tech Store', 'meta' => 'Bacoor, Cavite'],
                    ['id' => 'SH-48219', 'seller' => 'Glow & Co Beauty', 'meta' => 'Imus, Cavite'],
                ],
            ],
            'assigned' => [
                'label' => 'Assigned',
                'items' => [
                    ['id' => 'SH-48201', 'seller' => 'Home Essentials PH', 'meta' => 'Rider: Miguel R.', 'status' => 'Pickup pending'],
                ],
            ],
            'transit' => [
                'label' => 'In transit',
                'items' => [
                    ['id' => 'SH-48177', 'seller' => 'Pantry Bundle Co.', 'meta' => 'Rider: Angela C.', 'status' => 'On the way'],
                    ['id' => 'SH-48180', 'seller' => 'FitGear Sports', 'meta' => 'Rider: Jerome D.', 'status' => 'On the way'],
                ],
            ],
            'done' => [
                'label' => 'Delivered / issues',
                'items' => [
                    ['id' => 'SH-48090', 'seller' => 'Little Ones Baby Shop', 'meta' => 'Rider: Kaye P.', 'status' => 'Delivered'],
                    ['id' => 'SH-48072', 'seller' => 'AutoParts Direct', 'meta' => 'Rider: Miguel R.', 'status' => 'Failed — retry'],
                ],
            ],
        ];

        return view('logistics.deliveries.board', compact('columns'));
    }

    public function assign(Request $request, string $delivery): RedirectResponse
    {
        $request->validate(['rider_id' => ['required']]);

        // TODO: assign the delivery to the chosen rider, notify both parties.

        return back()->with('status', "Delivery #{$delivery} assigned.");
    }
}
