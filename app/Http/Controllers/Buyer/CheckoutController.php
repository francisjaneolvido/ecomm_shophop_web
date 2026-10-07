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
            'initialVoucherCode' => $preview['initial_voucher_code'],
            'itemCount' => $preview['item_count'],
            'shopCount' => $preview['shop_count'],
            'codFee' => $preview['cod_fee_per_shop'],
        ]);
    }

    public function placeOrder(Request $request)
    {
        $buyer = Auth::user()->buyer;

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_method' => 'required|array',
            'shipping_method.*' => 'required|in:standard,express',
            'payment_method' => 'required|in:cod,online',
            'voucher_code' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*.note' => 'nullable|string|max:500',
        ]);

        $result = $this->checkout->placeOrder($buyer, $data);

        if ($result['payment_id']) {
            return redirect()
                ->route('buyer.payments.show', $result['payment_id'])
                ->with('status', 'Orders placed. Submit your payment reference and receipt for Admin review.');
        }

        return redirect()
            ->route('buyer.orders')
            ->with('status', 'Order placed successfully!');
    }
}
