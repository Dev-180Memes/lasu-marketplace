<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     */
    public function index(): View
    {
        $user = auth()->user();

        $conversations = Conversation::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->with(['listing.images', 'buyer', 'seller', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('last_message_at')
            ->paginate(20);

        return view('conversations.index', compact('conversations'));
    }

    /**
     * Open or create a conversation for a listing.
     */
    public function openOrCreate(Listing $listing): RedirectResponse
    {
        $user = auth()->user();

        // Seller cannot message themselves
        if ($listing->user_id === $user->id) {
            return back()->with('error', 'You cannot message yourself about your own listing.');
        }

        $conversation = Conversation::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_id'   => $user->id,
                'seller_id'  => $listing->user_id,
            ]
        );

        return redirect()->route('conversations.show', $conversation->id);
    }

    /**
     * Show a conversation thread.
     */
    public function show(Conversation $conversation): View
    {
        $user = auth()->user();

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );

        // Mark all unread messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load([
            'messages.sender',
            'listing.images',
            'buyer',
            'seller',
            'meetupProposals.campusZone',
            'meetupProposals.proposedBy',
        ]);

        $other = $conversation->getOtherParticipant($user);

        return view('conversations.show', compact('conversation', 'other'));
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );

        $request->validate([
            'body'       => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ]);

        $data = [
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $request->body ?? '',
            'type'            => 'text',
        ];

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('messages/attachments', 'public');
            $data['type']            = 'media';
        }

        $message = Message::create($data);

        $conversation->update(['last_message_at' => now()]);

        // Notify the other party
        $other = $conversation->getOtherParticipant($user);
        $other->notify(new NewMessageNotification($conversation, $message));

        return back();
    }
}
