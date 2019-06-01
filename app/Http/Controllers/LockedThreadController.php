<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Thread;

class LockedThreadController extends Controller
{
    public function store(Thread $thread)
    {
        $thread->lock();
    }
}
