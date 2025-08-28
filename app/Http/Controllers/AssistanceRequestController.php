<?php

namespace App\Http\Controllers;

use App\Models\AssitanceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Support\SchemaIntrospector;

class AssistanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $tables = [
            'medicalRequests' => 'medical_forms',
            'educationalRequests' => 'educational_forms',
            'foodRequests' => 'food_forms',
        ];

        $forms = [];
        foreach ($tables as $label => $table) {
            $forms[$label] = SchemaIntrospector::tableSchema($table);
        }

        return response()->json(['Forms' => $forms]);
    }
    public function log()
    {
        $userId = auth()->id();

        $requests = AssitanceRequest::with(['educationalForm', 'medicalForm', 'foodForm'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')  
            ->get();

        $grouped = ['educational' => [], 'medical' => [], 'food' => []];

        foreach ($requests as $r) {
            $date = $r->created_at;
            $date = Carbon::parse($date);
            $date = $date->format('Y-m-d');
            $base = [
                'id' => $r->id,
                'status' => $r->status,
                'description' => $r->description,
                'admin_response' => $r->admin_response,
                'created_at' => $date,
                'updated_at' => $r->updated_at,
            ];

            if ($r->type === 'educational') {
                $grouped['educational'][] = $base + ['form' => $r->educationalForm];
            } elseif ($r->type === 'medical') {
                $grouped['medical'][] = $base + ['form' => $r->medicalForm];
            } else { // food
                $grouped['food'][] = $base + ['form' => $r->foodForm];
            }
        }

        return response()->json($grouped);
    }
}
