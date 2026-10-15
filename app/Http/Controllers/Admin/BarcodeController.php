<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CanteenUser;
use Illuminate\View\View;

class BarcodeController extends Controller
{
    public function show(CanteenUser $canteenUser): View
    {
        return view('admin.barcode', ['canteenUser' => $canteenUser]);
    }
}
