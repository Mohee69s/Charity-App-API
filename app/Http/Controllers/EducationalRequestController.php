<?php

namespace App\Http\Controllers;

use App\Models\AssitanceRequest;
use App\Models\EducationalApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EducationalRequestController extends Controller
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
            'schoolName' => 'required',
            'level' => 'required',
            'reasonForSupport' => 'required',
            'needsBooks' => 'required',
            'needsTuitionSupport' => 'required',
           'currentGPA' => 'required',
            'specialization' => 'required',
            'semesterStartDate' => 'required'
        ]);
        $req = AssitanceRequest::create([
            'status' => 'pending',
            'type' => 'educational',
            'description' => null,
            'admin_response' => null,
            'user_id' => auth()->user()->id
        ]);
        $temp = $request->all();
        $temp['request_id'] = $req->id;
        $req->save();
        EducationalApplication::create($temp);
        return response()->json([
            'message' => 'request has been placed, wait for approval'
        ]);
    }
}
