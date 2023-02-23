<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\QrCode;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function store(User $user)
    {
        QrCode::where('email', $user->email)->delete();

        QrCode::generateFor($user);
    }
}
