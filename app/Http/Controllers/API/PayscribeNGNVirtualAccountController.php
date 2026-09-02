<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\Collections\NGNVirtualAccountsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PayscribeNGNVirtualAccountController extends Controller
{
    public function __construct(private NGNVirtualAccountsHelper $nGNVirtualAccountsHelper)
    {
    }

    /**
     * Get Virtual Account Details
     */
    public function virtualAccountDetails()
    {
        try {
            $user = auth()->user();

            if (empty($user->account_number)) {
                return response()->json([
                    'status'      => false,
                    'description' => 'No virtual account found for this user',
                    'status_code' => 404
                ], 404);
            }

            $response = $this->nGNVirtualAccountsHelper->getVirtualAccountDetails($user->account_number);

            return response()->json($response, $response['status_code'] ?? 500);

        } catch (Exception $e) {
            Log::error('Virtual Account Details Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'      => false,
                'description' => 'Unable to fetch virtual account details at the moment',
                'status_code' => 500
            ], 500);
        }
    }

    /**
     * Deactivate Virtual Account
     */
    public function deactivateVirtualAccount()
    {
        try {
            $user = auth()->user();

            if (empty($user->account_number)) {
                return response()->json([
                    'status'      => false,
                    'description' => 'No virtual account found for this user',
                    'status_code' => 404
                ], 404);
            }

            $data = [
                'account' => $user->account_number
            ];

            $response = $this->nGNVirtualAccountsHelper->deactivateVirtualAccount($data);

            return response()->json($response, $response['status_code'] ?? 500);

        } catch (Exception $e) {
            Log::error('Deactivate Virtual Account Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'      => false,
                'description' => 'Unable to deactivate virtual account at the moment',
                'status_code' => 500
            ], 500);
        }
    }

    /**
     * Activate Virtual Account
     */
    public function activateVirtualAccount()
    {
        try {
            $user = auth()->user();

            if (empty($user->account_number)) {
                return response()->json([
                    'status'      => false,
                    'description' => 'No virtual account found for this user',
                    'status_code' => 404
                ], 404);
            }

            $data = [
                'account' => $user->account_number
            ];

            $response = $this->nGNVirtualAccountsHelper->activateVirtualAccount($data);

            return response()->json($response, $response['status_code'] ?? 500);

        } catch (Exception $e) {
            Log::error('Activate Virtual Account Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'      => false,
                'description' => 'Unable to activate virtual account at the moment',
                'status_code' => 500
            ], 500);
        }
    }

    /**
     * Create Dynamic (Temporary) Virtual Account - Used for Funding
     * Wrapped in DB transaction for ACID compliance
     */
    public function dynamicTemporaryVirtualAccount(Request $request)
    {
        try {
            $user = auth()->user();

            $reqData = $request->validate([
                'amount' => 'required|integer|min:100',
            ]);

            return DB::transaction(function () use ($user, $reqData) {

                $referenceId = (string) Str::uuid();

                // Clean phone number
                $phone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
                $phone = ltrim($phone, '0');

                $data = [
                    'account_type' => 'dynamic',
                    'ref'          => $referenceId,
                    'currency'     => 'NGN',
                    'order' => [
                        'amount'       => (int) $reqData['amount'],
                        'amount_type'  => 'EXACT',
                        'description'  => "Wallet funding for {$user->firstname} {$user->lastname}",
                        'expiry' => [
                            'duration'      => 1,
                            'duration_type' => 'hours'
                        ]
                    ],
                    'customer' => [
                        'name'  => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')),
                        'email' => $user->email,
                        'phone' => '0' . $phone,
                    ]
                ];

                $response = $this->nGNVirtualAccountsHelper->createDynamicTemporaryVirtualAccount($data);

                // Optional: You can save the dynamic account details to your DB here if needed
                // Example:
                // if (isset($response['status']) && $response['status'] === true) {
                //     // Save reference, account number, amount, expiry etc.
                // }

                return response()->json($response, $response['status_code'] ?? 500);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'      => false,
                'description' => 'Validation failed',
                'errors'      => $e->errors(),
                'status_code' => 422
            ], 422);

        } catch (Exception $e) {
            Log::error('Create Dynamic Virtual Account Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'amount'  => $request->amount ?? null,
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'      => false,
                'description' => 'Unable to generate virtual account at the moment. Please try again.',
                'status_code' => 500
            ], 500);
        }
    }
}