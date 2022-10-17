<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Entities\User;

class GeneralController extends Controller
{
    //
    public function DashboardCounter()
    {
        $total_user = User::count();
        $total_company = Company::count();
        $total_camps = Camp::count();
        $total_square = Camp::count();
        $pending_user = User::where('status', 'pending')->count();
        $ready_camps = Camp::where('status', 'ready')->count();
        $user = User::with('Type')->find(Auth::user()->id);
        $data = ['total_user' => $total_user, 'total_company' => $total_company, 'total_camps' => $total_camps, 'total_square' => $total_square, 'pending_user' => $pending_user, 'ready_camps' => $ready_camps];
        return response()->json(['user' => $user, 'data' => $data], 200);
    }
}
