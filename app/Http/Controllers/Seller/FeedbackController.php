<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\SellerFeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request, SellerFeedbackService $feedback)
    {
        return view('seller.feedback', $feedback->listing($request));
    }

    public function reply(Request $request, int $review, SellerFeedbackService $feedback)
    {
        $feedback->reply($request, $review);
        return back()->with('status', 'Reply saved.');
    }
}
