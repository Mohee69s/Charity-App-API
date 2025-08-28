<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\InKind;
use App\Models\InKindDonation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class InKindDonationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $id = auth()->user()->id;
        $don = InKindDonation::where('user_id', $id)->with('InKind')->with('campaign')->get();
        if (!$don) {
            return response()->json([
                'message' => 'you haven\'t made any in-kind donations yet'
            ]);
        }
        return response()->json([
            'inkinddonations' => $don
        ]);
    }
    public function store(Request $request, $id): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|numeric',
        ]);

        $camp = Campaign::where('id', $id)->first();
        if (!$camp || !$camp->need_in_kind_donations) {
            return response()->json([
                'message' => 'This campaign doesn\'t need in-kind donations.'
            ]);
        }

        $results = [];

        foreach ($request->items as $item) {
            $inkind = InKind::where('campaign_id', $id)
                ->where('name', $item['name'])
                ->first();

            if (!$inkind) {
                $results[] = [
                    'Item' => $item['name'],
                    'Quantity' => $item['quantity'],
                    'status' => 'not found in campaign'
                ];
                continue;
            }

            if ($inkind->goal <= $inkind->cost) {
                $results[] = [
                    'Item' => $item['name'],
                    'Quantity' => $item['quantity'],
                    'status' => 'goal reached'
                ];
                continue;
            }

            InKindDonation::create([
                'status' => 'pending',
                'approved' => false,
                'name' => $inkind->name,
                'in_kind' => $inkind->id,
                'campaign_id' => $camp->id,
                'user_id' => auth()->user()->id,
                'quantity' => $item['quantity'],
                'description' => 'null',
            ]);

            $results[] = [
                'Item' => $inkind->name,
                'Quantity' => $item['quantity'],
                'status' => 'submitted'
            ];
        }

        return response()->json([
            'From' => auth()->user()->full_name,
            'To' => $camp->name,
            'Time' => Carbon::now(),
            'Items' => $results
        ]);
    }

}