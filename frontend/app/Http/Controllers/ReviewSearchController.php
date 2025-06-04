<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReviewSearchController extends Controller
{
    private $pythonApiUrl;
    
    public function __construct()
    {
        // URL API Python - sesuaikan dengan server Anda
        $this->pythonApiUrl = env('PYTHON_API_URL', 'http://localhost:5000/api');
    }
    
    /**
     * Tampilkan halaman search
     */
    public function index()
    {
        return view('search.index');
    }
    
    /**
     * Handle search request via API
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);
        
        try {
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/search', [
                'query' => $request->input('query'),
                'limit' => $request->input('limit', 10)
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Directly return the Python API response
                // since it already has the correct structure
                return response()->json($data);
                
            } else {
                Log::error('Python API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Search service unavailable',
                    'query' => $request->input('query'),
                    'total_results' => 0,
                    'results' => []
                ], 503);
            }
            
        } catch (\Exception $e) {
            Log::error('Search Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching: ' . $e->getMessage(),
                'query' => $request->input('query'),
                'total_results' => 0,
                'results' => []
            ], 500);
        }
    }

    /**
     * Handle search request via web form (for traditional form submission)
     */
    public function searchWeb(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);
        
        try {
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/search', [
                'query' => $request->input('query'),
                'limit' => $request->input('limit', 10)
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    return view('search.index', [
                        'results' => $data['results'] ?? [],
                        'query' => $request->input('query'),
                        'limit' => $request->input('limit', 10),
                        'total_results' => $data['total_results'] ?? 0
                    ]);
                } else {
                    return view('search.index', [
                        'results' => [],
                        'query' => $request->input('query'),
                        'limit' => $request->input('limit', 10),
                        'total_results' => 0,
                        'error' => $data['message'] ?? 'Search failed'
                    ]);
                }
            } else {
                Log::error('Python API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return view('search.index', [
                    'results' => [],
                    'query' => $request->input('query'),
                    'limit' => $request->input('limit', 10),
                    'total_results' => 0,
                    'error' => 'Search service unavailable'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Search Error: ' . $e->getMessage());
            
            return view('search.index', [
                'results' => [],
                'query' => $request->input('query'),
                'limit' => $request->input('limit', 10),
                'total_results' => 0,
                'error' => 'An error occurred while searching'
            ]);
        }
    }
    
    /**
     * Get search statistics
     */
    public function stats()
    {
        try {
            $response = Http::timeout(10)->get($this->pythonApiUrl . '/stats');
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not fetch statistics'
                ], 503);
            }
            
        } catch (\Exception $e) {
            Log::error('Stats Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Statistics service unavailable'
            ], 500);
        }
    }
    
    /**
     * Check API health
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)->get($this->pythonApiUrl . '/health');
            
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'python_api' => $data,
                    'laravel_api' => [
                        'success' => true,
                        'message' => 'Laravel API is running',
                        'timestamp' => now()
                    ]
                ]);
            } else {
                return response()->json([
                    'python_api' => [
                        'success' => false,
                        'message' => 'Python API unavailable'
                    ],
                    'laravel_api' => [
                        'success' => true,
                        'message' => 'Laravel API is running'
                    ]
                ], 503);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'python_api' => [
                    'success' => false,
                    'message' => 'Python API connection failed'
                ],
                'laravel_api' => [
                    'success' => true,
                    'message' => 'Laravel API is running'
                ]
            ], 503);
        }
    }
}