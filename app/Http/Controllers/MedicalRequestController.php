<?php

namespace App\Http\Controllers;

use App\Models\MedicalApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\AssitanceRequest;
class MedicalRequestController extends Controller
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
            'diagnosis' => 'required',
            'treatmentPlan' => 'required',
            'needsSurgery' => 'required|boolean',
            'hospitalName' => 'string',
            'appointmentDate' => 'date',
            'hasInsurance' => 'boolean|required',
            'estimatedCost' => 'numeric',
        ]);
        $req = AssitanceRequest::create([
            'status' => 'pending',
            'type' => 'medical',
            'description' => null,
            'admin_response' => null,
            'user_id' => auth()->user()->id
        ]);
        $temp = $request->all();
        $temp['request_id'] = $req->id;
        $req->save();
        MedicalApplication::create($temp);
        return response()->json([
            'message' => 'request has been placed, wait for approval'
        ]);
    }
}
