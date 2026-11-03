<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $items = Campaign::where('brand_id', $request->user()->id)->latest()->paginate(15);
        return response()->json(['data' => $items->items(), 'meta' => [
            'current_page'=>$items->currentPage(),'last_page'=>$items->lastPage(),
            'per_page'=>$items->perPage(),'total'=>$items->total()
        ]]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCampaign($request);
        $campaign = $request->user()->campaigns()->create($data);
        return response()->json(['data'=>$campaign], 201);
    }

    public function show(Request $request, Campaign $campaign)
    {
        abort_unless($campaign->brand_id === $request->user()->id || $request->user()->role === 'admin', 403);
        return response()->json(['data'=>$campaign->load('invitations')]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        abort_unless($campaign->brand_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $campaign->update($this->validateCampaign($request, true));
        return response()->json(['data'=>$campaign->fresh()]);
    }

    private function validateCampaign(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        return $request->validate([
            'name'=>[$required,'string','max:180'],
            'description'=>['sometimes','nullable','string','max:5000'],
            'objective'=>['sometimes','nullable','string','max:500'],
            'budget'=>[$required,'numeric','min:0'],
            'currency'=>[$required,Rule::in(['GHS','NGN','KES'])],
            'country'=>[$required,Rule::in(['GH','NG','KE'])],
            'status'=>['sometimes',Rule::in(['draft','active','paused','completed','cancelled'])],
            'start_date'=>['sometimes','nullable','date'],
            'end_date'=>['sometimes','nullable','date','after_or_equal:start_date'],
            'deliverables'=>['sometimes','array'],
            'deliverables.*'=>['string','max:500'],
        ]);
    }
}
