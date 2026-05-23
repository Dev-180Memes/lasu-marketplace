<?php

namespace App\Http\Controllers;

use App\Models\CampusZone;
use App\Models\Conversation;
use App\Models\MeetupProposal;
use App\Notifications\MeetupProposalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MeetupProposalController extends Controller
{
    /**
     * Submit a new meetup proposal within a conversation.
     */
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );

        $request->validate([
            'campus_zone_id' => ['required', 'exists:campus_zones,id'],
            'proposed_at'    => ['required', 'date', 'after:now'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $proposal = MeetupProposal::create([
            'conversation_id' => $conversation->id,
            'proposed_by'     => $user->id,
            'campus_zone_id'  => $request->campus_zone_id,
            'proposed_at'     => $request->proposed_at,
            'notes'           => $request->notes,
            'status'          => 'pending',
        ]);

        // Notify the other party
        $other = $conversation->getOtherParticipant($user);
        $other->notify(new MeetupProposalNotification($proposal, 'new'));

        return back()->with('success', 'Meetup proposal sent!');
    }

    /**
     * Accept a meetup proposal.
     */
    public function accept(MeetupProposal $proposal): RedirectResponse
    {
        $user         = auth()->user();
        $conversation = $proposal->conversation;

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );
        abort_unless($proposal->proposed_by !== $user->id, 422, 'You cannot accept your own proposal.');
        abort_unless($proposal->isPending(), 422, 'This proposal is no longer pending.');

        $proposal->update([
            'status'       => 'accepted',
            'responded_at' => now(),
            'accepted_at'  => now(),
        ]);

        // Link to order if conversation has one
        $conversation->orders()
            ->where('order_status', 'confirmed')
            ->update(['meetup_proposal_id' => $proposal->id]);

        $other = $conversation->getOtherParticipant($user);
        $other->notify(new MeetupProposalNotification($proposal, 'accepted'));

        return back()->with('success', 'Meetup accepted! See you there.');
    }

    /**
     * Decline a proposal.
     */
    public function decline(MeetupProposal $proposal): RedirectResponse
    {
        $user         = auth()->user();
        $conversation = $proposal->conversation;

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );
        abort_unless($proposal->isPending(), 422);

        $proposal->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);

        $other = $conversation->getOtherParticipant($user);
        $other->notify(new MeetupProposalNotification($proposal, 'declined'));

        return back()->with('info', 'Meetup proposal declined.');
    }

    /**
     * Counter-propose a different time or place.
     */
    public function counter(Request $request, MeetupProposal $proposal): RedirectResponse
    {
        $user         = auth()->user();
        $conversation = $proposal->conversation;

        abort_unless(
            $conversation->buyer_id === $user->id || $conversation->seller_id === $user->id,
            403
        );
        abort_unless($proposal->isPending(), 422);

        $request->validate([
            'campus_zone_id' => ['required', 'exists:campus_zones,id'],
            'proposed_at'    => ['required', 'date', 'after:now'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        // Mark original as counter-proposed
        $proposal->update(['status' => 'counter_proposed', 'responded_at' => now()]);

        // Create the counter-proposal
        $counter = MeetupProposal::create([
            'conversation_id' => $conversation->id,
            'proposed_by'     => $user->id,
            'campus_zone_id'  => $request->campus_zone_id,
            'proposed_at'     => $request->proposed_at,
            'notes'           => $request->notes,
            'parent_id'       => $proposal->id,
            'status'          => 'pending',
        ]);

        $other = $conversation->getOtherParticipant($user);
        $other->notify(new MeetupProposalNotification($counter, 'counter'));

        return back()->with('success', 'Counter-proposal sent!');
    }
}
