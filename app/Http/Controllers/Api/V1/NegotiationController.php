<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NegotiationController extends Controller
{
    public function brandIndex(Request $request)
    {
        $items = Invitation::with(['campaign','creator.creatorProfile','offers'])
            ->where('brand_id',$request->user()->id)->latest()->get()
            ->map(fn($i) => $this->payload($i));
        return response()->json(['data'=>$items]);
    }

    public function brandOffer(Request $request, Invitation $invitation)
    {
        abort_unless($invitation->brand_id === $request->user()->id || $request->user()->role === 'admin', 403);
        return $this->makeOffer($request, $invitation, 'brand');
    }

    public function creatorOffer(Request $request, Invitation $invitation)
    {
        abort_unless($invitation->creator_id === $request->user()->id, 403);
        return $this->makeOffer($request, $invitation, 'creator');
    }

    private function makeOffer(Request $request, Invitation $invitation, string $actor)
    {
        $data = $request->validate([
            'fee'=>['required','numeric','min:0'],
            'currency'=>['required',Rule::in(['GHS','NGN','KES'])],
            'deliverables'=>['required','array','min:1'],
            'deliverables.*'=>['string','max:500'],
            'deadline'=>['nullable','date','after_or_equal:today'],
            'note'=>['nullable','string','max:3000'],
        ]);
        $updated = DB::transaction(function () use ($invitation, $actor, $data) {
            $locked = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'open', 409, 'This negotiation is no longer open.');
            abort_unless($locked->turn === $actor, 409, 'It is not your turn to make an offer.');
            $nextRound = ((int) $locked->offers()->max('round')) + 1;
            abort_if($nextRound > $locked->max_rounds, 409, 'The maximum number of offer rounds has been reached.');
            Offer::create(['invitation_id'=>$locked->id,'round'=>$nextRound,'by'=>$actor,...$data]);
            $locked->update(['turn'=>$actor === 'brand' ? 'creator' : 'brand']);
            return $locked->fresh()->load('offers','campaign','brand','creator.creatorProfile');
        });
        return response()->json(['data'=>$this->payload($updated)], 201);
    }

    public function brandRespond(Request $request, Invitation $invitation)
    {
        abort_unless($invitation->brand_id === $request->user()->id || $request->user()->role === 'admin', 403);
        return $this->respond($request, $invitation, 'brand');
    }

    private function respond(Request $request, Invitation $invitation, string $actor)
    {
        $data = $request->validate(['response'=>['required',Rule::in(['accepted','declined'])]]);
        abort_unless($invitation->status === 'open' && $invitation->turn === $actor, 409, 'It is not your turn to respond.');
        $invitation->update(['status'=>$data['response'],'turn'=>null]);
        return response()->json(['data'=>$this->payload($invitation->fresh()->load('offers','campaign','brand','creator.creatorProfile'))]);
    }

    private function payload(Invitation $i): array
    {
        abort_unless($i->canBeSeenBy(request()->user()), 403);
        return [
            'id'=>$i->id,'campaign_id'=>$i->campaign_id,'campaign'=>$i->campaign,
            'brand'=>['id'=>$i->brand?->id,'name'=>$i->brand?->name],
            'creator'=> $i->creator?->creatorProfile?->publicPayload(),
            'creator_id'=>$i->creator_id,'status'=>$i->status,'turn'=>$i->turn,
            'max_rounds'=>$i->max_rounds,'proposed_rate'=>$i->proposed_rate,'currency'=>$i->currency,
            'deliverables'=>$i->deliverables,'deadline'=>$i->deadline,'message'=>$i->message,
            'offers'=>$i->offers->map(fn($o)=>[
                'id'=>$o->id,'round'=>$o->round,'by'=>$o->by,'fee'=>$o->fee,'currency'=>$o->currency,
                'deliverables'=>$o->deliverables,'deadline'=>$o->deadline,'note'=>$o->note,
            ]),
        ];
    }
}
