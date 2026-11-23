<?php

namespace App\Http\Controllers;

use Anthropic\Core\Exceptions\APIException;
use App\Support\OrderAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The website order chat. The conversation lives in the visitor's session. */
class ChatController extends Controller
{
    /** Messages kept per chat (customer + assistant + tool turns) before asking for a fresh start. */
    private const MAX_HISTORY = 80;

    public function send(Request $request): JsonResponse
    {
        abort_unless(OrderAssistant::enabled(), 404);

        $text = trim($request->validate(['message' => ['required', 'string', 'max:1000']])['message']);
        $history = $request->session()->get('order_chat.history', []);

        if (count($history) >= self::MAX_HISTORY) {
            return response()->json(['reply' => 'This chat has got quite long. Tap “New chat” to start fresh, or use the order form.', 'order' => null]);
        }

        try {
            $result = app(OrderAssistant::class)->reply($history, $text, $request->session()->get('order_chat.order_id'));
        } catch (APIException $e) {
            report($e);

            return response()->json(['reply' => 'Sorry, the chat is unavailable right now. Please use the order form or message us on WhatsApp.', 'order' => null], 503);
        }

        $request->session()->put('order_chat.history', $result['history']);
        if ($result['order']) {
            $request->session()->put('order_chat.order_id', $result['order']->id);
        }

        return response()->json([
            'reply' => $result['reply'],
            'order' => $result['order'] ? ['reference' => $result['order']->reference()] : null,
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(['order_chat.history', 'order_chat.order_id']);

        return response()->json(['ok' => true]);
    }
}
