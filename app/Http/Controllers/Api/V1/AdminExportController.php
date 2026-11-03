<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CreatorProfile;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    public function creators(): StreamedResponse
    {
        return $this->csv('relay-creators.csv',
            ['id','slug','display_name','country','currency','city','primary_niche','starting_rate'],
            CreatorProfile::query()->cursor()->map(fn($p)=>[
                $p->id,$p->slug,$p->display_name,$p->country,$p->currency,$p->city,$p->primary_niche,$p->starting_rate
            ]));
    }

    public function leads(): StreamedResponse
    {
        return $this->csv('relay-leads.csv',
            ['id','name','email','role','country','created_at'],
            User::query()->whereIn('role',['brand','creator'])->cursor()->map(fn($u)=>[
                $u->id,$u->name,$u->email,$u->role,$u->country,$u->created_at
            ]));
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output','w');
            fputcsv($out,$headers);
            foreach ($rows as $row) fputcsv($out,(array)$row);
            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
