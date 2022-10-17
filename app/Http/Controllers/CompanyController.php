<?php

namespace App\Http\Controllers;

use App\Models\AssignCamp;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    //
    public function index()
    {
        $paginate = env('PAGINATE');
        $company = Company::paginate($paginate);
        return response()->json($company, 200);
    }

}
