<?php

namespace App\Http\Controllers;

use App\Models\QRToken;
use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QRTokenController extends Controller
{
    /**
     * Generate QR token (valid for 10 minutes)
     */
    public function generate()
    {
        $token = 'medchain_' . now()->timestamp . '_' . bin2hex(random_bytes(16));

        $qrToken = QRToken::create([
            'user_id'    => null,
            'token'      => $token,
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false
        ]);

        return $this->success([
            'token'       => $qrToken->token,
            'expires_at'  => $qrToken->expires_at->format('Y-m-d H:i:s')
        ], 'QR token', 'QR token generated successfully');
    }

    /**
     * Cleanup expired tokens
     */
    public function getPatient(Request $request)
    {
        $validated = $request->validate([
            'qr_token' => 'required|string'
        ]);

        // 1. Get QR token
        $qrToken = QRToken::where('token', $validated['qr_token'])->first();

        if (!$qrToken) {
            return $this->failed(null, 'Invalid QR token', 'QR token', 404);
        }

        // 2. Check expiration
        if ($qrToken->expires_at < now()) {
            return $this->failed(null, 'Token expired', 'QR token', 400);
        }

        // 3. Ensure token is approved (VERY IMPORTANT 🔥)
        if (!$qrToken->is_used || !$qrToken->user_id) {
            return $this->failed(null, 'Token not approved yet', 'QR token', 403);
        }

        // 4. Get user
        $user = User::find($qrToken->user_id);

        if (!$user) {
            return $this->failed(null, 'User not found', 'User', 404);
        }

        return $this->success([
            'user' => $user,
            'approved_at' => $qrToken->expires_at
        ], 'QR token', 'Patient retrieved successfully');
    }

    /**
     * Approve / Reject QR token
     */
    public function approve(Request $request)
    {
        $validated = $request->validate([
            'user_token' => 'required|string',
            'qr_token'   => 'required|string',
            'approve'    => 'required|boolean'
        ]);

        return DB::transaction(function () use ($validated) {

            // 1. Validate user
            $credential = UserCredential::where('token', $validated['user_token'])->first();

            if (!$credential) {
                return $this->failed(null, 'Invalid user token', 'User token', 404);
            }

            $user = User::find($credential->user_id);

            if (!$user) {
                return $this->failed(null, 'User not found', 'User', 404);
            }

            // 2. Lock QR token (prevent double approval)
            $qrToken = QRToken::where('token', $validated['qr_token'])
                ->lockForUpdate()
                ->first();

            if (!$qrToken) {
                return $this->failed(null, 'Invalid QR token', 'QR token', 404);
            }

            // 3. Validate token state
            if ($qrToken->expires_at < now()) {
                return $this->failed(null, 'Token expired', 'QR token', 400);
            }

            if ($qrToken->is_used) {
                return $this->failed(null, 'Token already used', 'QR token', 400);
            }

            // 4. Handle approval
            if ($validated['approve']) {

                $qrToken->update([
                    'is_used'     => true,
                    'user_id'     => $user->id,
                    'approved_at' => now()
                ]);

                return $this->success([
                    'user_id'     => $user->id,
                    'qr_token'    => $qrToken->token,
                    'approved_at' => $qrToken->approved_at->format('Y-m-d H:i:s')
                ], 'QR token', 'QR token approved successfully');

            } else {

                $qrToken->delete();

                return $this->success(null, 'QR token', 'QR token rejected successfully');
            }
        });
    }
}
