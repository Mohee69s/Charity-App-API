<?php

namespace App\Http\Controllers;

use App\Models\AssitanceRequest;
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
            ->orderByDesc('created_at')   // same as ->latest()
            ->get();

        $grouped = ['educational' => [], 'medical' => [], 'food' => []];

        foreach ($requests as $r) {
            $base = [
                'id' => $r->id,
                'status' => $r->status,
                'description' => $r->description,
                'admin_response' => $r->admin_response,
                'created_at' => $r->created_at,
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
