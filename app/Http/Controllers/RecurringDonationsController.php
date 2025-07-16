<?php

namespace App\Http\Controllers;

use App\Models\RecurringDonation;
use App\Models\wallet;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
class RecurringDonationsController extends Controller
{
    public function index(): JsonResponse
    {
        $user_id = auth()->user()->id;
        $rec=RecurringDonation::where('user_id',$user_id)->get();
    return response()->json([
            'recurring_donations'=>$rec
        ]);
    }
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount'=>'required|numeric',
            'period'=>'required|in:daily,weekly,monthly,yearly',
            'start_date'=>'date|required',
            'type'=>'required|in:food,education,health,most_need',
            'wallet_pin'=>'required',
            'reminder_notification'=>'required|in:one_day_before,when_the_time_comes'
        ]);
        $user_id=auth()->user()->id;
//        $wallet=wallet::where('user_id',$user_id)->first();
        $req_date = Carbon::parse($request->start_date);
        if ($req_date->isToday()||$req_date->isPast()){
            return response()->json([
                'message'=>'the starting date must be in the future'
            ]);
        }
        $start=Carbon::parse($request->start_date);
        $period = $request->period;
        $nextdate =  match ($period){
            'daily'=> $start->copy()->addDay(),
            'weekly'=> $start->copy()->addWeek(),
            'monthly'=> $start->copy()->addMonth(),
            'yearly'=> $start->copy()->addYear(),
            default => throw new InvalidArgumentException("Invalid period: $period")
        };
        RecurringDonation::create([
            'user_id'=>$user_id,
            'type'=>$request->type,
            'amount'=>$request->amount,
            'period'=>$request->period,
            'start_date'=>$request->start_date,
            'next_run'=> $nextdate,
            'is_active'=>true
        ])->save();
        return response()->json([
            'from' =>auth()->user()->full_name,
            'to' => "{$request->type} campaigns",
            'amount'=>$request->amount,
            'payment_method' => 'Donation wallet',
            'time' => Carbon::now(),
            'type_of_repetition' => $request->period,
        ]);
    }
    public function destroy($id): JsonResponse
    {
        $rec=RecurringDonation::where('id',$id)->first();
        if ($rec->user_id != auth()->user()->id){
            return response()->json(['message'=>'you are not authorized to view this record']);
        }
        $rec->update(['is_active'=>false]);
        $total = $rec->run_count * $rec->amount;
        return response()->json([
            'message'=>'Recurring Donation has been stopped',
            'total'=>$total
        ]);

    }
}
