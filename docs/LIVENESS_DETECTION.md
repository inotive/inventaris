# Liveness Detection Integration

## Overview

Fitur liveness detection telah diintegrasikan ke dalam sistem absensi untuk memastikan bahwa foto yang digunakan untuk absensi adalah foto wajah asli (live), bukan foto dari layar atau cetakan.

## API Provider

**MXFace Liveness Detection API**
- **URL**: `https://faceapi.mxface.ai/api/v3/face/Liveness`
- **Method**: POST
- **Content-Type**: application/json

## Configuration

### 1. Environment Variables

Tambahkan subscription key ke file `.env`:

```env
MXFACE_SUBSCRIPTION_KEY=your_subscription_key_here
```

### 2. Minimum Liveness Score

Default minimum score adalah **70.0**. Bisa diubah di:
```php
// app/Services/LivenessDetectionService.php
private const MIN_LIVENESS_SCORE = 70.0;
```

## How It Works

### Flow Diagram

```
User captures photo (base64)
         ↓
Frontend: Convert photo blob to base64
         ↓
Frontend: Call MXFace API with base64 image
         ↓
API returns livenessScore
         ↓
Score >= 70? 
    ├─ YES → Send request to backend with photo
    │        ↓
    │        Backend saves attendance
    │        ↓
    │        Return success response
    └─ NO  → Show error message (no backend call)
             Return rejection response immediately
```

### Request Format

**Headers:**
```json
{
  "Content-Type": "application/json",
  "Subscriptionkey": "your_subscription_key"
}
```

**Body:**
```json
{
  "image": "base64_encoded_image_without_prefix"
}
```

**Response:**
```json
{
  "livenessScore": 83.39
}
```

## Integration Points

### 1. Frontend (Vue.js)

File: `resources/js/Pages/Admin/Presence/Index.vue`

**Liveness Check Function:**
```javascript
async function checkLivenessAPI(photoBlob) {
    // Convert blob to base64
    const base64Image = await new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => {
            const base64 = reader.result.split(',')[1];
            resolve(base64);
        };
        reader.onerror = reject;
        reader.readAsDataURL(photoBlob);
    });

    // Call MXFace API
    const response = await fetch('https://faceapi.mxface.ai/api/v3/face/Liveness', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Subscriptionkey': import.meta.env.VITE_MXFACE_SUBSCRIPTION_KEY
        },
        body: JSON.stringify({ image: base64Image })
    });

    const data = await response.json();
    const livenessScore = data.livenessScore || 0;
    
    return {
        isLive: livenessScore >= 70.0,
        score: livenessScore,
        message: livenessScore >= 70.0 
            ? 'Verifikasi liveness berhasil' 
            : 'Foto tidak terdeteksi sebagai wajah asli'
    };
}
```

**Integration in notifyHit:**
```javascript
async function notifyHit({ employeeId, distance, photo }) {
    // Check liveness first if photo is provided
    if (photo) {
        const livenessResult = await checkLivenessAPI(photo);
        
        if (!livenessResult.isLive) {
            // Return error without calling backend
            return {
                ok: false,
                status: 400,
                message: `${livenessResult.message} (Score: ${livenessResult.score.toFixed(2)})`,
                type: 4,
                data: { employee: { id: employeeId }, status: 'DITOLAK' }
            };
        }
    }
    
    // Continue with attendance submission...
}
```

### 2. Backend (Optional - for Mobile API)

File: `app/Services/LivenessDetectionService.php`

**Note:** Backend service is available but not used for web camera attendance. Can be used for mobile API if needed.

**Main Method:**
```php
public function checkLiveness(string $base64Image, ?string $subscriptionKey = null): array
```

**Return Format:**
```php
[
    'is_live' => bool,      // true if score >= MIN_LIVENESS_SCORE
    'score' => float,       // livenessScore from API
    'message' => string,    // User-friendly message
    'error' => bool         // true if API call failed
]
```

## API Endpoints Affected

### 1. Camera Attendance (Web) ✅
- **Endpoint**: `POST /attendance-employee`
- **Controller**: `PresenceController@attendaceEmployee`
- **Frontend**: Liveness check performed before API call
- **Location**: `resources/js/Pages/Admin/Presence/Index.vue`

### 2. QR Code Attendance (Mobile)
- **Endpoint**: `POST /attendance-employee-barcode`
- **Controller**: `PresenceController@attendaceEmployeeBarcode`
- **Note**: QR code attendance may need frontend liveness check integration

### 3. Mobile API
- **Endpoint**: `POST /api/v1/attendances/hit`
- **Controller**: `Api\v1\AttendanceController@hit`
- **Note**: Can use backend LivenessDetectionService if needed

## Request Examples

### Web/Camera Attendance

```javascript
// Frontend (Vue.js)
const formData = new FormData();
formData.append('employee_id', employeeId);
formData.append('photo_base64', capturedImageBase64); // data:image/jpeg;base64,/9j/4AAQ...

axios.post('/attendance-employee', formData);
```

### Mobile API

```javascript
// Mobile App
const response = await fetch('/api/v1/attendances/hit', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        photo_base64: capturedImageBase64,
        latitude: -6.2088,
        longitude: 106.8456
    })
});
```

## Response Examples

### Success (Live Face Detected)

```json
{
    "message": "Anda berhasil absen tepat waktu. Terima kasih atas kedisiplinan Anda.",
    "type": 1,
    "data": {
        "employee": {
            "id": 1,
            "name": "John Doe"
        },
        "type": 1,
        "status": "RUNNING",
        "dateTime": "2025-11-09 16:50:00"
    }
}
```

### Failure (Fake Face Detected)

```json
{
    "message": "Foto tidak terdeteksi sebagai wajah asli. Silakan gunakan kamera langsung. (Score: 45.23)",
    "type": 4,
    "data": {
        "employee": {
            "id": 1,
            "name": "John Doe"
        },
        "type": 4,
        "status": "DITOLAK",
        "dateTime": "2025-11-09 16:50:00"
    }
}
```

### API Error

```json
{
    "message": "Terjadi kesalahan saat verifikasi liveness: Connection timeout",
    "type": 4,
    "data": {
        "employee": {
            "id": 1,
            "name": "John Doe"
        },
        "type": 4,
        "status": "DITOLAK",
        "dateTime": "2025-11-09 16:50:00"
    }
}
```

## Logging

All liveness checks are logged for debugging and audit purposes:

### Success Log
```
[INFO] Liveness check passed
{
    "employee_id": 1,
    "score": 83.39
}
```

### Failure Log
```
[WARNING] Liveness check failed for employee
{
    "employee_id": 1,
    "score": 45.23,
    "message": "Foto tidak terdeteksi sebagai wajah asli"
}
```

### API Error Log
```
[ERROR] Liveness API request failed
{
    "status": 500,
    "body": "Internal Server Error"
}
```

## Testing

### Manual Testing

1. **Test with live camera capture** (should pass):
```bash
# Capture photo using device camera
# Expected: livenessScore >= 70
```

2. **Test with photo of a photo** (should fail):
```bash
# Take a photo of a printed photo or screen
# Expected: livenessScore < 70
```

3. **Test without photo_base64** (should skip liveness check):
```bash
# Send request without photo_base64 field
# Expected: No liveness check performed
```

### Unit Testing

```php
// tests/Unit/LivenessDetectionServiceTest.php
public function test_liveness_check_with_live_face()
{
    $service = new LivenessDetectionService();
    $result = $service->checkLiveness($livePhotoBase64);
    
    $this->assertTrue($result['is_live']);
    $this->assertGreaterThanOrEqual(70, $result['score']);
}

public function test_liveness_check_with_fake_face()
{
    $service = new LivenessDetectionService();
    $result = $service->checkLiveness($fakePhotoBase64);
    
    $this->assertFalse($result['is_live']);
    $this->assertLessThan(70, $result['score']);
}
```

## Troubleshooting

### Issue: "Liveness detection service not configured"

**Solution**: Add `MXFACE_SUBSCRIPTION_KEY` to `.env` file

### Issue: "Gagal melakukan verifikasi liveness"

**Possible causes**:
1. Invalid subscription key
2. API endpoint is down
3. Network timeout
4. Invalid image format

**Solution**: Check logs for detailed error message

### Issue: All photos are rejected

**Possible causes**:
1. MIN_LIVENESS_SCORE is too high
2. Poor image quality
3. Bad lighting conditions

**Solution**: 
- Adjust MIN_LIVENESS_SCORE
- Improve camera quality
- Ensure good lighting

## Performance Considerations

- **API Timeout**: 10 seconds
- **Image Size**: Recommend max 2MB for faster processing
- **Caching**: No caching implemented (each check is fresh)
- **Async**: Currently synchronous (blocks attendance process)

## Future Improvements

1. **Async Processing**: Move liveness check to queue for better UX
2. **Retry Logic**: Implement retry mechanism for failed API calls
3. **Fallback**: Allow manual approval if API is down
4. **Analytics**: Track liveness scores for optimization
5. **Multiple Providers**: Support multiple liveness detection providers

## Security Notes

- ✅ Subscription key stored in `.env` (not in code)
- ✅ API calls logged for audit trail
- ✅ Base64 validation before sending to API
- ✅ Error messages don't expose sensitive info
- ⚠️ Consider encrypting logs containing face data

## References

- [MXFace API Documentation](https://faceapi.mxface.ai/docs)
- [Laravel HTTP Client](https://laravel.com/docs/http-client)
- [Base64 Image Encoding](https://developer.mozilla.org/en-US/docs/Web/API/FileReader/readAsDataURL)
