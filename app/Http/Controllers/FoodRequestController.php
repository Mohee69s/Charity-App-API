<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\FoodApplication;
use App\Models\AssitanceRequest;

class FoodRequestController extends Controller
{
    public function index()
    {
        $tableName = 'medical_forms';
        $columns = DB::connection()->getSchemaBuilder()->getColumnListing($tableName); // Get column names

        $columnTypes = [];
        foreach ($columns as $column) {
            $columnTypes[$column] = DB::connection()->getSchemaBuilder()->getColumnType($tableName, $column);
        }
        return response()->json([
            'columns' => $columnTypes
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'familySize' => 'required|numeric',
            'dietaryRestrictions' => 'string|nullable',
            'needsBabyFood' => 'required|boolean',
            'needsCookingTools' => 'boolean|required',
            'preferredFoodItems' => 'string',
            'lastFoodPackageDate' => 'string'
        ]);
        $req = AssitanceRequest::create([
            'status' => 'pending',
            'type' => 'food',
            'description' => null,
            'admin_response' => null,
            'user_id' => auth()->user()->id
        ]);
        $temp = $request->all();
        $temp['request_id']=$req->id;
        $req->save();
        FoodApplication::create($temp);
        return response()->json([
            'message'=>'request has been placed, wait for approval'
        ]);
    }
}
