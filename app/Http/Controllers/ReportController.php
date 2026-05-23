<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Report;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reportable_type' => ['required', 'in:listing,user,store'],
            'reportable_id'   => ['required', 'integer'],
            'reason'          => ['required', 'in:fraud,fake_listing,harassment,spam,inappropriate_content,other'],
            'description'     => ['nullable', 'string', 'max:1000'],
        ]);

        // Resolve the morph type
        $morphMap = [
            'listing' => Listing::class,
            'user'    => User::class,
            'store'   => Store::class,
        ];

        $reportableType = $morphMap[$request->reportable_type];
        $reportable     = $reportableType::findOrFail($request->reportable_id);

        // Prevent duplicate pending reports
        $exists = Report::where('reporter_id', auth()->id())
            ->where('reportable_type', $reportableType)
            ->where('reportable_id', $reportable->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('info', 'You already have a pending report for this item.');
        }

        Report::create([
            'reporter_id'     => auth()->id(),
            'reportable_type' => $reportableType,
            'reportable_id'   => $reportable->id,
            'reason'          => $request->reason,
            'description'     => $request->description,
            'status'          => 'pending',
        ]);

        return back()->with('success', 'Report submitted. Our team will review it shortly.');
    }
}
