<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HospitalController extends Controller
{
    /**
     * Display a listing of hospitals.
     */
    public function index()
    {
        try {
            $hospitals = Hospital::with('license')->latest()->paginate(10);

            return $this->success([
                'hospitals' => $hospitals
            ], 'Hospital List', 'Hospitals retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Hospital index error: ' . $e->getMessage());

            return $this->failed(
                'Failed to retrieve hospitals.',
                'Hospital List',
                'An error occurred while retrieving hospitals.',
                500
            );
        }
    }

    /**
     * Store a newly created hospital.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',

            'image' => 'nullable',

            'license_number' => 'nullable|string|max:255',
            'license_issue_date' => 'nullable|date',
            'license_expiry_date' => 'nullable|date|after_or_equal:license_issue_date',
            'license_issuing_authority' => 'nullable|string|max:255',
            'license_document' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            //medchain_id generation from license number and current timestamp
            $validated['medchain_id'] = 'MEDCHAIN-' . strtoupper(uniqid()) . '-' . time();

            // Upload image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage(
                    $request->file('image'),
                    'images',
                    'public'
                );
            }

            // Upload license document
            $licenseDocumentPath = null;
            if ($request->hasFile('license_document')) {
                $licenseDocumentPath = $this->uploadImage(
                    $request->file('license_document'),
                    'documents',
                    'public'
                );
            }

            // Create hospital
            $hospital = Hospital::create([
                'medchain_id' => $validated['medchain_id'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'image' => $imagePath,
            ]);

            // Create license only if required fields exist
            if (
                !empty($validated['license_number']) &&
                !empty($validated['license_issue_date']) &&
                !empty($validated['license_expiry_date']) &&
                !empty($validated['license_issuing_authority'])
            ) {
                $hospital->license()->create([
                    'license_number' => $validated['license_number'],
                    'issue_date' => $validated['license_issue_date'],
                    'expiry_date' => $validated['license_expiry_date'],
                    'issuing_authority' => $validated['license_issuing_authority'],
                    'license_document' => $licenseDocumentPath,
                ]);
            }

            DB::commit();

            return $this->success([
                'hospital' => $hospital->load('license')
            ], 'Store Hospital', 'Hospital created successfully');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Hospital store error: ' . $e->getMessage());

            return $this->failed(
                'Failed to create hospital.',
                'Store Hospital',
                'An error occurred while creating the hospital.',
                500
            );
        }
    }

    /**
     * Display the specified hospital.
     */
    public function show(Request $request)
    {
        $hospital = Hospital::with('license')->where('id', $request->id)->first();

        if (!$hospital) {
            return $this->failed(
                null,
                'Hospital Not Found',
                'No hospital found with the provided MedChain ID.',
                404
            );
        }

        return $this->success([
            'hospital' => $hospital
        ], 'Hospital Details', 'Hospital details retrieved successfully');
    }

    /**
     * Update the specified hospital.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {

            $hospital = Hospital::where('id', $request->id)->first();

            // Check if hospital exists
            if (!$hospital) {
                return $this->failed(
                    null,
                    'Hospital Not Found',
                    'No hospital found with the provided ID.',
                    404
                );
            }

            $imagePath = $hospital->image;

            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage(
                    $request->file('image'),
                    'images',
                    'public'
                );
            }

            $hospital->update([
                'name' => $validated['name'] ?? $hospital->name,
                'address' => $validated['address'] ?? $hospital->address,
                'phone' => $validated['phone'] ?? $hospital->phone,
                'image' => $imagePath,
            ]);

            DB::commit();


            return $this->success([
            ], 'Update Hospital', 'Hospital updated successfully');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Hospital update error: ' . $e->getMessage());

            return $this->failed(
                'Failed to update hospital.',
                'Update Hospital',
                'An error occurred while updating the hospital.',
                500
            );
        }
    }

    /**
     * Remove the specified hospital.
     */
    public function destroy(Request $request)
    {
        try {
            $hospital = Hospital::where('id', $request->id)->first();

            // Check if hospital exists
            if (!$hospital) {
                return $this->failed(
                    null,
                    'Hospital Not Found',
                    'No hospital found with the provided ID.',
                    404
                );
            }

            $hospital->delete();

            return $this->success(
                null,
                'Delete Hospital',
                'Hospital deleted successfully'
            );

        } catch (\Exception $e) {

            Log::error('Hospital delete error: ' . $e->getMessage());

            return $this->failed(
                'Failed to delete hospital.',
                'Delete Hospital',
                'An error occurred while deleting the hospital.',
                500
            );
        }
    }
}