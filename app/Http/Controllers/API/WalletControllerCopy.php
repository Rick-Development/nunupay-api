<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WalletControllerCopy extends Controller
{
    protected $baseUrl = 'http://102.216.128.75:9090'; // Replace with your actual API base URL

    public function openWallet(Request $request)
    {
        $request->validate([
            'transactionTrackingRef' => 'required|string',
            'lastName' => 'required|string',
            'otherNames' => 'required|string',
            'accountName' => 'required|string',
            'phoneNo' => 'required|string',
            'gender' => 'required|integer',
            'dateOfBirth' => 'required|date',
            'email' => 'required|email',
            'bvn' => 'required|string',
            'customerID' => 'required|string',
            'walletType' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/open_wallet", $request->all());

        return $response->json();
    }

    public function debitTransfer(Request $request)
    {
        $request->validate([
            'accountNo' => 'required|string',
            'totalAmount' => 'required|numeric',
            'transactionId' => 'required|string',
            'narration' => 'required|string',
            'merchant' => 'required|array',
            'transactionType' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/debit/transfer", $request->all());

        return $response->json();
    }

    public function creditTransfer(Request $request)
    {
        $request->validate([
            'accountNo' => 'required|string',
            'totalAmount' => 'required|numeric',
            'transactionId' => 'required|string',
            'narration' => 'required|string',
            'merchant ' => 'required|array',
            'transactionType' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/credit/transfer", $request->all());

        return $response->json();
    }

    public function getTransactionHistory(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'fromDate' => 'required|date',
            'toDate' => 'required|date',
            'numberOfItems' => 'required|integer',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/wallet_transactions", $request->all());

        return $response->json();
    }

    public function walletRequery(Request $request)
    {
        $request->validate([
            'sessionID' => 'required|string',
            'accountNumber' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/notification_requery", $request->all());

        return $response->json();
    }

    public function getWalletStatus(Request $request)
    {
        $request->validate([
            'accountNo' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/wallet_status", $request->all());

        return $response->json();
    }

    public function changeWalletStatus(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'accountStatus' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/change_wallet_status", $request->all());

        return $response->json();
    }

    public function getBanks(Request $request)
    {
        $response = Http::post("{$this->baseUrl}/waas/api/v1/get_banks", $request->all());

        return $response->json();
    }

    public function getWallet(Request $request)
    {
        $request->validate([
            'bvn' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/get_wallet", $request->all());

        return $response->json();
    }

    public function openCorporateAccount(Request $request)
    {
        $request->validate([
            'phoneNo' => 'required|string',
            'postalAddress' => 'required|string',
            'taxIDNo' => 'required|string',
            'businessName' => 'required|string',
            'tradeName' => 'required|string',
            'industrialSector' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
            'companyRegDate' => 'required|date',
            'contactPersonFirstName' => 'required|string',
            'contactPersonLastName' => 'required|string',
            'businessType' => 'required|string',
            'natureOfBusiness' => 'required|string',
            'webAddress' => 'required|string',
            'dateIncorporated' => 'required|date',
            'businessCommencementDate' => 'required|date',
            'registrationNumber' => 'required|string',
            'cacCertificate' => 'required|string',
            'scumlCertificate' => 'required|string',
            'regulatoryLicenseFintech' => 'required|string',
            'utilityBill' => 'required|string',
            'proofOfAddressVerification' => 'required|string',
            'directors' => 'required|array',
            'accountSignatories' => 'required|array',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/open_corporate_account", $request->all());

        return $response->json();
    }

    public function getAccountNumber(Request $request)
    {
        $request->validate([
            'taxIDNo' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/get_account_number", $request->all());

        return $response->json();
    }

    public function getRequestStatus(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/get_request_status", $request->all());

        return $response->json();
    }

    public function upgradeAccount(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'bvn' => 'required|string',
            'accountName' => 'required|string',
            'phoneNumber' => 'required|string',
            'tier' => 'required|string',
            'email' => 'required|email',
            'userPhoto' => 'required|string',
            'idType' => 'required|string',
            'idNumber' => 'required|string',
            'idIssueDate' => 'required|date',
            'idExpiryDate' => 'required|date',
            'idCardFront' => 'required|string',
            'idCardBack' => 'required|string',
            'houseNumber' => 'required|string',
            'streetName' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'localGovernment' => 'required|string',
            'pep' => 'required|string',
            'customerSignature' => 'required|string',
            'utilityBill' => 'required|string',
            'nearestLandmark' => 'required|string',
            'placeOfBirth' => 'required|string',
            'proofOfAddressVerification' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/wallet_upgrade", $request->all());

        return $response->json();
    }

    public function upgradeAccountFileUpload(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'bvn' => 'required|string',
            'accountName' => 'required|string',
            'phoneNumber' => 'required|string',
            'tier' => 'required|string',
            'email' => 'required|email',
            'userPhoto' => 'required|file',
            'idType' => 'required|string',
            'idNumber' => 'required|string',
            'idIssueDate' => 'required|date',
            'idExpiryDate' => 'required|date',
            'idCardFront' => 'required|file',
            'idCardBack' => 'required|file',
            'houseNumber' => 'required|string',
            'streetName' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'localGovernment' => 'required|string',
            'pep' => 'required|string',
            'customerSignature' => 'required|file',
            'utilityBill' => 'required|file',
            'nearestLandmark' => 'required|string',
            'placeOfBirth' => 'required|string',
            'proofOfAddressVerification' => 'required|file',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/wallet_upgrade_file_upload", $request->all());

        return $response->json();
    }

    public function upgradeTier3Base64(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'bvn' => 'required|string',
            'proofOfAddressVerification' => 'required|string',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/walletUpgrade-tier3-base64", $request->all());

        return $response->json();
    }

    public function upgradeTier3Multipart(Request $request)
    {
        $request->validate([
            'accountNumber' => 'required|string',
            'bvn' => 'required|string',
            'proofOfAddressVerification' => 'required|file',
        ]);

        $response = Http::post("{$this->baseUrl}/waas/api/v1/walletUpgrade-tier3-multipart", $request->all());

        return $response->json();
    }
}