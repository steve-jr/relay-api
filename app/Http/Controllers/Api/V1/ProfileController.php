<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('creatorProfile');
        return response()->json(['data' => array_merge($user->toApiArray(), [
            'creator_profile' => $user->creatorProfile?->publicPayload(),
        ])]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['sometimes','string','max:120'],
            'country' => ['sometimes', Rule::in(['GH','NG','KE'])],
            'currency' => ['sometimes', Rule::in(['GHS','NGN','KES'])],
            'display_name' => ['sometimes','string','max:120'],
            'slug' => ['sometimes','string','max:140','alpha_dash', Rule::unique('creator_profiles','slug')->ignore($user->id,'user_id')],
            'bio' => ['sometimes','nullable','string','max:3000'],
            'city' => ['sometimes','nullable','string','max:120'],
            'primary_niche' => ['sometimes','nullable','string','max:120'],
            'secondary_niches' => ['sometimes','array'],
            'secondary_niches.*' => ['string','max:120'],
            'industries' => ['sometimes','array'],
            'industries.*' => ['string','max:120'],
            'socials' => ['sometimes','array'],
            'socials.*.platform' => ['required_with:socials','string','max:40'],
            'socials.*.handle' => ['required_with:socials','string','max:120'],
            'socials.*.followers' => ['nullable','integer','min:0'],
            'average_engagement_rate' => ['sometimes','numeric','min:0','max:100'],
            'starting_rate' => ['sometimes','numeric','min:0'],
            'profile_image_url' => ['sometimes','nullable','url','max:2048'],
        ]);
        $user->fill(array_intersect_key($data, array_flip(['name','country','currency'])))->save();
        if ($user->role === 'creator') {
            $profileData = array_diff_key($data, array_flip(['name','country','currency']));
            $profileData['country'] ??= $user->country;
            $profileData['currency'] ??= $user->currency;
            $profileData['display_name'] ??= $user->name;
            $profileData['slug'] ??= str($user->name)->slug('-').'-'.$user->id;
            $user->creatorProfile()->updateOrCreate(['user_id' => $user->id], $profileData);
        }

        return $this->show($request);
    }
}
