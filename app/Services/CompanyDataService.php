<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Company;

class CompanyDataService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://www.finstat.sk/api/',
            'timeout' => 10.0,
        ]);
    }
    
    public function fetchCompanyDataByIco(string $ico)
    {
        try {
            // This is a placeholder API call. In a real app, you would use a specific
            // Slovakian business registry API with proper authentication
            // Example APIs: finstat.sk, registeruz.sk, or orsr.sk
            
            // Simulating API response for example purposes
            // In real implementation, you would make an actual API request here
            
            // Simulate successful API response
            if (strlen($ico) === 8 && is_numeric($ico)) {
                return [
                    'success' => true,
                    'data' => [
                        'ico' => $ico,
                        'name' => 'Spoločnosť ' . $ico,
                        'street' => 'Hlavná ' . rand(1, 100),
                        'city' => 'Bratislava',
                        'postal_code' => '811 0' . rand(1, 9),
                        'country' => 'Slovensko',
                        'dic' => '202' . rand(1000000, 9999999),
                        'ic_dph' => 'SK202' . rand(1000000, 9999999),
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Neplatné IČO'
            ];
            
        } catch (Exception $e) {
            Log::error('Error fetching company data: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Chyba pri získavaní údajov spoločnosti'
            ];
        }
    }
    
    public function findOrCreateCompany(string $ico)
    {
        // First check if company already exists in our database
        $company = Company::where('ico', $ico)->first();
        
        if ($company) {
            return $company;
        }
        
        // If not, fetch from API and create
        $companyData = $this->fetchCompanyDataByIco($ico);
        
        if (!$companyData['success']) {
            return null;
        }
        
        // Create new company record
        return Company::create([
            'ico' => $companyData['data']['ico'],
            'name' => $companyData['data']['name'],
            'street' => $companyData['data']['street'],
            'city' => $companyData['data']['city'],
            'postal_code' => $companyData['data']['postal_code'],
            'country' => $companyData['data']['country'],
            'dic' => $companyData['data']['dic'],
            'ic_dph' => $companyData['data']['ic_dph'],
        ]);
    }
}
