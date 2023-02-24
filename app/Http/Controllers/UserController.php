<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereHas('chatSessions', function ($query) {
                return $query->where('is_group', false);
            })
            ->where('id', '!=', Auth::id())
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return response()->json(['users' => $users]);
    }
}
