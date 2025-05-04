<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompanyController extends Controller
{
    public function fetchByIco(Request $request)
    {
        $ico = $request->input('ico');

        if (! $ico) {
            return response()->json([
                'success' => false,
                'message' => 'IČO je povinné',
            ]);
        }

        try {
            // This should be replaced with your actual API endpoint for fetching company data
            // This is a placeholder implementation
            $response = Http::get("https://api.example.com/companies/{$ico}");

            if ($response->successful()) {
                $data = $response->json();

                // Here we're simulating a successful response with dummy data
                // In a real implementation, you would format the actual API response
                return response()->json([
                    'success' => true,
                    'data' => [
                        'name' => 'Spoločnosť '.$ico,
                        'street' => 'Hlavná ulica 123',
                        'city' => 'Bratislava',
                        'postal_code' => '81101',
                        'dic' => '2023'.$ico,
                        'ic_dph' => 'SK2023'.$ico,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Spoločnosť s týmto IČO nebola nájdená',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chyba pri načítaní údajov: '.$e->getMessage(),
            ], 500);
        }
    }
}
