<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Pack;
use App\Models\Trade;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'cardCount' => Card::count(),
            'packCount' => Pack::count(),
            'userCount' => User::count(),
            'bannedCount' => User::whereNotNull('banned_at')->count(),
            'tradeCount' => Trade::count(),
            'pendingTrades' => Trade::where('status', Trade::STATUS_PENDING)->count(),
            'aiDriver' => config('cards.driver'),
        ]);
    }
}
