<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecordController extends Controller
{
    /**
     * Display a listing of records.
     */
    public function index()
    {
        $records = Record::latest()->paginate(10);

        if ($records->isEmpty()) {
            return $this->failed(
                'No records found',
                'Record Lists',
                'No records found in the database',
                404
            );
        }

        return $this->success(
            $records,
            'Record Lists',
            'Records retrieved successfully'
        );
    }

    /**
     * Store a newly created record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer',
            'hospital_id' => 'required|integer',
            'assessment_date' => 'nullable|date',
            'physician_name' => 'nullable|string|max:255',
            'complement_by' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'record_date' => 'nullable|date',
            'medical_record_files' => 'nullable',
        ]);

        // Upload image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage(
                $request->file('image'),
                'medical_record_files',
                'public'
            );
        }

        $record = Record::create([
            'record_id' => 'REC-' . Str::uuid(),
            'patient_id' => $validated['patient_id'],
            'hospital_id' => $validated['hospital_id'],
            'assessment_date' => $validated['assessment_date'] ?? null,
            'physician_name' => $validated['physician_name'] ?? null,
            'complement_by' => $validated['complement_by'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'treatment' => $validated['treatment'] ?? null,
            'record_date' => $validated['record_date'] ?? null,
            'medical_record_files' => $imagePath,
        ]);

        return $this->success(
            $record,
            'Create Record',
            'Record created successfully',
            201
        );
    }

    /**
     * Display the specified record.
     */
    public function show(Request $request)
    {
        $record = Record::find($request->id);

        return $this->success(
            $record,
            'Record Details',
            'Record retrieved successfully'
        );
    }

    /**
     * Update the specified record.
     */
    public function update(Request $request, Record $record)
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|integer',
            'hospital_id' => 'sometimes|integer',
            'assessment_date' => 'nullable|date',
            'physician_name' => 'nullable|string|max:255',
            'complement_by' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'record_date' => 'nullable|date',
            'medical_record_files' => 'nullable',
        ]);

        // Upload new image if exists
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage(
                $request->file('image'),
                'medical_record_files',
                'public'
            );

            $validated['medical_record_files'] = $imagePath;
        }

        $record->update($validated);

        return $this->success(
            $record,
            'Update Record',
            'Record updated successfully'
        );
    }

    /**
     * Remove the specified record (Soft Delete).
     */
    public function destroy(Record $record)
    {
        $record->delete();

        return $this->success(
            null,
            'Delete Record',
            'Record deleted successfully'
        );
    }
}