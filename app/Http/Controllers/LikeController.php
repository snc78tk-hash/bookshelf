<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();


        $review->likedByUsers()->toggle($user->id);

        return back();
    }
}
