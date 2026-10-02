<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BuyerCheckoutService;
use App\Models\Buyer\Cart\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileBuyerCheckoutController extends Controller
{
    public function __construct(private readonly BuyerCheckoutService $checkout)
    {
    }

    public function preview(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_method' => ['nullable', 'array'],
            'shipping_method.*' => ['required', 'in:standard,express'],
            'payment_method' => ['nullable', 'in:cod,online'],
            'voucher_code' => ['nullable', 'string', 'max:120'],
        ]);

        $preview = $this->checkout->preview(
            buyer: $buyer,
            selectedIds: array_keys($data['items']),
            quantityOverrides: collect($data['items'])
                ->mapWithKeys(fn (array $item, $key) => [(string) $key => (int) $item['quantity']])
                ->all(),
            voucherCode: (string) ($data['voucher_code'] ?? ''),
            shippingMethods: $data['shipping_method'] ?? [],
            paymentMethod: (string) ($data['payment_method'] ?? 'cod'),
            strictVoucher: trim((string) ($data['voucher_code'] ?? '')) !== '',
        );

        return response()->json([
            'success' => true,
            'message' => 'Checkout preview loaded.',
            'data' => $preview,
        ]);
    }

    public function place(Request $request): JsonResponse
    {
        $buyer = $this->approvedBuyer($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_method' => ['required', 'array'],
            'shipping_method.*' => ['required', 'in:standard,express'],
            'payment_method' => ['required', 'in:cod,online'],
            'voucher_code' => ['nullable', 'string', 'max:120'],
            'groups' => ['nullable', 'array'],
            'groups.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->checkout->placeOrder($buyer, $data);

        return response()->json([
            'success' => true,
            'message' => $result['payment_id']
                ? 'Orders placed. Submit payment proof for ShopHop Admin review.'
                : 'Order placed successfully.',
            'data' => [
                ...$result,
                'cart_count' => CartItem::where('buyer_id', $buyer->id)->count(),
            ],
        ], 201);
    }

    private function approvedBuyer(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401, 'Unauthenticated.');
        abort_unless($user->account_type === 'buyer', 403, 'Buyer account required.');
        abort_unless($user->email_verified_at, 403, 'Email verification required.');
        abort_unless($user->status === 'approved', 403, 'Approved buyer account required.');
        abort_unless($user->buyer, 403, 'Buyer profile not found.');

        return $user->buyer;
    }
}
