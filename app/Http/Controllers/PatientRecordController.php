<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientRecordController extends Controller
{
    //index
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $user = $request->user();
        $records = $user->records()->get();
        return response()->json($records);
    }

    //show
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $record = $user->records()->find($id);
        if (!$record) {
            return response()->json(['message' => 'Record not found'], 404);
        }
        return response()->json($record);
    }
}
