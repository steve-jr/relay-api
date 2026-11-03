<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    public function store(Request $request, Campaign $campaign)
    {
        abort_unless($campaign->brand_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $data = $request->validate([
            'creator_id'=>['required','integer','exists:users,id'],
            'proposed_rate'=>['required','numeric','min:0'],
            'currency'=>['required',Rule::in(['GHS','NGN','KES'])],
            'deliverables'=>['required','array','min:1'],
            'deliverables.*'=>['string','max:500'],
            'deadline'=>['nullable','date','after_or_equal:today'],
            'message'=>['nullable','string','max:3000'],
        ]);
        abort_unless(\App\Models\User::whereKey($data['creator_id'])->where('role','creator')->exists(), 422, 'The selected user is not a creator.');
        $invitation = Invitation::create([
            ...$data, 'campaign_id'=>$campaign->id, 'brand_id'=>$campaign->brand_id,
            'status'=>'open', 'turn'=>'creator', 'max_rounds'=>5,
        ]);
        return response()->json(['data'=>$invitation->load('offers')], 201);
    }

    public function creatorIndex(Request $request)
    {
        $items = Invitation::with(['campaign','brand','offers'])
            ->where('creator_id',$request->user()->id)->latest()->get()
            ->map(fn($i) => $this->payload($i));
        return response()->json(['data'=>$items]);
    }

    public function creatorRespond(Request $request, Invitation $invitation)
    {
        abort_unless($invitation->creator_id === $request->user()->id, 403);
        $data = $request->validate(['response'=>['required',Rule::in(['accepted','declined'])]]);
        abort_unless($invitation->status === 'open' && $invitation->turn === 'creator', 409, 'It is not the creator’s turn to respond.');
        $invitation->update(['status'=>$data['response'],'turn'=>null]);
        return response()->json(['data'=>$this->payload($invitation->fresh()->load('offers'))]);
    }

    private function payload(Invitation $i): array
    {
        return [
            'id'=>$i->id,'campaign_id'=>$i->campaign_id,'campaign'=>$i->campaign,
            'brand'=>['id'=>$i->brand?->id,'name'=>$i->brand?->name],
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
