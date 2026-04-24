<?php

namespace App\Traits;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Blockchain
{

    public function registerPatient($request)
    {
        $url = env('BLOCKCHAIN_API_URL') . '/medchain/patient';

        // Transform request into required structure
        $payload = [
            'id' => $request->medchain_id,
            'info' => [
                'data' => $request
            ]
        ];

        try {
            $response = Http::post($url, $payload);

            Log::channel('blockchain_log')->info(
                'Patient Registration Attempt',
                [
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]
            );

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Blockchain API request failed',
                    'error' => $response->body()
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::channel('blockchain_log')->error(
                'Blockchain Exception',
                ['error' => $e->getMessage()]
            );

            return [
                'success' => false,
                'message' => 'Exception occurred while calling blockchain API'
            ];
        }
    }

    //register hospital
    public function registerHospital($request)
    {
        $url = env('BLOCKCHAIN_API_URL') . '/medchain/hospital';

        // Transform request into required structure
        $payload = [
            'id' => $request->medchain_id,
            'info' => [
                'data' => $request
            ]
        ];

        try {
            $response = Http::post($url, $payload);

            Log::channel('blockchain_log')->info(
                'Hospital Registration Attempt',
                [
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]
            );

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Blockchain API request failed',
                    'error' => $response->body()
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::channel('blockchain_log')->error(
                'Blockchain Exception',
                ['error' => $e->getMessage()]
            );

            return [
                'success' => false,
                'message' => 'Exception occurred while calling blockchain API'
            ];
        }
    }

    //register record
    public function registerRecord($request, $patient_medchain_id)
    {
        $url = env('BLOCKCHAIN_API_URL') . '/medchain/record';

        $payload = [
            'patientId' => $patient_medchain_id,
            'recordId'  => $request->record_id,
            'data' => $request
        ];

        try {
            $response = Http::post($url, $payload);

            Log::channel('blockchain_log')->info(
                'Record Registration Attempt',
                [
                    'payload' => $payload,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]
            );

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Blockchain API request failed',
                    'error' => $response->body()
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::channel('blockchain_log')->error(
                'Blockchain Exception',
                ['error' => $e->getMessage()]
            );

            return [
                'success' => false,
                'message' => 'Exception occurred while calling blockchain API'
            ];
        }
    }
}