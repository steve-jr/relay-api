<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use Illuminate\Http\Request;

class CreatorController extends Controller
{
    public function index(Request $request)
    {
        $query = CreatorProfile::query()->where('is_published', true)
            ->where('country', $request->user()->country);

        foreach (['city','primary_niche'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->string($field)->toString());
        }
        if ($request->filled('country')) $query->where('country', $request->string('country')->toString());
        if ($request->filled('industry')) $query->whereJsonContains('industries', $request->string('industry')->toString());
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(fn($q) => $q->where('display_name','like',$term)->orWhere('bio','like',$term)->orWhere('city','like',$term));
        }
        if ($request->filled('platform')) $query->whereJsonContains('socials', ['platform' => $request->string('platform')->toString()]);
        if ($request->filled('min_followers')) {
            $minimum = (int) $request->input('min_followers');
            $query->whereHas('user', fn($q) => $q->whereRaw('1 = 1'));
            // Follower filtering is applied to social payload in application code below.
        }
        if ($request->input('sort') === 'rate_low') $query->orderBy('starting_rate');
        else $query->orderByDesc('average_engagement_rate');

        $profiles = $query->paginate(min(max((int)$request->input('per_page', 15), 1), 100));
        $data = collect($profiles->items())->filter(function ($profile) use ($request) {
            if (!$request->filled('min_followers')) return true;
            $followers = collect($profile->socials ?? [])->max(fn($social) => (int)($social['followers'] ?? 0)) ?? 0;
            return $followers >= (int)$request->input('min_followers');
        })->map(fn($profile) => $profile->publicPayload())->values();

        return response()->json(['data' => $data, 'meta' => [
            'current_page' => $profiles->currentPage(), 'last_page' => $profiles->lastPage(),
            'per_page' => $profiles->perPage(), 'total' => $profiles->total(),
        ]]);
    }

    public function show(string $slug)
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();
        return response()->json(['data' => $profile->publicPayload()]);
    }
}
