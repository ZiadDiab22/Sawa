<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
  public function saveToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required'
    ]);

    $request->user()->update([
        'fcm_token' => $request->fcm_token
    ]);

    return response()->json(['message' => 'Token saved']);
}
}
