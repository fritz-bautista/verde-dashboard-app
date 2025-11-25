<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class QRLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $user = User::where('qr_code', $request->qr_code)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code'], 401);
        }

        // Generate a permanent API token (Sanctum)
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }

}

