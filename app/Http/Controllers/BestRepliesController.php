<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reply;

class BestRepliesController extends Controller
{
    public function store(Reply $reply)
    {
        $reply->thread->update(['best_reply_id' => $reply->id]);
    }
}
