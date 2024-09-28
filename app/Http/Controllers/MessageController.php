<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = Message::where(function ($query) use ($request) {
            $query->where('sender_id', $request->sender_id)
                ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                ->where('receiver_id', $request->sender_id);
        })->get();

        // dd($chats);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $sender_id = $request->sender_id ?? null;
        $receiver_id = $request->receiver_id ?? null;
        $message = $request->message ?? null;
        $attachment = $request->attachment ?? null;

        $attachments = [];
        if ($request->hasFile('attachment')) {
            foreach ($attachment as $key => $image) {
                $file_name = time() . trim($image->getClientOriginalName());
                $path =  '/chat_attachments';
                $image->move(public_path() . '/' . $path,  $file_name);
                $attachments[] = $file_name;
            }
        }

        $chats = Message::create([
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message' => $message,
            'attachment' => $attachments
        ]);
        return response()->json($chats);
    }
}
