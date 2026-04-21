<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    //index
    public function index()
    {
        $users = User::with('patient')->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->patient ? $user->patient->first_name : null,
                'last_name' => $user->patient ? $user->patient->last_name : null,
                'nid' => $user->nid,
                'phone' => $user->phone,
                'role' => (int) $user->role,
                'status' => (int) $user->status,
            ];
        });

        return $this->success($data, 'User List', 'List of all registered users', 200);
    }

    //show user details
    public function show($id)
    {
        $user = User::with('patient')->find($id);

        if (!$user) {
            return $this->failed(null, 'User Not Found', 'No user found with the given ID', 404);
        }

        $data = [
            'id' => $user->id,
            'first_name' => $user->patient ? $user->patient->first_name : null,
            'last_name' => $user->patient ? $user->patient->last_name : null,
            'nid' => $user->nid,
            'phone' => $user->phone,
            'role' => (int) $user->role,
            'status' => (int) $user->status,
        ];

        return $this->success($data, 'User Details', 'Details of the specified user', 200);
    }
    /*
    |--------------------------------------------------------------------------
    | 1️⃣ Register (Basic Info Only)
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage(
                    $request->file('image'),
                    'images',
                    'public'
                );
            }

            // Check if NID already exists
            if (User::where('nid', $request->nid)->exists()) {
                return $this->failed(
                    null,
                    'Registration Failed',
                    'NID already exists',
                    400
                );
            }

            // Check if phone already exists
            if (User::where('phone', $request->phone)->exists()) {
                return $this->failed(
                    null,
                    'Registration Failed',
                    'Phone number already exists',
                    400
                );
            }

            // Create User
            $user = User::create([
                'image' => $imagePath,
                'nid' => $request->nid,
                'phone' => $request->phone,
            ]);

            // Create Patient
            $patient = Patient::create([
                'user_id' => $user->id,
                'medchain_id' => 'MC-P-' . Str::upper(Str::random(10)),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'height' => $request->height,
                'weight' => $request->weight,
                'birth_date' => $request->birth_date,
                'contact' => $request->phone,
                'place_of_birth' => $request->place_of_birth,
                'emergency_name' => $request->emergency_name,
                'emergency_relationship' => $request->emergency_relationship,
                'emergency_contact' => $request->emergency_contact,
                'image' => $imagePath,
            ]);

            // Temporary claim token
            $claimToken = Str::random(64);

            //store to UserCredential
            UserCredential::create([
                'user_id' => $user->id,
                'token' => $claimToken,
                'status' => 'active',
                'expires_at' => now()->addMinutes(10)
            ]);

            DB::commit();

            return $this->success([
                'claim_token' => $claimToken,
                'user' => [
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'nid' => $user->nid,
                    'phone' => $user->phone,
                    'role' => (int) $user->role,
                    'status' => (int) $user->status,
                ],
                'expires_in' => 600
            ], 'Registration Successful', 'Scan QR to claim credential', 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->failed(
                $e->getMessage(),
                'Registration Failed',
                'Something went wrong during registration',
                500
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 2️⃣ Claim Credential (After QR Scan)
    |--------------------------------------------------------------------------
    */
    public function claimCredential(Request $request)
    {
        //Log incoming request for debugging in channel transaction_log
        Log::channel('transaction_log')->info('Claim Credential Request', [
            'request' => $request->all()
        ]);
        $claimToken = $request->claim_token;

        $credential = UserCredential::where('token', $claimToken)->first();

        if (!$credential) {
            return $this->failed(null, 'Claim Failed', 'Invalid or expired claim token', 400);
        }

        $user = $credential->user;

        if (!$user) {
            return $this->failed(null, 'Claim Failed', 'User not found for this token', 404);
        }

        $patient = Patient::where('user_id', $user->id)->first();

        if (!$patient) {
            return $this->failed(null, 'Claim Failed', 'Patient record not found', 404);
        }

        return $this->success([
            'user' => [
                'first_name' => $patient->first_name,
                'last_name'  => $patient->last_name,
                'nid'        => $user->nid,
                'phone'      => $user->phone,
                'role'       => (int) $user->role,
                'status'     => (int) $user->status,
            ],
            'token' => $credential->token
        ], 'Credential Issued', 'Credential successfully claimed', 200);
    }

    /*
    |--------------------------------------------------------------------------
    | 3️⃣ Login Using Credential Token
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $token = $request->token;

        $credential = UserCredential::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$credential) {
            return $this->failed(null, 'Login Failed', 'Invalid or revoked token', 401);
        }

        $user = $credential->user;

        return $this->success([
            'nid' => $user->nid,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status
        ], 'Login Successful', 'Authenticated via credential', 200);
    }

    /*
    |--------------------------------------------------------------------------
    | 4️⃣ GetMe (Credential-based)
    |--------------------------------------------------------------------------
    */
    public function getMe(Request $request)
    {
        $token = $request->token;

        $credential = UserCredential::where('token', $token)
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return $this->failed(null, 'Unauthorized', 'Invalid or revoked token', 401);
        }

        $user = $credential->user;

        return $this->success([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'nid' => $user->nid,
            'phone' => $user->phone,
            'role' => (int) $user->role,
            'status' => (int) $user->status,
        ], 'User Info', 'User information retrieved', 200);
    }

    /*
    |--------------------------------------------------------------------------
    | 5️⃣ Revoke Credential
    |--------------------------------------------------------------------------
    */
    public function revokeCredential(Request $request)
    {
        $nid = $request->nid;

        $user = User::where('nid', $nid)->first();

        if (!$user) {
            return $this->failed(null, 'Revocation Failed', 'User not found', 404);
        }

        $credential = UserCredential::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return $this->failed(null, 'Revocation Failed', 'Token not found', 404);
        }

        $credential->update([
            'status' => 'revoked'
        ]);

        return $this->success(null, 'Credential Revoked', 'Credential has been revoked', 200);
    }

    /*
    |--------------------------------------------------------------------------
    | 6️⃣ Renew Credential
    |--------------------------------------------------------------------------
    */
    public function renewCredential(Request $request)
    {
        $oldToken = $request->token;

        $credential = UserCredential::where('token', $oldToken)
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return $this->failed(null, 'Renewal Failed', 'Invalid or revoked token', 401);
        }

        // Invalidate old token
        $credential->update([
            'token' => bin2hex(random_bytes(32)),
            'status' => 'active'
        ]);

        return $this->success([
            'new_token' => $credential->token
        ], 'Credential Renewed', 'New credential token issued', 200);
    }

    public function renewCredentialNid(Request $request)
    {
        $nid = $request->input('nid');

        $user = User::where('nid', $nid)->first();

        if (!$user) {
            return $this->failed(
                null,
                'Renewal Failed',
                'User not found',
                404
            );
        }

        // Find active credential
        $credential = UserCredential::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($credential) {
            // Revoke old credential
            $credential->update([
                'status' => 'revoked'
            ]);
        }

        // Issue new credential
        $newCredential = UserCredential::create([
            'user_id' => $user->id,
            'token' => bin2hex(random_bytes(32)),
            'status' => 'active'
        ]);

        return $this->success(
            [
                'token' => $newCredential->token
            ],
            'Credential Renewed',
            'A new credential has been issued successfully',
            200
        );
    }

    //createCredentialNid
    public function createCredentialNid(Request $request)
    {
        $nid = $request->input('nid');

        $user = User::where('nid', $nid)->first();

        if (!$user) {
            return $this->failed(
                null,
                'Creation Failed',
                'User not found',
                404
            );
        }

        // Check if user already has an active credential
        $existingCredential = UserCredential::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingCredential) {
            return $this->failed(
                null,
                'Creation Failed',
                'User already has an active credential',
                400
            );
        }

        // Issue new credential
        $credential = UserCredential::create([
            'user_id' => $user->id,
            'token' => bin2hex(random_bytes(32)),
            'status' => 'active'
        ]);

        return $this->success(
            [
                'token' => $credential->token
            ],
            'Credential Created',
            'A new credential has been created successfully',
            201
        );
    }
}