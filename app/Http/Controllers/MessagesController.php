<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function getLatest(Request $request): string
    {
        return Message::whereType($request->type)->first()->message;
    }
}
