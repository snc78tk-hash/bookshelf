<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        $user = Auth::user();

        $review->likedByUsers()->toggle($user->id);

        return back();
    }
}
