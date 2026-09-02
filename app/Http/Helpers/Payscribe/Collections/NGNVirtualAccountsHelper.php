<?php

namespace App\Http\Helpers\Payscribe\Collections;

use App\Http\Helpers\ConnectionHelper;
use Exception;
use Illuminate\Support\Facades\Log;

class NGNVirtualAccountsHelper extends ConnectionHelper
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create Virtual Account (Static or Dynamic)
     */
    public function createVirtualAccount($data)
    {
        try {
            $url = '/collections/virtual-accounts/create';
            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe createVirtualAccount Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to create virtual account',
                'status_code' => 500
            ];
        }
    }

    /**
     * Get Virtual Account Details
     */
    public function getVirtualAccountDetails($data)
    {
        try {
            $url = "/collections/virtual-accounts/{$data}";
            $response = $this->get($url);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe getVirtualAccountDetails Error: ' . $e->getMessage(), [
                'account' => $data,
                'trace'   => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to fetch virtual account details',
                'status_code' => 500
            ];
        }
    }

    /**
     * Deactivate Virtual Account
     */
    public function deactivateVirtualAccount($data)
    {
        try {
            $url = '/collections/virtual-accounts/deactivate';
            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe deactivateVirtualAccount Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to deactivate virtual account',
                'status_code' => 500
            ];
        }
    }

    /**
     * Activate Virtual Account
     */
    public function activateVirtualAccount($data)
    {
        try {
            $url = '/collections/virtual-accounts/activate';
            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe activateVirtualAccount Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to activate virtual account',
                'status_code' => 500
            ];
        }
    }

    /**
     * Create Dynamic (Temporary) Virtual Account
     */
    public function createDynamicTemporaryVirtualAccount($data)
    {
        try {
            $url = '/collections/virtual-accounts/create';

            $data['account_type'] = 'dynamic';
            $data['currency']     = $data['currency'] ?? 'NGN';

            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe createDynamicTemporaryVirtualAccount Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to create dynamic virtual account',
                'status_code' => 500
            ];
        }
    }

    /**
     * Confirm / Verify Payment
     */
    public function verifyPayment($data)
    {
        try {
            $url = '/collections/virtual-accounts/confirm-payment';
            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe verifyPayment Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to verify payment',
                'status_code' => 500
            ];
        }
    }

    /**
     * Simulate a Transaction (Sandbox only)
     */
    public function simulateATransaction($data)
    {
        try {
            $url = '/collections/virtual-accounts/simulate-transfer';
            $response = $this->post($url, $data);
            return json_decode($response, true);
        } catch (Exception $e) {
            Log::error('Payscribe simulateATransaction Error: ' . $e->getMessage(), [
                'data'  => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status'      => false,
                'description' => 'Failed to simulate transaction',
                'status_code' => 500
            ];
        }
    }
}