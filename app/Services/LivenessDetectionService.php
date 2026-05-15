<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LivenessDetectionService
{
    private const API_URL = 'https://faceapi.mxface.ai/api/v3/face/Liveness';
    private const MIN_LIVENESS_SCORE = 70.0; // Minimum score untuk dianggap live
    
    /**
     * Check if the face in the image is live (not a photo/video)
     * 
     * @param string $base64Image Base64 encoded image string
     * @param string|null $subscriptionKey API subscription key
     * @return array ['is_live' => bool, 'score' => float, 'message' => string]
     */
    public function checkLiveness(string $base64Image, ?string $subscriptionKey = null): array
    {
        try {
            // Get subscription key from env if not provided
            $subscriptionKey = $subscriptionKey ?? env('MXFACE_SUBSCRIPTION_KEY');
            
            if (!$subscriptionKey) {
                Log::error('MXFace subscription key not configured');
                return [
                    'is_live' => false,
                    'score' => 0,
                    'message' => 'Liveness detection service not configured',
                    'error' => true
                ];
            }

            // Remove data:image prefix if exists
            $imageData = $this->cleanBase64Image($base64Image);
            
            Log::info('Calling liveness detection API', [
                'image_length' => strlen($imageData),
                'api_url' => self::API_URL
            ]);

            // Call MXFace API
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Subscriptionkey' => $subscriptionKey,
                ])
                ->post(self::API_URL, [
                    'image' => $imageData
                ]);

            if (!$response->successful()) {
                Log::error('Liveness API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'is_live' => false,
                    'score' => 0,
                    'message' => 'Gagal melakukan verifikasi liveness',
                    'error' => true
                ];
            }

            $data = $response->json();
            $livenessScore = $data['livenessScore'] ?? 0;

            Log::info('Liveness detection result', [
                'score' => $livenessScore,
                'threshold' => self::MIN_LIVENESS_SCORE
            ]);

            $isLive = $livenessScore >= self::MIN_LIVENESS_SCORE;

            return [
                'is_live' => $isLive,
                'score' => $livenessScore,
                'message' => $isLive 
                    ? 'Verifikasi liveness berhasil' 
                    : 'Foto tidak terdeteksi sebagai wajah asli. Silakan gunakan kamera langsung.',
                'error' => false
            ];

        } catch (\Exception $e) {
            Log::error('Liveness detection exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_live' => false,
                'score' => 0,
                'message' => 'Terjadi kesalahan saat verifikasi liveness: ' . $e->getMessage(),
                'error' => true
            ];
        }
    }

    /**
     * Clean base64 image string (remove data:image prefix)
     */
    private function cleanBase64Image(string $base64Image): string
    {
        // Remove data:image/xxx;base64, prefix if exists
        if (preg_match('/^data:image\/\w+;base64,/', $base64Image)) {
            return substr($base64Image, strpos($base64Image, ',') + 1);
        }
        
        return $base64Image;
    }

    /**
     * Validate if image is in correct base64 format
     */
    public function validateBase64Image(string $base64Image): bool
    {
        // Check if it's a valid base64 string
        $cleaned = $this->cleanBase64Image($base64Image);
        
        if (base64_encode(base64_decode($cleaned, true)) !== $cleaned) {
            return false;
        }
        
        return true;
    }
}
