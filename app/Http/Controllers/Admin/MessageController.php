<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;

/** Contact form messages. Opening one marks it read. */
class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', ['messages' => Message::latest()->paginate(25)]);
    }

    public function show(Message $message)
    {
        $message->read_at ?? $message->update(['read_at' => now()]);

        return view('admin.messages.show', ['message' => $message]);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('status', 'Message deleted.');
    }
}
