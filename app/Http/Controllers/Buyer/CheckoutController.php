<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Services\BuyerCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(private readonly BuyerCheckoutService $checkout)
    {
    }

    public function index(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $selectedIds = collect($request->query('items', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $queryQty = collect($request->query('qty', []))->toArray();
        $queryVoucher = strtoupper(trim((string) $request->query('voucher', '')));

        try {
            $preview = $this->checkout->preview(
                buyer: $buyer,
                selectedIds: $selectedIds,
                quantityOverrides: $queryQty,
                voucherCode: $queryVoucher,
                paymentMethod: 'cod',
                strictVoucher: false,
                platformVoucherCode: (string) $request->query('merchandise_voucher_code', ''),
                shippingVoucherCode: (string) $request->query('shipping_voucher_code', ''),
                logisticsChoices: (array) $request->query('logistics_partner', []),
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->route('buyer.cart')
                ->withErrors($exception->errors());
        }

        return view('buyer.checkout.cart-checkout', [
            'address' => $preview['address'],
            'cartGroups' => $preview['cart_groups'],
            'availableVouchers' => $preview['available_vouchers'],
            'platformVouchers' => $preview['available_platform_vouchers'],
            'initialVoucherCode' => $preview['initial_voucher_code'],
            'platformVoucherCode' => $preview['platform_voucher_code'],
            'shippingVoucherCode' => $preview['shipping_voucher_code'],
            'initialPlatformDiscount' => $preview['summary']['platform_discount'],
            'initialShippingDiscount' => $preview['summary']['shipping_discount'],
            'itemCount' => $preview['item_count'],
            'shopCount' => $preview['shop_count'],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_method' => 'required|array',
            'shipping_method.*' => 'required|in:standard',
            'payment_method' => 'required|in:cod',
            'voucher_code' => 'nullable|string',
            'merchandise_voucher_code' => 'nullable|string|max:40',
            'shipping_voucher_code' => 'nullable|string|max:40',
            // Optional courier per shop; the service rechecks coverage before saving it.
            'logistics_partner' => 'nullable|array',
            'logistics_partner.*' => 'nullable|integer',
            'groups' => 'nullable|array',
            'groups.*.note' => 'nullable|string|max:500',
        ]);

        $this->checkout->placeOrder($buyer, $data);

        return redirect()
            ->route('buyer.orders')
            ->with('status', 'Order placed successfully!');
    }
}