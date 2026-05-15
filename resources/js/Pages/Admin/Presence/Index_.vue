<script setup>
import { defineProps, ref, onMounted, onBeforeUnmount, watch } from "vue";
import ToggleSwitch from "primevue/toggleswitch";
import { RadioButton } from "primevue";
import * as faceapi from "@vladmandic/face-api";
import * as tf from "@tensorflow/tfjs";
import "@tensorflow/tfjs-backend-webgl";
import "@tensorflow/tfjs-backend-wasm";
import AbsenceStatuses from "./AbsenceStatuses.vue";
import Pusher from "pusher-js";

const props = defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
});

const position = ref({
    latitude: null,
    longitude: null,
});

let showAbsenceStatuses = ref(false);

let isCameraOn = ref(true);

const textList = [
    "Posisikan wajah di tengah bingkai",
    "Pastikan cahaya cukup & tidak membelakangi sumber cahaya.",
    "Lepaskan aksesoris yang menutupi wajah.",
];
const options = [
    { label: "Camera", value: "camera" },
    { label: "QR", value: "qr" },
];

const selectedOption = ref(options[0].value);

const currentTime = ref(new Date().toLocaleTimeString());

setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString();
}, 1000);

const currentDate = ref(
    new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "short",
        day: "numeric",
    })
);

// Generate attendance URL for QR code
const attendanceUrl = ref(" ");

const generateAttendanceUrl = () => {
    const qrToken =
        Math.random().toString(36).substring(2, 15) +
        Math.random().toString(36).substring(2, 15);
    const baseUrl = window.location.origin;
    const attendancePath = `/api/v1/attendance-employee-barcode?qr_token=${qrToken}`;
    console.log(attendancePath);
    attendanceUrl.value = `${baseUrl}${attendancePath}`;
};

// Watch selectedOption to automatically start/stop camera
watch(selectedOption, (newValue) => {
    if (newValue === "camera" && isCameraOn.value) {
        start();
    } else if (newValue === "qr") {
        stop();
    }
});

// Generate URL on component mount
onMounted(() => {
    generateAttendanceUrl();
    console.log(attendanceUrl.value);
    // Start camera if default is camera mode
    if (selectedOption.value === "camera" && isCameraOn.value) {
        start();
        generateAttendanceUrl();
    }
});

const videoRef = ref(null);
const canvasRef = ref(null);

const notifyData = ref({
    message: "",
    data: null,
    type: null,
});

// Face detection stability tracking with voting mechanism
const detectionHistory = ref([]);
const MAX_HISTORY_SIZE = 10; // Store last 10 detections
const STABILITY_DELAY = 2000; // 2 seconds in milliseconds
const stableDetection = ref({
    employeeId: null,
    distance: null,
    startTime: null,
    isStable: false,
});
let stabilityTimer = null;

// Function to get most consistent employee ID from history
function getMostConsistentEmployee() {
    if (detectionHistory.value.length === 0) return null;

    // Count occurrences of each employee ID
    const counts = {};
    detectionHistory.value.forEach((id) => {
        counts[id] = (counts[id] || 0) + 1;
    });

    // Find the most frequent employee ID
    let maxCount = 0;
    let mostConsistent = null;

    for (const [id, count] of Object.entries(counts)) {
        if (count > maxCount) {
            maxCount = count;
            mostConsistent = id;
        }
    }

    // Return only if it appears in at least 60% of detections
    const threshold = detectionHistory.value.length * 0.8;
    return maxCount >= threshold ? mostConsistent : null;
}

// Anti-spoofing detection functions
const previousFrames = ref([]);
const MAX_FRAME_HISTORY = 5;
const landmarkHistory = ref([]);
const ENABLE_MOTION_CHECK = false; // Set to false to disable motion detection

function detectLivenessQuality(detection) {
    // Check detection quality score
    const detectionScore = detection.detection.score;

    // More lenient threshold for detection quality (was 0.85, now 0.75)
    // This allows more valid detections while still filtering low-quality ones
    if (detectionScore < 0.75) {
        return {
            isLive: false,
            reason: "Kualitas deteksi rendah (foto/video)",
            score: detectionScore,
        };
    }

    return {
        isLive: true,
        score: detectionScore,
    };
}

function analyzeImageQuality(videoElement) {
    // Create temporary canvas to analyze image
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;
    ctx.drawImage(videoElement, 0, 0);

    // Get image data
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    // Calculate brightness and contrast
    let totalBrightness = 0;
    let pixelCount = 0;

    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const brightness = (r + g + b) / 3;
        totalBrightness += brightness;
        pixelCount++;
    }

    const avgBrightness = totalBrightness / pixelCount;

    // Calculate variance for contrast
    let variance = 0;
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const brightness = (r + g + b) / 3;
        variance += Math.pow(brightness - avgBrightness, 2);
    }
    const contrast = Math.sqrt(variance / pixelCount);

    // Detect moiré pattern (common in photos of screens)
    const hasScreenPattern = detectMoirePattern(imageData);

    // Detect blur (photos/videos from screen are usually blurrier)
    const blurScore = detectBlur(imageData);

    // Detect color uniformity (screen photos have less natural color variation)
    const colorScore = detectColorUniformity(data);

    // Detect texture (real faces have more natural texture)
    const textureScore = detectTexture(imageData);

    // More strict validation
    const isLive =
        !hasScreenPattern &&
        contrast > 40 &&
        avgBrightness > 60 &&
        avgBrightness < 190 &&
        blurScore > 100 &&
        colorScore > 0.15 &&
        textureScore > 50;

    return {
        brightness: avgBrightness,
        contrast: contrast,
        hasScreenPattern: hasScreenPattern,
        blurScore: blurScore,
        colorScore: colorScore,
        textureScore: textureScore,
        isLive: isLive,
        reason: !isLive
            ? getFailureReason(
                  hasScreenPattern,
                  contrast,
                  avgBrightness,
                  blurScore,
                  colorScore,
                  textureScore
              )
            : null,
    };
}

function getFailureReason(
    hasPattern,
    contrast,
    brightness,
    blur,
    color,
    texture
) {
    if (hasPattern) return "Terdeteksi pola layar (moiré pattern)";
    if (contrast <= 40) return "Kontras terlalu rendah (foto/video)";
    if (brightness <= 60 || brightness >= 190)
        return "Pencahayaan tidak natural";
    if (blur <= 100) return "Gambar terlalu blur (foto dari layar)";
    if (color <= 0.15) return "Variasi warna tidak natural";
    if (texture <= 50) return "Tekstur wajah tidak natural";
    return "Gagal validasi liveness";
}

function detectMoirePattern(imageData) {
    // Simple moiré pattern detection using frequency analysis
    // Photos of screens often have repetitive patterns
    const data = imageData.data;
    const width = imageData.width;
    const height = imageData.height;

    // Sample a small region in the center
    const centerX = Math.floor(width / 2);
    const centerY = Math.floor(height / 2);
    const sampleSize = 50;

    let patternScore = 0;
    let prevBrightness = 0;
    let changes = 0;

    for (let y = centerY - sampleSize; y < centerY + sampleSize; y++) {
        for (let x = centerX - sampleSize; x < centerX + sampleSize; x++) {
            const i = (y * width + x) * 4;
            const brightness = (data[i] + data[i + 1] + data[i + 2]) / 3;

            if (Math.abs(brightness - prevBrightness) > 20) {
                changes++;
            }
            prevBrightness = brightness;
        }
    }

    // High frequency changes might indicate screen pattern
    const changeRate = changes / (sampleSize * sampleSize * 4);
    return changeRate > 0.25; // Stricter threshold
}

// Detect blur using Laplacian variance
function detectBlur(imageData) {
    const data = imageData.data;
    const width = imageData.width;
    const height = imageData.height;

    // Convert to grayscale and calculate Laplacian
    let laplacianSum = 0;
    let count = 0;

    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            const idx = (y * width + x) * 4;

            // Get grayscale value
            const center = (data[idx] + data[idx + 1] + data[idx + 2]) / 3;

            // Get neighbors
            const top =
                (data[((y - 1) * width + x) * 4] +
                    data[((y - 1) * width + x) * 4 + 1] +
                    data[((y - 1) * width + x) * 4 + 2]) /
                3;
            const bottom =
                (data[((y + 1) * width + x) * 4] +
                    data[((y + 1) * width + x) * 4 + 1] +
                    data[((y + 1) * width + x) * 4 + 2]) /
                3;
            const left =
                (data[(y * width + (x - 1)) * 4] +
                    data[(y * width + (x - 1)) * 4 + 1] +
                    data[(y * width + (x - 1)) * 4 + 2]) /
                3;
            const right =
                (data[(y * width + (x + 1)) * 4] +
                    data[(y * width + (x + 1)) * 4 + 1] +
                    data[(y * width + (x + 1)) * 4 + 2]) /
                3;

            // Laplacian operator
            const laplacian = Math.abs(
                4 * center - top - bottom - left - right
            );
            laplacianSum += laplacian * laplacian;
            count++;
        }
    }

    // Higher variance = sharper image (real face)
    // Lower variance = blurrier image (photo from screen)
    return laplacianSum / count;
}

// Detect color uniformity
function detectColorUniformity(data) {
    let rVariance = 0,
        gVariance = 0,
        bVariance = 0;
    let rSum = 0,
        gSum = 0,
        bSum = 0;
    const pixelCount = data.length / 4;

    // Calculate mean
    for (let i = 0; i < data.length; i += 4) {
        rSum += data[i];
        gSum += data[i + 1];
        bSum += data[i + 2];
    }

    const rMean = rSum / pixelCount;
    const gMean = gSum / pixelCount;
    const bMean = bSum / pixelCount;

    // Calculate variance
    for (let i = 0; i < data.length; i += 4) {
        rVariance += Math.pow(data[i] - rMean, 2);
        gVariance += Math.pow(data[i + 1] - gMean, 2);
        bVariance += Math.pow(data[i + 2] - bMean, 2);
    }

    const totalVariance =
        (rVariance + gVariance + bVariance) / (pixelCount * 3);

    // Real faces have more color variation
    // Screen photos have more uniform colors
    return Math.sqrt(totalVariance) / 255; // Normalize to 0-1
}

// Detect texture using edge density
function detectTexture(imageData) {
    const data = imageData.data;
    const width = imageData.width;
    const height = imageData.height;

    let edgeCount = 0;
    const threshold = 30;

    // Simple edge detection (Sobel-like)
    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            const idx = (y * width + x) * 4;
            const gray = (data[idx] + data[idx + 1] + data[idx + 2]) / 3;

            const grayRight =
                (data[(y * width + (x + 1)) * 4] +
                    data[(y * width + (x + 1)) * 4 + 1] +
                    data[(y * width + (x + 1)) * 4 + 2]) /
                3;
            const grayBottom =
                (data[((y + 1) * width + x) * 4] +
                    data[((y + 1) * width + x) * 4 + 1] +
                    data[((y + 1) * width + x) * 4 + 2]) /
                3;

            const gradientX = Math.abs(grayRight - gray);
            const gradientY = Math.abs(grayBottom - gray);
            const gradient = Math.sqrt(
                gradientX * gradientX + gradientY * gradientY
            );

            if (gradient > threshold) {
                edgeCount++;
            }
        }
    }

    // Real faces have more natural texture/edges
    // Screen photos have less texture detail
    return (edgeCount / (width * height)) * 10000;
}

// Detect motion between frames (real person moves slightly, photo doesn't)
function detectMotion(currentLandmarks) {
    if (!currentLandmarks || !currentLandmarks.positions)
        return { hasMotion: true, score: 0 };

    landmarkHistory.value.push(currentLandmarks.positions);

    if (landmarkHistory.value.length > MAX_FRAME_HISTORY) {
        landmarkHistory.value.shift();
    }

    if (landmarkHistory.value.length < 3) {
        return { hasMotion: true, score: 0 }; // Not enough data yet
    }

    // Calculate movement between frames (only use key points for better accuracy)
    let totalMovement = 0;
    const keyPoints = [30, 33, 36, 39, 45, 48]; // Nose tip, eye corners, mouth corners

    for (let i = 1; i < landmarkHistory.value.length; i++) {
        const prev = landmarkHistory.value[i - 1];
        const curr = landmarkHistory.value[i];

        let frameMovement = 0;

        for (const pointIdx of keyPoints) {
            if (pointIdx < prev.length && pointIdx < curr.length) {
                const dx = curr[pointIdx].x - prev[pointIdx].x;
                const dy = curr[pointIdx].y - prev[pointIdx].y;
                frameMovement += Math.sqrt(dx * dx + dy * dy);
            }
        }
        totalMovement += frameMovement / keyPoints.length; // Average per key point
    }

    const avgMovement = totalMovement / (landmarkHistory.value.length - 1);

    // More relaxed thresholds for real person detection
    // Real person: natural micro-movements (0.3-50 pixels average)
    // Photo static: almost no movement (< 0.3)
    // Video/rapid movement: too much (> 50)
    const hasNaturalMotion = avgMovement > 0.3 && avgMovement < 50;

    return {
        hasMotion: hasNaturalMotion,
        score: avgMovement,
        reason:
            avgMovement <= 0.3
                ? "Tidak ada gerakan (foto statis)"
                : avgMovement >= 50
                ? "Gerakan tidak natural (terlalu cepat)"
                : null,
    };
}

// Sound effects
const audioCache = new Map();
async function playAudio(src) {
    try {
        let audio = audioCache.get(src);
        if (!audio) {
            audio = new Audio(src);
            audio.preload = "auto";
            audioCache.set(src, audio);
        }
        audio.currentTime = 0;
        await audio.play();
    } catch (e) {
        throw e;
    }
}

async function playSuccessSound() {
    try {
        // Try play success mp3 if available
        await playAudio("/audio/Berhasil Absen.mp3");
    } catch (e) {
        // Fallback to synthesized tone
        try {
            const audioContext = new AudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(
                523.25,
                audioContext.currentTime
            );
            oscillator.frequency.setValueAtTime(
                659.25,
                audioContext.currentTime + 0.1
            );
            oscillator.frequency.setValueAtTime(
                783.99,
                audioContext.currentTime + 0.2
            );

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                audioContext.currentTime + 0.5
            );

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e2) {
            console.log("Could not play success sound:", e2);
        }
    }
}

async function playBelumMemilikiShift() {
    try {
        await playAudio("/audio/Belum Memiliki Shift.mp3");
    } catch (e) {
        // Fallback to synthesized tone
        try {
            const audioContext = new AudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(
                370,
                audioContext.currentTime + 0.15
            );

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                audioContext.currentTime + 0.3
            );

            oscillator.type = "square";
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e2) {
            console.log("Could not play error sound:", e2);
        }
    }
}

async function playSudahAbsen() {
    try {
        await playAudio("/audio/Sudah Absen.mp3");
    } catch (e) {
        // Fallback to synthesized tone
        try {
            const audioContext = new AudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(
                370,
                audioContext.currentTime + 0.15
            );

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                audioContext.currentTime + 0.3
            );

            oscillator.type = "square";
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e2) {
            console.log("Could not play error sound:", e2);
        }
    }
}

async function playErrorSound() {
    try {
        await playAudio("/audio/Gagal Absen.mp3");
    } catch (e) {
        // Fallback to synthesized tone
        try {
            const audioContext = new AudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(
                370,
                audioContext.currentTime + 0.15
            );

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                audioContext.currentTime + 0.3
            );

            oscillator.type = "square";
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e2) {
            console.log("Could not play error sound:", e2);
        }
    }
}
async function playCompletedAttendance() {
    try {
        await playAudio("/audio/Absen Lenkap.mp3");
    } catch (e) {
        // Fallback to synthesized tone
        try {
            const audioContext = new AudioContext();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(
                370,
                audioContext.currentTime + 0.15
            );

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(
                0.01,
                audioContext.currentTime + 0.3
            );

            oscillator.type = "square";
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e2) {
            console.log("Could not play error sound:", e2);
        }
    }
}

// Watch for notifyData changes to play sounds
watch(
    () => notifyData.value.type,
    (newType) => {
        if (newType == 1) {
            playSuccessSound();
        } else if (newType == 3) {
            playSudahAbsen();
        } else if (newType == 4) {
            playErrorSound();
        } else if (newType == 2) {
            playBelumMemilikiShift();
        } else if (newType == 5) {
            playCompletedAttendance();
        }
    }
);

function clearNotices() {
    notifyData.value = { message: "", data: null, type: null };
}

const running = ref(false);
const statusText = ref("Idle");
const error = ref("");

// Ambil URL model dari variabel environment jika tersedia, fallback ke default
const modelsUrl = import.meta.env.VITE_MODELS_URL || "/public/models";

const inputSize = ref(416); // Increased from 320 for better accuracy
const scoreThreshold = ref(0.6); // Increased from 0.5 for better detection quality
const matchThreshold = ref(0.65); // Increased from 0.6 to be more lenient (lower = stricter)
const relaxedMatchThreshold = ref(0.75); // Fallback threshold for more lenient matching

let stream = null;
let detectTimer = null;
let faceMatcher = null;
let modelsLoaded = false;

function getOptions() {
    return new faceapi.TinyFaceDetectorOptions({
        inputSize: inputSize.value,
        scoreThreshold: scoreThreshold.value,
    });
}

async function ensureBackend() {
    try {
        console.log("Attempting to set WebGL backend...");
        await tf.setBackend("webgl");
        await tf.ready();
        console.log("WebGL backend initialized successfully");
    } catch (error) {
        console.log("WebGL backend failed, falling back to WASM:", error);
        await tf.setBackend("wasm");
        await tf.ready();
        console.log("WASM backend initialized successfully");
    }
}

async function loadModelsOnce() {
    if (modelsLoaded) return;
    console.log("Starting to load face recognition models...");
    statusText.value = "Loading models...";

    try {
        await ensureBackend();
        console.log("Backend initialized, loading models from:", modelsUrl);

        const testUrl = `${modelsUrl}/tiny_face_detector_model-weights_manifest.json`;

        const response = await fetch(testUrl);
        if (!response.ok) {
            throw new Error(
                `Failed to fetch model manifest: ${response.status} ${response.statusText}`
            );
        }
        // Test individual model loading
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelsUrl);
        await faceapi.nets.faceLandmark68Net.loadFromUri(modelsUrl);
        await faceapi.nets.faceRecognitionNet.loadFromUri(modelsUrl);
        modelsLoaded = true;
        statusText.value = "Models loaded successfully";
    } catch (error) {
        console.error("Failed to load models:", error);
        console.error("Error details:", {
            message: error.message,
            stack: error.stack,
            modelsUrl: modelsUrl,
        });
        statusText.value = `Failed to load models: ${error.message}`;
        throw error;
    }
}

function resizeCanvas() {
    const video = videoRef.value;
    const canvas = canvasRef.value;
    if (!video || !canvas) return;
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
}

function clearCanvas() {
    const c = canvasRef.value;
    if (!c) return;
    const ctx = c.getContext("2d");
    ctx.clearRect(0, 0, c.width, c.height);
}

const NOTIFY_COOLDOWN_MS = 10_000;
const lastNotifiedAt = new Map();

function shouldNotifyNow(id) {
    const now = Date.now();
    const last = lastNotifiedAt.get(id) || 0;
    if (now - last < NOTIFY_COOLDOWN_MS) return false;
    lastNotifiedAt.set(id, now);
    return true;
}
const recognitionEnabled = ref(false);

async function encodeAllEmployees() {
    const options = getOptions();
    const labeled = [];
    const employees = Array.isArray(props.employees) ? props.employees : [];

    let idx = 0;
    for (const emp of employees) {
        idx++;
        statusText.value = `Encoding ${idx}/${employees.length}: ${
            emp?.label ?? "-"
        }`;
        if (!emp?.photo_url) {
            console.warn(
                `No photo for ${emp?.label ?? "(no label)"}, skipping.`
            );
            continue;
        }
        try {
            const img = await faceapi.fetchImage(emp.photo_url);
            console.log(emp.photo_url);

            // Try to detect face with current options
            let desc = await faceapi
                .detectSingleFace(img, options)
                .withFaceLandmarks()
                .withFaceDescriptor();

            // If detection fails, try with more lenient options
            if (!desc) {
                console.warn(`Primary detection failed for ${emp.label}, trying with relaxed options...`);
                const relaxedOptions = new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320, // Smaller input for better detection on low-quality images
                    scoreThreshold: 0.4, // Lower threshold to catch more faces
                });
                desc = await faceapi
                    .detectSingleFace(img, relaxedOptions)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
            }

            if (!desc) {
                console.warn(`No face detected for ${emp.label} at ${emp.photo_url}`);
                continue;
            }

            // Log detection quality for debugging
            console.log(`Encoded ${emp.label}: score=${desc.detection.score.toFixed(3)}`);

            labeled.push(
                new faceapi.LabeledFaceDescriptors(emp.label, [desc.descriptor])
            );
            if (img && img.remove) img.remove();
        } catch (e) {
            console.warn(`Failed ${emp?.label ?? "(no label)"}:`, e);
        }
    }

    if (labeled.length > 0) {
        faceMatcher = new faceapi.FaceMatcher(labeled, matchThreshold.value);
        statusText.value = `Loaded ${labeled.length} face template(s)`;
        recognitionEnabled.value = true;
    } else {
        faceMatcher = null;
        recognitionEnabled.value = false;
        statusText.value = "No face templates found. Detection only mode.";
        console.warn("No descriptors created; running in detection-only mode.");
    }
}

let detecting = false;

async function detect() {
    if (!running.value || !videoRef.value || !canvasRef.value) return;
    if (detecting) return;
    detecting = true;

    try {
        const hasMatcher = !!faceMatcher;
        const options = getOptions();
        const chain = faceapi
            .detectAllFaces(videoRef.value, options)
            .withFaceLandmarks();
        const results = hasMatcher
            ? await chain.withFaceDescriptors()
            : await chain;

        const viewSize = {
            width: videoRef.value.videoWidth || canvasRef.value.width,
            height: videoRef.value.videoHeight || canvasRef.value.height,
        };
        const resized = faceapi.resizeResults(results, viewSize);

        const ctx = canvasRef.value.getContext("2d");
        ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);

        let photoBlob = null;
        try {
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");
            canvas.width = videoRef.value.videoWidth;
            canvas.height = videoRef.value.videoHeight;
            context.drawImage(
                videoRef.value,
                0,
                0,
                canvas.width,
                canvas.height
            );
            photoBlob = await new Promise((resolve) =>
                canvas.toBlob(resolve, "image/jpeg", 0.8)
            );
        } catch (e) {
            console.error("Failed to capture photo:", e);
        }

        for (const r of resized) {
            let label = "Unknown";
            let distance = 1;
            let matchQuality = "none";

            if (hasMatcher && r.descriptor) {
                const best = faceMatcher.findBestMatch(r.descriptor);
                label = best.label === "unknown" ? "Unknown" : best.label;
                distance = best.distance;

                // Determine match quality
                if (distance <= matchThreshold.value) {
                    matchQuality = "high";
                } else if (distance <= relaxedMatchThreshold.value) {
                    matchQuality = "medium";
                } else {
                    matchQuality = "low";
                }

                // Log matching details for debugging
                if (best.label !== "unknown") {
                    console.log(`Match: ${best.label}, distance=${distance.toFixed(3)}, quality=${matchQuality}, detection_score=${r.detection.score.toFixed(3)}`);
                }
            }

            // Anti-spoofing checks
            const livenessCheck = detectLivenessQuality(r);
            const imageQuality = analyzeImageQuality(videoRef.value);
            const motionCheck = ENABLE_MOTION_CHECK
                ? detectMotion(r.landmarks)
                : { hasMotion: true, score: 0 };

            // Check if this is a real person (not photo from screen)
            const failedLiveness = !livenessCheck.isLive;
            const failedQuality = !imageQuality.isLive;
            const failedMotion = ENABLE_MOTION_CHECK && !motionCheck.hasMotion;

            if (failedLiveness || failedQuality || failedMotion) {
                // Determine failure reason
                let failureReason = "⚠️ Terdeteksi Pemalsuan";
                if (failedLiveness) {
                    failureReason = `⚠️ ${livenessCheck.reason}`;
                } else if (failedQuality) {
                    failureReason = `⚠️ ${imageQuality.reason}`;
                } else if (failedMotion) {
                    failureReason = `⚠️ ${motionCheck.reason}`;
                }

                // Draw warning box for suspected spoofing
                new faceapi.draw.DrawBox(r.detection.box, {
                    label: failureReason,
                    boxColor: "#ef4444", // Red
                }).draw(canvasRef.value);

                console.warn("Anti-spoofing triggered:", {
                    liveness: livenessCheck,
                    quality: imageQuality,
                    motion: motionCheck,
                });
                continue; // Skip this detection
            }

            // Calculate stability progress
            let stabilityProgress = 0;
            let boxColor = "#0ea5e9"; // Default blue

            // Accept matches with standard threshold OR relaxed threshold for stability tracking
            const isMatchForStability = hasMatcher &&
                label !== "Unknown" &&
                (distance <= matchThreshold.value || distance <= relaxedMatchThreshold.value);

            if (isMatchForStability) {
                const [idPart] = String(label).split("|");
                const employeeId = idPart?.trim();

                if (
                    employeeId === stableDetection.value.employeeId &&
                    stableDetection.value.startTime
                ) {
                    const elapsed =
                        Date.now() - stableDetection.value.startTime;
                    stabilityProgress = Math.min(
                        (elapsed / STABILITY_DELAY) * 100,
                        100
                    );

                    if (stabilityProgress >= 100) {
                        boxColor = "#10b981"; // Green when stable
                    } else {
                        boxColor = "#f59e0b"; // Orange while waiting
                    }
                }
            }

            // Show match quality in label
            const matchIndicator = matchQuality === "high" ? "✓" : matchQuality === "medium" ? "~" : "";
            new faceapi.draw.DrawBox(r.detection.box, {
                label: `${label}${
                    hasMatcher ? ` ${matchIndicator} (${distance.toFixed(2)})` : ""
                }${
                    stabilityProgress > 0
                        ? ` - ${Math.round(stabilityProgress)}%`
                        : ""
                }`,
                boxColor: boxColor,
            }).draw(canvasRef.value);

            // Accept matches with standard threshold OR relaxed threshold
            const isMatchForHistory = hasMatcher &&
                label !== "Unknown" &&
                (distance <= matchThreshold.value || distance <= relaxedMatchThreshold.value);

            if (isMatchForHistory) {
                const [idPart] = String(label).split("|");
                const employeeId = idPart?.trim();

                if (employeeId) {
                    // Add to detection history
                    detectionHistory.value.push(employeeId);

                    // Keep only last MAX_HISTORY_SIZE detections
                    if (detectionHistory.value.length > MAX_HISTORY_SIZE) {
                        detectionHistory.value.shift();
                    }

                    // Get most consistent employee from history
                    const consistentEmployee = getMostConsistentEmployee();

                    // Check if we have a consistent detection
                    if (consistentEmployee) {
                        // Check if this is the same person being tracked
                        if (
                            stableDetection.value.employeeId ===
                            consistentEmployee
                        ) {
                            // Same person, check if stable for 2 seconds
                            const now = Date.now();
                            const elapsedTime =
                                now - stableDetection.value.startTime;

                            if (
                                elapsedTime >= STABILITY_DELAY &&
                                !stableDetection.value.isStable
                            ) {
                                // Face has been stable for 2 seconds, process it
                                stableDetection.value.isStable = true;

                                if (shouldNotifyNow(consistentEmployee)) {
                                    try {
                                        const res = await notifyHit({
                                            employeeId: consistentEmployee,
                                            distance,
                                            photo: photoBlob,
                                        });
                                        notifyData.value = {
                                            message: res.message,
                                            data: res?.data || null,
                                            type: res?.type || "error",
                                        };

                                        // Clear history after successful processing
                                        detectionHistory.value = [];
                                    } catch (e) {
                                        notifyData.value = {
                                            message: String(e?.message || e),
                                            data: null,
                                            type: "error",
                                        };
                                    }
                                }
                            }
                        } else {
                            // New consistent person detected, reset stability tracking
                            if (stabilityTimer) {
                                clearTimeout(stabilityTimer);
                            }

                            stableDetection.value = {
                                employeeId: consistentEmployee,
                                distance: distance,
                                startTime: Date.now(),
                                isStable: false,
                            };

                            // Set timer to mark as stable after delay
                            stabilityTimer = setTimeout(() => {
                                // Timer will be checked in next detection cycle
                            }, STABILITY_DELAY);
                        }
                    }
                }
            } else {
                // No valid face detected, reset stability and clear history
                if (stabilityTimer) {
                    clearTimeout(stabilityTimer);
                    stabilityTimer = null;
                }
                stableDetection.value = {
                    employeeId: null,
                    distance: null,
                    startTime: null,
                    isStable: false,
                };
                detectionHistory.value = [];
            }
        }
    } finally {
        detecting = false;
    }
}

function stopCountdown() {
    if (timerId !== null) {
        clearInterval(timerId);
        timerId = null;
    }
}

function startCountdown() {
    stopCountdown();
    clearCountdownForm;
    timerId = window.setInterval(() => {
        if (clearCountdownForm.value > 1) {
            clearCountdownForm.value--;
        } else {
            stopCountdown();
            showAbsenceStatuses = false;
            //   window.location.reload()
        }
    }, 1000);
}

async function start() {
    try {
        error.value = "";
        clearNotices();
        await loadModelsOnce();

        statusText.value = "Encoding employees...";
        await encodeAllEmployees();

        statusText.value = "Starting camera...";

        // Check if getUserMedia is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error("getUserMedia is not supported in this browser");
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "user" },
                audio: false,
            });
        } catch (mediaError) {
            console.error("getUserMedia error:", mediaError);
            let errorMessage = "Failed to access camera: ";

            if (mediaError.name === "NotAllowedError") {
                errorMessage += "Camera permission denied";
            } else if (mediaError.name === "NotFoundError") {
                errorMessage += "No camera device found";
            } else if (mediaError.name === "NotReadableError") {
                errorMessage +=
                    "Camera is already in use by another application";
            } else if (mediaError.name === "OverconstrainedError") {
                errorMessage += "Camera constraints cannot be satisfied";
            } else {
                errorMessage += mediaError.message || "Unknown error";
            }

            throw new Error(errorMessage);
        }

        const video = videoRef.value;
        video.srcObject = stream;
        await new Promise((resolve, reject) => {
            const onReady = () => {
                video.removeEventListener("canplay", onReady);
                resolve();
            };
            video.addEventListener("canplay", onReady, { once: true });
            video.play().catch(reject);
        });
        resizeCanvas();

        running.value = true;
        statusText.value = recognitionEnabled.value
            ? "Running (recognition ON)"
            : "Running (detection only)";
        detectTimer = setInterval(detect, 150);
    } catch (e) {
        console.error("Start function error:", e);
        error.value = String(e?.message || e);
        stop();
    }
}

function stop() {
    stopCountdown();
    running.value = false;
    statusText.value = "Stopped";
    if (detectTimer) {
        clearInterval(detectTimer);
        detectTimer = null;
    }
    if (stream) {
        stream.getTracks().forEach((t) => t.stop());
        stream = null;
    }
    clearCanvas();
}

function getCsrf() {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || ""
    );
}

const clearCountdownForm = ref(7);
let timerId = null;

/**
 * Check liveness using MXFace API
 * @param {Blob} photoBlob - Photo blob to check
 * @returns {Promise<{isLive: boolean, score: number, message: string}>}
 */
async function checkLivenessAPI(photoBlob) {
    try {
        // Convert blob to base64
        const base64Image = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => {
                // Remove data:image/jpeg;base64, prefix
                const base64 = reader.result.split(',')[1];
                resolve(base64);
            };
            reader.onerror = reject;
            reader.readAsDataURL(photoBlob);
        });

        console.log('Calling liveness detection API...');

        // Call MXFace API
        const response = await fetch('https://faceapi.mxface.ai/api/v3/face/Liveness', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Subscriptionkey': import.meta.env.VITE_MXFACE_SUBSCRIPTION_KEY || ''
            },
            body: JSON.stringify({
                image: base64Image
            })
        });

        if (!response.ok) {
            console.error('Liveness API error:', response.status, response.statusText);
            return {
                isLive: false,
                score: 0,
                message: 'Gagal melakukan verifikasi liveness'
            };
        }

        const data = await response.json();
        const livenessScore = data.livenessScore || 0;
        const MIN_LIVENESS_SCORE = 70.0;

        console.log('Liveness detection result:', {
            score: livenessScore,
            threshold: MIN_LIVENESS_SCORE,
            isLive: livenessScore >= MIN_LIVENESS_SCORE
        });

        return {
            isLive: livenessScore >= MIN_LIVENESS_SCORE,
            score: livenessScore,
            message: livenessScore >= MIN_LIVENESS_SCORE
                ? 'Verifikasi liveness berhasil'
                : 'Foto tidak terdeteksi sebagai wajah asli. Silakan gunakan kamera langsung.'
        };
    } catch (error) {
        console.error('Liveness check exception:', error);
        return {
            isLive: false,
            score: 0,
            message: 'Terjadi kesalahan saat verifikasi liveness: ' + error.message
        };
    }
}

async function notifyHit({ employeeId, distance, photo }) {
    console.log("notifyHit called with:", {
        employeeId,
        distance,
        photo: photo ? "photo provided" : "no photo",
    });
    console.log("Current position:", position.value);

    try {
        // Check liveness first if photo is provided
        if (photo) {
            console.log('Checking liveness before attendance...');
            const livenessResult = await checkLivenessAPI(photo);

            if (!livenessResult.isLive) {
                console.warn('Liveness check failed:', livenessResult);

                // Return error response without calling backend
                return {
                    ok: false,
                    status: 400,
                    message: `${livenessResult.message} (Score: ${livenessResult.score.toFixed(2)})`,
                    type: 4, // REJECTED type
                    data: {
                        employee: { id: employeeId },
                        status: 'DITOLAK',
                        type: 4
                    }
                };
            }

            console.log('Liveness check passed:', livenessResult);
        }

        const fd = new FormData();
        fd.append("employee_id", String(employeeId));
        if (position.value?.latitude != null)
            fd.append("latitude", String(position.value.latitude));
        if (position.value?.longitude != null)
            fd.append("longitude", String(position.value.longitude));
        if (distance != null) fd.append("distance", String(distance));
        if (photo) fd.append("photo", photo, "absen.jpg");

        console.log("FormData prepared:", {
            employee_id: employeeId,
            latitude: position.value?.latitude,
            longitude: position.value?.longitude,
            distance: distance,
            hasPhoto: !!photo,
        });

        const urlAttendance = route("attendance.employee", {
            employee_id: employeeId,
        });
        console.log("Request URL:", urlAttendance);

        const res = await fetch(urlAttendance, {
            method: "POST",
            credentials: "include",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": getCsrf(),
            },
            body: fd,
        });

        console.log("Response status:", res.status);
        console.log("Response ok:", res.ok);

        const raw = await res.text();
        console.log("Raw response:", raw);

        let body;
        try {
            body = JSON.parse(raw);
            console.log("Parsed response body:", body);
        } catch {
            console.warn("Failed to parse response as JSON, using raw text");
            body = { message: raw };
        }

        const typeFromServer = body?.data?.type;
        const employee = body?.data?.employee ?? null;
        showAbsenceStatuses = true;

        if (!res.ok) {
            const errorResult = {
                ok: false,
                status: res.status,
                message: body?.message || `Gagal absensi (HTTP ${res.status})`,
                type: typeFromServer ?? "error",
                data: { employee },
            };
            console.error("Request failed:", errorResult);
            return errorResult;
        }

        const dataPayload = body?.data ?? null;
        const successResult = {
            ok: true,
            status: res.status,
            message: body?.message || "Absensi berhasil",
            type: typeFromServer ?? "success",
            data: dataPayload,
        };
        console.log("Request successful:", successResult);
        return successResult;
    } catch (error) {
        const errorResult = {
            ok: false,
            status: 0,
            message: "Tidak bisa terhubung ke server.",
            type: "error",
            data: null,
        };
        console.error("Exception in notifyHit:", error);
        console.error("Returning error result:", errorResult);
        return errorResult;
    }
}

function getMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                position.value.latitude = Number(
                    pos.coords.latitude.toFixed(8)
                );
                position.value.longitude = Number(
                    pos.coords.longitude.toFixed(8)
                );
            },
            () => {
                alert("Unable to retrieve your location.");
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

onMounted(() => {
    loadModelsOnce().catch((e) => {
        error.value = String(e?.message || e);
    });
    getMyLocation();

    // Initialize Pusher
    const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY || "", {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || "ap1",
        encrypted: true,
    });

    // Subscribe to attendance channel
    const channel = pusher.subscribe("attendance-channel");

    // Listen for attendance events
    channel.bind("attendance-barcode-event", function (data) {
        console.log("Attendance event received:", data);

        // Show the same detailed modal as face recognition
        notifyData.value = data;

        showAbsenceStatuses.value = true;
        // startCountdown();

        // Also show the alert notification
        // showAttendanceAlert(data);

        generateAttendanceUrl();
    });

    // Listen for error events
    // channel.bind('attendance-error-event', function (data) {
    //     console.log('Attendance error event received:', data);
    //     notifyData.value = {
    //         message: data.message,
    //         data: {
    //             employee: {
    //                 name: data.details.employee_name,
    //                 nik: '',
    //                 department: {
    //                     name: ''
    //                 },
    //                 role: {
    //                     name: ''
    //                 }
    //             },
    //             dateTime: data.details.time || '',
    //             status: data.details.status || '',
    //             type: data.type
    //         },
    //         type: 'error'
    //     };

    //     showAbsenceStatuses.value = true;

    //     generateAttendanceUrl();
    // });
});

onBeforeUnmount(() => {
    stop();
    // Clear stability timer
    if (stabilityTimer) {
        clearTimeout(stabilityTimer);
        stabilityTimer = null;
    }
});

// Alert state for attendance notifications
const attendanceAlerts = ref([]);

function showAttendanceAlert(data) {
    const alert = {
        id: Date.now(),
        employeeName: data.employee_name,
        type: data.attendance_type,
        status: data.status,
        time: data.time,
        timestamp: data.timestamp,
        variant: data.attendance_type === "masuk" ? "success" : "info",
    };
    attendanceAlerts.value.push(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        removeAlert(alert.id);
    }, 5000);
}

function showAttendanceError(data) {
    const alert = {
        id: Date.now(),
        message: data.message,
        details: data.details,
        timestamp: data.timestamp,
        variant: "error",
    };
    attendanceAlerts.value.push(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        removeAlert(alert.id);
    }, 5000);
}

function removeAlert(id) {
    attendanceAlerts.value = attendanceAlerts.value.filter(
        (alert) => alert.id !== id
    );
}
</script>

<template>
    <!-- Pusher Alert Display -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="alert in attendanceAlerts"
            :key="alert.id"
            :class="{
                'bg-green-500': alert.variant === 'success',
                'bg-blue-500': alert.variant === 'info',
                'bg-red-500': alert.variant === 'error',
            }"
            class="px-6 py-4 max-w-md text-white rounded-lg shadow-lg"
        >
            <div v-if="alert.employeeName" class="flex items-center">
                <svg
                    v-if="alert.type === 'masuk'"
                    class="mr-3 w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <svg
                    v-else-if="alert.type === 'keluar'"
                    class="mr-3 w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <svg
                    v-else
                    class="mr-3 w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <div class="flex-1">
                    <p class="font-semibold">
                        {{ alert.employeeName }}
                        {{ alert.type === "masuk" ? "masuk" : "keluar" }}
                    </p>
                    <p class="text-sm opacity-90">{{ alert.time }}</p>
                    <p v-if="alert.status" class="text-xs opacity-75">
                        {{ alert.status }}
                    </p>
                </div>
                <button
                    @click="removeAlert(alert.id)"
                    class="ml-4 text-white hover:text-gray-200"
                >
                    <svg
                        class="w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
            </div>
            <div v-else class="flex items-center">
                <svg
                    class="mr-3 w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <p class="font-semibold">{{ alert.message }}</p>
            </div>
        </div>
    </div>

    <div class="flex overflow-hidden h-screen">
        <div class="bg-[#112D5A] w-1/2">
            <!-- Radio Button Selection -->
            <div class="flex justify-center gap-6 pt-6 pb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        name="attendanceMode"
                        value="camera"
                        v-model="selectedOption"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                    />
                    <span class="text-white font-medium">Absen Muka</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        name="attendanceMode"
                        value="qr"
                        v-model="selectedOption"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
                    />
                    <span class="text-white font-medium">Absen QR</span>
                </label>
            </div>

            <div class="flex flex-col justify-center items-center h-full">
                <div
                    class="flex flex-col items-center w-3/4 text-center bg-white rounded-md shadow-md"
                    v-if="selectedOption === 'camera'"
                    :class="!isCameraOn ? 'p-24' : ''"
                >
                    <div v-if="!isCameraOn">
                        <svg
                            width="319"
                            height="320"
                            viewBox="0 0 319 320"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <g clip-path="url(#clip0_4556_66742)">
                                <path
                                    d="M75.1901 183.482C67.3091 172.565 59.3114 161.532 54.1742 149.097C49.037 136.663 46.877 122.477 50.9635 109.692C55.5169 95.3899 68.0681 83.948 82.7208 80.6788C89.3758 79.2194 97.2568 78.8691 101.343 73.3816C102.861 71.3384 103.62 68.8282 104.32 66.318C106.831 57.0943 109.691 46.4113 118.448 42.5C127.963 38.2384 138.53 44.4848 147.928 49.0382C158.67 54.2338 170.345 57.3862 182.254 58.2619C187.567 58.6705 192.938 58.6121 198.016 60.1299C203.095 61.6478 207.941 65.2088 209.4 70.2876C211.035 76.1838 207.882 82.7804 209.925 88.5014C211.56 93.0548 216.172 95.9737 220.842 97.4332C225.454 98.8926 230.416 99.3596 235.028 100.994C247.929 105.548 256.102 119.033 257.561 132.635C259.021 146.237 254.818 159.839 249.213 172.273C246.178 178.986 242.733 185.525 238.063 191.246C220.725 212.67 188.968 218.742 162.523 210.919C156.218 209.051 150.03 206.482 143.55 205.49C136.311 204.381 128.956 205.256 121.6 205.081C105.196 204.731 88.9672 198.952 75.949 188.911"
                                    fill="white"
                                />
                                <path
                                    d="M274.727 211.444C273.909 208.876 272.683 206.482 271.282 204.264C269.765 201.87 267.955 199.477 267.254 196.675C266.437 193.639 267.371 190.545 268.13 187.568C268.889 184.591 269.297 181.73 268.83 178.694C267.838 172.448 263.81 167.136 259.84 162.349C260.891 161.24 261.475 159.839 261.592 158.262C261.708 156.511 261.3 154.76 261.358 153.008C261.358 152.716 261.241 152.483 261.125 152.308C261.883 149.331 262.584 146.353 263.343 143.376C263.985 140.866 264.744 138.297 265.036 135.67C265.853 136.312 266.904 136.721 267.955 136.721C271.165 136.721 273.267 132.81 274.318 130.241C275.661 127.03 276.011 123.528 275.953 120.083C275.894 118.099 275.719 116.172 275.544 114.187C275.369 112.67 275.427 110.86 274.435 109.576C273.968 108.992 273.325 108.583 272.625 108.35C272.391 107.591 272.041 106.89 271.516 106.248C270.348 104.847 268.188 104.088 266.554 105.139C265.912 103.154 264.686 101.461 262.409 101.228C259.957 100.994 258.031 102.629 257.097 104.73C255.462 102.278 252.952 100.585 249.916 100.644C246.764 100.702 243.728 102.337 241.51 104.497C239 106.949 237.307 110.218 236.139 113.487C234.971 116.698 234.154 120.084 233.746 123.528C233.045 129.774 233.629 136.604 230.243 142.15C229.893 142.734 229.484 143.376 229.075 143.96C226.624 146.645 225.164 150.206 225.047 153.826C224.989 155.694 225.865 157.854 224.347 159.372C223.121 160.597 221.486 161.24 220.377 162.582C217.633 165.851 218.042 170.288 218.1 174.258C218.159 176.535 217.925 178.694 217.166 180.796C216.466 182.781 215.415 184.649 214.539 186.575C213.138 189.553 212.263 192.705 211.971 195.916C208.702 189.261 204.382 183.19 199.186 177.877C193.991 172.565 188.094 168.011 181.906 163.925C179.571 162.349 177.178 160.773 174.668 159.372C174.668 159.021 174.492 158.729 174.259 158.554C173.383 153.826 172.508 149.097 171.632 144.369C170.639 138.939 169.589 133.51 168.596 128.081C168.421 127.206 168.246 126.33 168.071 125.396C173.15 122.535 178.229 119.675 183.249 116.814C183.95 116.406 183.833 115.588 183.249 115.18C178.17 111.444 173.15 107.649 168.071 103.855C165.444 101.87 162.759 99.8265 160.073 97.8417C157.505 95.9152 154.994 93.8136 152.192 92.2375C150.383 91.1867 148.456 90.3694 146.471 89.9024C147.522 89.3186 148.573 88.6181 149.565 87.6256C152.367 84.7651 153.652 75.5415 147.872 70.9297C143.202 83.4808 134.387 77.5847 124.872 85.5824C117.633 91.7121 121.953 107.007 127.615 113.72C125.047 114.246 122.712 115.764 121.369 118.04C119.151 121.893 120.201 127.206 123.704 129.949C125.222 131.175 127.148 131.759 129.075 131.817C127.79 141.041 125.864 150.09 124.054 159.196C118.625 161.006 113.196 162.874 108.351 165.968C103.33 169.179 98.7185 172.915 94.3402 176.943C92.297 178.811 90.3121 180.679 88.3273 182.606C87.3349 177.877 84.5912 173.44 80.2712 171.281C80.5631 170.755 80.2128 170.171 79.7458 169.938C80.2712 167.311 80.0961 164.625 79.1037 162.057C77.9945 159.138 76.1264 156.628 74.4919 154.059C70.9892 148.572 71.5146 141.683 70.6973 135.495C70.2303 132.109 69.3546 128.723 68.1871 125.513C66.9611 122.243 65.2682 118.974 62.6996 116.581C60.4229 114.479 57.3873 112.845 54.2349 112.845C51.1993 112.845 48.689 114.538 47.1128 117.048C46.1788 114.946 44.2523 113.37 41.8005 113.604C39.5238 113.837 38.3562 115.588 37.7141 117.573C36.0795 116.522 33.9195 117.34 32.752 118.741C32.2266 119.383 31.8763 120.084 31.7012 120.901C31.0006 121.134 30.3585 121.543 29.9498 122.127C29.0158 123.411 29.0742 125.221 28.8991 126.739C28.7239 128.723 28.5488 130.65 28.5488 132.635C28.5488 136.079 28.9575 139.582 30.3585 142.792C31.4677 145.361 33.6276 149.214 36.8384 149.214C37.8892 149.214 38.94 148.805 39.7573 148.163C40.0492 150.79 40.8664 153.359 41.5086 155.869C42.2675 158.846 43.0848 161.765 43.8437 164.742C42.034 166.785 41.8589 169.938 42.3259 172.565C42.6178 174.199 43.2016 175.834 43.2016 177.469C43.2016 179.161 42.2675 180.679 41.3919 182.08C39.6405 185.058 38.5314 188.385 39.407 191.829C39.5822 192.647 39.9324 193.406 40.341 194.106C40.5162 194.456 40.7497 194.807 40.8665 195.157C41.0416 195.682 40.7497 195.741 40.3994 195.974C39.0567 196.733 37.9476 197.842 37.1303 199.127C36.313 200.411 35.846 201.87 35.6708 203.33C35.4957 204.964 35.846 206.541 36.313 208.117C36.8384 209.868 37.5389 211.678 37.6557 213.487C37.7725 215.414 37.4221 217.34 36.9551 219.267C36.1962 222.594 35.4373 225.863 35.4373 229.308C35.4373 232.343 35.9044 235.437 36.78 238.356L38.7649 238.239C38.8816 238.648 38.9984 238.998 39.1735 239.407C39.1735 239.407 104.498 257.971 139.758 246.471C175.018 234.912 228.783 203.446 273.792 225.98C275.135 226.622 277.937 221.427 274.727 211.444Z"
                                    fill="white"
                                />
                                <path
                                    d="M110.219 115.468L102.455 123.349C96.6756 129.245 87.2768 129.303 81.3807 123.524L75.6597 117.92C69.7636 112.14 69.7052 102.742 75.4845 96.8456L83.2488 88.9062"
                                    fill="#CB730B"
                                />
                                <path
                                    d="M110.262 115.455C112.365 113.317 108.03 105.646 100.581 98.3225C93.1318 90.9988 85.389 86.7951 83.2868 88.9332C81.1846 91.0714 85.5192 98.7418 92.9683 106.065C100.417 113.389 108.16 117.593 110.262 115.455Z"
                                    fill="white"
                                />
                                <path
                                    d="M29.1316 128.015C30.2408 128.482 29.7737 130.35 28.6062 129.883C25.7457 128.716 22.9435 127.315 20.3166 125.622C11.0929 119.726 3.91252 110.852 0.0596043 100.578C-0.349038 99.4103 1.46063 98.9433 1.92765 100.052C2.97845 102.796 4.26281 105.423 5.78062 107.992C11.2097 116.924 19.4409 124.104 29.1316 128.015ZM78.5189 120.66C77.0595 121.769 75.6 122.878 73.9654 123.754C72.0974 124.804 70.1126 125.388 67.9526 125.622C64.0413 126.03 59.8381 125.739 56.4522 128.074C55.4598 128.774 56.3939 130.467 57.4447 129.708C60.8306 127.315 65.2672 127.898 69.1785 127.373C73.3233 126.789 76.6508 124.454 79.8615 121.944L78.5189 120.66ZM110.918 116.106C112.261 114.705 111.911 112.02 109.809 108.342C107.941 105.015 104.906 101.22 101.228 97.6006C94.1641 90.6537 85.5242 85.2245 82.547 88.2602C79.5697 91.2374 85.1739 99.8189 92.2376 106.766C97.9002 112.37 104.672 116.982 108.583 116.982C109.576 116.982 110.335 116.69 110.918 116.106ZM99.885 99.0016C103.446 102.504 106.365 106.124 108.116 109.276C109.809 112.312 110.043 114.238 109.517 114.764C108.233 116.048 101.403 113.071 93.5802 105.365C85.7577 97.6589 82.6637 90.8872 83.948 89.6029C84.1231 89.4277 84.4734 89.311 84.8821 89.311C87.3339 89.311 93.23 92.4634 99.885 99.0016ZM91.4787 101.045C93.8138 103.322 96.0905 105.598 98.4256 107.875C99.3013 108.751 100.644 107.408 99.7683 106.532C97.4332 104.256 95.1565 101.979 92.8214 99.7022C91.9457 98.8265 90.603 100.169 91.4787 101.045ZM93.697 97.8341C96.0321 100.111 98.3089 102.388 100.644 104.664C101.52 105.54 102.862 104.197 101.987 103.322C99.6515 101.045 97.3748 98.7681 95.0397 96.4914C94.164 95.6157 92.8214 96.9584 93.697 97.8341ZM80.4453 97.9508C83.8312 103.789 87.7425 109.801 93.8721 113.071C94.9813 113.654 95.9154 112.02 94.8646 111.436C89.0268 108.342 85.349 102.563 82.1383 97.0168C81.4961 95.9076 79.8615 96.9 80.4453 97.9508Z"
                                    fill="black"
                                />
                                <path
                                    d="M218.334 78.9844L228.375 83.6546C235.847 87.0988 239.116 95.9722 235.672 103.445L232.286 110.742C228.842 118.214 219.968 121.483 212.496 118.039L202.455 113.369"
                                    fill="#015B7A"
                                />
                                <path
                                    d="M218.334 78.9815C216.524 78.1642 213.314 80.8495 210.103 85.5197L202.456 82.0171C201.58 81.6085 200.529 82.0171 200.12 82.8928L199.42 84.4106C199.011 85.2862 199.42 86.337 200.296 86.7457L207.418 89.9564C206.775 91.1824 206.075 92.4667 205.491 93.8093C205.433 93.9261 205.375 94.1012 205.316 94.218L197.435 90.657C196.56 90.2483 195.509 90.657 195.1 91.5326L194.399 93.0504C193.991 93.9261 194.399 94.9769 195.275 95.3855L203.331 99.0633C200.763 106.302 200.296 112.257 202.514 113.249C205.258 114.533 211.037 107.82 215.415 98.3628C219.735 88.964 221.078 80.2658 218.334 78.9815Z"
                                    fill="white"
                                />
                                <path
                                    d="M218.741 78.1069C216.989 77.2896 214.537 78.5739 211.794 81.7846C211.152 82.5436 210.451 83.4192 209.809 84.3533L202.862 81.2009C202.22 80.909 201.461 80.8506 200.76 81.1425C200.06 81.376 199.534 81.9014 199.243 82.5436L198.542 84.0614C198.25 84.7035 198.192 85.4624 198.484 86.163C198.717 86.8635 199.243 87.3889 199.885 87.6808L206.073 90.4829C205.606 91.3002 205.197 92.1758 204.788 93.0515L197.783 89.8991C197.141 89.6072 196.382 89.5488 195.682 89.8407C194.981 90.0742 194.456 90.5996 194.164 91.2418L193.463 92.7596C193.171 93.4018 193.113 94.1607 193.405 94.8612C193.638 95.5617 194.164 96.0871 194.806 96.379L202.103 99.7065C201.286 102.158 200.644 104.493 200.352 106.595C199.71 110.798 200.293 113.425 202.045 114.243C202.395 114.418 202.804 114.476 203.154 114.476C207.007 114.476 212.436 107.004 216.172 98.8892C220.375 89.8991 222.535 79.8582 218.741 78.1069ZM200.293 85.4624C200.235 85.2873 200.235 85.0538 200.293 84.8203L200.994 83.3025C201.111 83.0106 201.403 82.8354 201.753 82.8354C201.87 82.8354 201.986 82.8354 202.103 82.8938L213.428 88.031L212.086 91.0083L200.76 85.8711C200.527 85.8127 200.352 85.6376 200.293 85.4624ZM195.215 94.1607C195.156 93.9855 195.156 93.752 195.215 93.5185L195.915 92.0007C196.032 91.8256 196.149 91.6504 196.382 91.5921C196.499 91.5337 196.557 91.5337 196.674 91.5337C196.791 91.5337 196.907 91.5337 197.024 91.5921L208.349 96.7293L207.007 99.7065L195.682 94.5693C195.448 94.5109 195.273 94.3358 195.215 94.1607ZM214.479 98.0719C209.867 107.996 204.555 113.25 202.862 112.491C202.161 112.199 201.694 110.331 202.22 106.887C202.512 104.96 203.096 102.801 203.854 100.524L207.882 102.334L210.86 95.8536L206.54 93.8688C206.948 92.9931 207.415 92.1175 207.824 91.3002L212.961 93.6353L215.939 87.1554L211.619 85.1705C212.144 84.4116 212.728 83.7111 213.253 83.0689C215.238 80.7922 216.814 79.8582 217.631 79.8582C217.748 79.8582 217.865 79.8582 217.982 79.9166C219.616 80.6171 219.091 88.0894 214.479 98.0719ZM222.01 85.1705C222.01 83.9446 223.936 83.9446 223.936 85.1705C223.995 95.6785 219.733 106.07 212.261 113.484C211.385 114.359 210.042 113.017 210.918 112.141C218.04 105.019 222.068 95.2115 222.01 85.1705ZM234.269 108.93C233.043 108.93 233.043 107.004 234.269 107.004C237.305 107.004 239.581 109.105 241.333 111.324C243.026 113.484 244.66 116.169 247.579 116.753C248.805 116.986 248.28 118.854 247.054 118.621C244.193 118.095 242.267 115.819 240.632 113.6C239.056 111.499 237.188 108.93 234.269 108.93ZM275.425 120.722C274.199 120.781 274.199 118.854 275.425 118.796C278.928 118.562 282.372 117.862 285.7 116.811C297.9 112.9 307.883 104.435 317.34 96.1455C318.274 95.3282 319.617 96.6709 318.683 97.4882C315.822 99.9984 313.02 102.45 310.043 104.844C300.119 112.9 288.56 119.905 275.425 120.722Z"
                                    fill="black"
                                />
                                <path
                                    d="M136.663 284.003C136.663 288.79 132.81 292.643 128.023 292.643C123.236 292.643 119.383 288.79 119.383 284.003C119.383 279.216 123.236 275.363 128.023 275.363C132.81 275.305 136.663 279.216 136.663 284.003ZM150.907 275.305C146.12 275.305 142.267 279.158 142.267 283.945C142.267 288.732 146.12 292.584 150.907 292.584C155.694 292.584 159.546 288.732 159.546 283.945C159.546 279.216 155.694 275.305 150.907 275.305ZM150.907 275.305C146.12 275.305 142.267 279.158 142.267 283.945C142.267 288.732 146.12 292.584 150.907 292.584C155.694 292.584 159.546 288.732 159.546 283.945C159.546 279.216 155.694 275.305 150.907 275.305ZM175.367 275.305C170.58 275.305 166.727 279.158 166.727 283.945C166.727 288.732 170.58 292.584 175.367 292.584C180.154 292.584 184.007 288.732 184.007 283.945C184.007 279.216 180.154 275.305 175.367 275.305Z"
                                    fill="white"
                                />
                                <path
                                    d="M158.262 136.25C158.612 136.6 158.612 137.242 158.262 137.593C157.853 137.943 157.503 138.352 157.153 138.76C157.095 138.819 157.094 138.877 157.036 138.877C157.036 138.877 157.036 138.935 156.978 138.935C156.919 139.052 156.803 139.169 156.744 139.227C156.569 139.461 156.452 139.636 156.336 139.869C156.044 140.336 155.81 140.803 155.577 141.27C155.518 141.329 155.518 141.387 155.518 141.446C155.518 141.446 155.518 141.446 155.518 141.504C155.46 141.621 155.402 141.737 155.343 141.913C155.226 142.146 155.168 142.38 155.11 142.671C154.935 143.197 154.468 143.489 153.942 143.372C153.417 143.255 153.125 142.671 153.242 142.204C153.942 139.928 155.168 137.885 156.919 136.308C157.27 135.841 157.853 135.841 158.262 136.25ZM39.2887 239.403C41.2736 245.007 44.9514 250.086 49.7383 253.53C50.7307 254.231 49.7967 255.924 48.7459 255.165C43.0249 251.02 38.8801 245.066 36.8953 238.294C36.0196 235.375 35.5526 232.281 35.5526 229.245C35.5526 225.801 36.3115 222.532 37.0704 219.204C37.5374 217.336 37.8877 215.41 37.7709 213.425C37.6542 211.557 36.9537 209.806 36.4283 208.054C35.9613 206.478 35.611 204.902 35.7861 203.267C35.9612 201.749 36.4282 200.29 37.2455 199.064C38.0628 197.78 39.172 196.671 40.5147 195.912C40.9234 195.678 41.1568 195.62 40.9817 195.094C40.8649 194.744 40.6314 194.394 40.4563 194.044C40.0476 193.343 39.7558 192.584 39.5223 191.767C38.6466 188.323 39.6974 184.995 41.5071 182.018C42.3828 180.617 43.3168 179.157 43.3168 177.406C43.3168 175.713 42.733 174.137 42.4411 172.502C41.9741 169.875 42.0908 166.723 43.9589 164.68C43.2 161.703 42.3827 158.784 41.6238 155.806C40.9817 153.296 40.1644 150.728 39.8725 148.101C39.0552 148.743 38.0044 149.151 36.9536 149.151C33.7429 149.151 31.5829 145.298 30.4737 142.73C29.1311 139.519 28.6641 136.016 28.6641 132.572C28.6641 130.587 28.8392 128.661 29.0143 126.676C29.1311 125.158 29.1311 123.349 30.0651 122.064C30.5322 121.48 31.1159 121.072 31.8165 120.838C32.05 120.079 32.3418 119.32 32.8672 118.678C34.0348 117.277 36.1947 116.46 37.8293 117.511C38.4714 115.526 39.639 113.775 41.9157 113.541C44.3676 113.308 46.2941 114.884 47.2281 116.985C48.8043 114.475 51.3145 112.782 54.3501 112.782C57.5025 112.782 60.5382 114.417 62.8149 116.518C65.3835 118.912 67.0764 122.181 68.3024 125.45C69.5283 128.661 70.4039 132.047 70.8126 135.433C71.6298 141.679 71.1044 148.509 74.6071 153.997C76.2417 156.624 78.1098 159.134 79.2189 161.994C80.2113 164.563 80.3281 167.248 79.8611 169.875C80.3281 170.109 80.6784 170.693 80.3865 171.218C84.7648 173.378 87.5086 177.815 88.4426 182.543C90.4274 180.617 92.4122 178.69 94.4554 176.881C98.8337 172.911 103.446 169.116 108.466 165.906C113.311 162.812 118.74 160.944 124.17 159.134C125.921 150.027 127.906 140.979 129.19 131.755C127.264 131.696 125.337 131.113 123.819 129.887C120.317 127.143 119.266 121.831 121.484 117.978C122.827 115.701 125.162 114.183 127.731 113.658C122.126 106.944 117.748 91.6496 124.987 85.5199C134.444 77.5222 143.317 83.4183 147.988 70.8672C153.825 75.479 152.541 84.7026 149.681 87.5631C148.688 88.5556 147.637 89.3145 146.587 89.8399C148.571 90.3069 150.498 91.1242 152.308 92.175C155.11 93.7512 157.62 95.8527 160.189 97.7792C162.874 99.764 165.501 101.749 168.186 103.792C173.207 107.587 178.227 111.381 183.364 115.117C183.948 115.526 184.065 116.402 183.364 116.752C178.285 119.612 173.265 122.531 168.186 125.333C168.361 126.209 168.536 127.085 168.712 128.019C169.704 133.448 170.755 138.877 171.747 144.306C172.623 149.035 173.499 153.763 174.374 158.492C174.608 158.667 174.841 158.959 174.783 159.309C177.235 160.71 179.628 162.286 182.022 163.862C188.21 167.949 194.106 172.502 199.301 177.815C204.497 183.127 208.817 189.198 212.086 195.853C212.378 192.643 213.254 189.49 214.655 186.513C215.53 184.586 216.581 182.718 217.282 180.734C218.041 178.574 218.274 176.472 218.216 174.195C218.157 170.226 217.749 165.789 220.492 162.52C221.602 161.177 223.236 160.535 224.462 159.309C225.98 157.85 225.104 155.631 225.163 153.763C225.221 150.144 226.68 146.583 229.191 143.897C229.599 143.314 229.95 142.671 230.358 142.088C233.744 136.542 233.16 129.712 233.861 123.465C234.269 120.079 235.087 116.635 236.254 113.424C237.422 110.155 239.115 106.886 241.625 104.434C243.843 102.274 246.879 100.64 250.031 100.581C253.067 100.523 255.577 102.216 257.212 104.668C258.146 102.566 260.014 100.932 262.524 101.165C264.801 101.34 266.027 103.092 266.669 105.076C268.303 104.026 270.463 104.784 271.631 106.186C272.156 106.828 272.507 107.528 272.74 108.287C273.441 108.462 274.083 108.871 274.55 109.513C275.484 110.797 275.484 112.607 275.659 114.125C275.893 116.11 276.068 118.036 276.068 120.021C276.126 123.465 275.717 126.968 274.433 130.179C273.382 132.747 271.281 136.659 268.07 136.659C267.019 136.659 265.968 136.25 265.151 135.608C264.859 138.235 264.1 140.803 263.458 143.314C262.699 146.291 261.999 149.268 261.24 152.245C261.415 152.421 261.532 152.596 261.473 152.946C261.415 154.697 261.824 156.449 261.707 158.2C261.648 159.776 261.065 161.177 259.956 162.286C263.925 167.073 267.953 172.386 268.946 178.632C269.413 181.668 269.004 184.528 268.245 187.505C267.486 190.483 266.552 193.577 267.369 196.612C268.128 199.414 269.88 201.808 271.398 204.201C272.857 206.478 274.024 208.813 274.842 211.382C278.111 221.423 275.893 232.981 269.062 241.037C268.245 241.972 266.902 240.629 267.72 239.695C271.398 235.375 273.616 229.887 274.258 224.283C274.783 219.263 274.141 214.125 272.098 209.514C269.938 204.552 265.443 200.407 265.151 194.744C264.976 191.709 265.968 188.79 266.669 185.871C267.311 183.01 267.486 180.208 266.727 177.406C265.385 172.21 261.999 167.774 258.613 163.746C258.554 163.746 258.496 163.804 258.438 163.804C255.577 164.797 252.542 165.088 249.564 165.088C246.587 165.088 243.552 164.913 240.633 164.505C235.671 163.862 231 161.819 227.147 158.667C226.856 159.718 226.213 160.652 225.221 161.411C223.82 162.52 222.302 163.22 221.368 164.797C219.267 168.416 220.434 172.853 220.142 176.764C219.85 181.025 217.632 184.586 215.997 188.439C214.363 192.176 213.721 196.204 213.954 200.232C214.012 200.407 214.071 200.64 214.012 200.757C214.012 201.049 214.071 201.341 214.071 201.691C214.188 202.917 212.32 202.917 212.144 201.691C212.086 201.341 212.086 200.991 212.028 200.64C211.619 199.648 211.21 198.655 210.743 197.721C207.241 190.191 202.22 183.536 196.266 177.756C191.362 172.969 185.875 168.883 180.154 165.147C178.577 164.096 176.943 163.104 175.308 162.111C175.016 162.52 174.491 162.578 174.082 162.403C173.615 164.213 173.148 166.081 172.273 167.715C170.463 171.276 166.785 173.728 162.932 174.429C158.554 175.246 154 173.903 150.439 171.276C150.089 171.043 149.972 170.693 150.031 170.342C149.739 170.342 149.447 170.401 149.097 170.401L149.038 170.459C145.652 174.079 139.873 174.487 135.261 173.32C132.518 172.619 130.649 170.868 129.19 168.533C127.672 166.139 126.446 163.571 125.279 161.002C122.185 162.053 119.032 163.045 116.055 164.33C110.684 166.606 105.897 169.992 101.402 173.67C97.024 177.231 92.9376 181.142 88.8512 185.112C88.9096 186.338 88.9096 187.564 88.7928 188.79C88.7345 189.724 88.5593 190.716 88.8512 191.592C89.0847 192.351 89.5517 192.876 90.0771 193.401C91.2447 194.452 92.5874 195.328 93.7549 196.32C94.9225 197.313 96.09 198.364 97.1408 199.473C98.6587 201.049 100.06 202.8 101.169 204.727C102.336 203.559 103.621 202.45 105.022 201.458C106.014 200.699 107.007 202.392 106.014 203.092C104.613 204.085 103.329 205.252 102.161 206.478C103.562 209.339 104.204 212.491 103.679 215.818C103.212 218.562 101.461 221.364 98.4835 221.715C97.1992 221.89 95.7398 221.539 94.8057 220.547C93.755 219.496 93.5214 217.978 93.6966 216.577C93.9301 215.001 94.689 213.483 95.5062 212.082C96.2652 210.681 97.1408 209.397 98.1332 208.171C98.6586 207.47 99.2424 206.77 99.8846 206.128C97.9581 202.625 94.8641 199.648 91.8284 197.196C90.7193 196.32 89.5517 195.561 88.5593 194.569C87.4501 193.518 86.8664 192.234 86.808 190.774C86.6912 189.373 87.0415 188.031 87.0415 186.63C87.0415 186.104 87.0415 185.579 86.9832 185.054C86.9248 184.878 86.8664 184.703 86.9248 184.528C86.8664 184.003 86.808 183.536 86.6913 183.01C85.8156 178.69 83.247 174.546 79.0438 172.794C77.5844 174.137 76.0082 175.304 74.432 176.472C70.5207 179.216 66.084 181.726 61.2971 182.31C55.7512 183.01 50.0302 182.076 44.9514 179.8C44.893 179.916 44.893 179.975 44.8346 180.091C44.1341 181.843 42.9665 183.361 42.1492 185.054C41.4487 186.513 41.2152 188.264 41.2736 189.899C41.332 190.716 41.5071 191.533 41.799 192.292C42.0909 193.051 42.6163 193.693 42.9082 194.511C43.1417 195.27 43.1417 196.087 42.6747 196.729C42.266 197.313 41.5655 197.546 40.9817 197.955C39.8141 198.772 38.8801 199.94 38.2963 201.282C37.7126 202.567 37.5374 204.026 37.7709 205.427C38.0628 207.179 38.7633 208.813 39.2304 210.506C40.2228 214.184 39.5223 217.745 38.6466 221.364C37.8877 224.458 37.4791 227.494 37.5958 230.705C37.6542 233.682 38.2379 236.601 39.2887 239.403ZM38.9385 123.173C38.9969 124.866 39.2888 126.559 39.7558 128.194C39.9893 128.077 40.2228 128.019 40.5147 127.96C42.0909 127.435 43.7254 127.318 45.4184 127.318C45.36 125.8 45.36 124.283 45.5352 122.765C45.6519 121.772 45.7686 120.78 46.0021 119.787C46.0021 119.729 45.9438 119.671 45.9438 119.612C45.7686 118.328 45.3016 117.044 44.2508 116.285C43.3168 115.584 41.8574 115.351 40.9233 116.051C39.9893 116.752 39.639 118.153 39.4055 119.204C39.0553 120.488 38.9385 121.831 38.9385 123.173ZM39.8141 143.781C39.639 143.722 39.4639 143.605 39.3472 143.489C35.7278 140.979 33.451 136.892 32.3419 132.689C31.6997 130.295 31.4078 127.785 31.4078 125.275C31.4078 124.691 31.4078 124.107 31.4078 123.524C30.9991 124.283 30.9992 125.392 30.9408 126.151C30.4154 131.521 30.0067 137.301 32.2834 142.321C32.8088 143.43 33.451 144.54 34.2099 145.474C34.9105 146.291 35.7277 147.283 36.8953 147.283C38.8801 147.283 39.7558 145.532 39.8141 143.781ZM41.6238 141.621C41.799 140.862 41.6822 140.103 41.5655 139.344C41.5071 138.643 41.3903 137.943 41.2736 137.242C39.7558 136.775 38.4131 136.016 37.479 134.732C36.545 133.506 36.0196 131.696 36.8953 130.295C37.1872 129.828 37.5958 129.42 38.0628 129.128C37.2455 126.443 36.8369 123.699 37.1288 120.897C37.1872 120.371 37.2456 119.846 37.3623 119.262C36.1364 118.737 35.2023 119.262 34.8521 119.554C33.2759 120.722 33.3343 123.057 33.3343 124.866C33.3926 128.719 33.918 132.689 35.6694 136.133C36.4283 137.651 37.3623 139.052 38.5299 140.278C39.1136 140.92 39.8142 141.562 40.5731 142.029C41.1569 142.38 41.4487 142.321 41.6238 141.621ZM45.6519 163.862C45.9438 163.979 46.2356 164.213 46.294 164.505C47.3448 164.797 48.7459 164.505 49.7383 164.33C50.9059 164.154 52.1318 163.921 53.2993 163.629C55.576 163.045 57.8528 162.286 59.9544 161.294C64.2743 159.251 68.1856 156.449 71.4548 152.946C71.5131 152.888 71.5715 152.829 71.6299 152.771C70.5791 150.377 70.1121 147.75 69.8202 145.123C69.4699 142.088 69.2948 139.052 68.9445 136.016C68.5942 132.922 67.7769 129.887 66.7261 126.968C65.6753 123.932 64.1576 120.838 61.8808 118.503C59.9544 116.518 57.269 114.884 54.4085 114.825C51.3145 114.767 49.2713 116.927 47.987 119.554C47.9286 119.671 47.8703 119.788 47.8119 119.904C47.1697 122.298 47.1697 124.866 47.2281 127.26C47.2281 127.318 47.2281 127.318 47.2281 127.318C47.8119 127.377 48.3372 127.377 48.921 127.435C51.081 127.61 53.2993 127.96 55.3425 128.602C56.1598 126.267 56.3933 123.757 55.9263 121.364C55.8096 120.838 56.0431 120.313 56.6269 120.196C57.0939 120.079 57.736 120.371 57.7944 120.897C58.3198 123.699 58.0863 126.618 57.1523 129.303C57.6193 129.478 58.0863 129.712 58.4949 129.887C61.9976 131.638 64.5662 134.791 66.551 138.06C67.1932 139.11 65.5586 140.103 64.9165 139.052C62.9316 135.841 60.4214 132.806 56.8604 131.229C54.2918 130.12 51.548 129.595 48.8043 129.361C46.4692 129.186 44.1341 128.953 41.799 129.478C40.6898 129.712 39.2304 130.12 38.4715 131.113C37.7126 132.105 38.8217 133.623 39.5806 134.265C40.9233 135.374 42.733 135.666 44.426 135.783C46.4108 135.958 48.3957 136.016 50.4389 136.075C51.4313 136.133 51.7231 137.359 50.9059 137.885C49.0378 139.169 48.2205 141.504 48.1621 143.722C48.1621 146.583 49.3297 149.326 50.4389 151.953C50.614 152.421 50.2053 153.004 49.7383 153.121C49.2129 153.238 48.7459 152.946 48.5708 152.42C47.3448 149.443 46.0022 146.291 46.2357 143.022C46.2357 142.671 46.2941 142.38 46.3525 142.029C45.2433 142.088 44.1925 141.679 43.4336 140.803C43.4336 141.154 43.4335 141.446 43.3752 141.796C43.2584 142.613 42.8498 143.43 42.1492 143.839C42.0325 143.897 41.8574 143.956 41.7406 144.014C41.7406 144.131 41.6823 144.248 41.6823 144.364C41.6823 144.598 41.6238 144.831 41.5655 145.007C41.4487 146.232 41.5071 147.458 41.7406 148.684C42.2076 151.37 43.0249 153.997 43.7255 156.682C44.426 159.134 45.0681 161.469 45.6519 163.862ZM44.3092 138.527C44.7763 139.461 45.4768 140.511 46.6443 140.161C46.8195 140.103 46.9362 140.044 47.1113 139.986C47.4032 139.286 47.7535 138.643 48.2205 138.06V138.001C46.7611 137.943 45.36 137.885 43.9006 137.709C44.0757 138.001 44.1925 138.293 44.3092 138.527ZM76.9422 161.761C75.7163 159.251 73.9649 157.032 72.5639 154.639C69.1196 158.317 64.9165 161.294 60.2463 163.337C57.8528 164.388 55.3426 165.205 52.8323 165.789C51.548 166.081 50.3221 166.256 49.0378 166.431C47.7535 166.606 46.4692 166.665 45.2433 166.198C43.3168 168.591 44.3676 172.152 44.893 174.954C45.1265 175.947 45.2433 176.939 45.1849 177.931C47.9287 179.216 50.9643 180.091 53.9999 180.442C56.6269 180.734 59.429 180.792 62.0559 180.325C64.2159 179.916 66.2591 179.041 68.1856 178.048C71.5131 176.297 74.7238 174.137 77.526 171.568C77.526 171.51 77.526 171.452 77.526 171.393C78.5184 168.124 78.4016 164.855 76.9422 161.761ZM100.643 207.996C100.06 208.638 99.5343 209.339 99.0673 210.039C98.1332 211.323 97.2575 212.724 96.4986 214.184C95.8565 215.41 95.0976 216.986 95.5646 218.387C95.9149 219.496 97.0824 219.963 98.1916 219.846C99.4175 219.671 100.293 218.854 100.877 217.803C102.161 215.527 102.045 212.549 101.402 210.097C101.169 209.339 100.935 208.638 100.643 207.996ZM146.295 170.284C145.652 170.226 145.01 170.109 144.368 169.934C140.982 169.116 137.772 167.482 134.678 165.964C132.401 164.855 130.182 163.687 128.022 162.403C128.781 163.921 129.599 165.439 130.416 166.956C131.642 169.058 133.043 170.751 135.495 171.452C138.881 172.386 143.259 172.327 146.295 170.284ZM171.864 163.104C171.806 163.104 171.806 163.162 171.747 163.162C170.988 163.629 170.171 164.096 169.354 164.505C167.719 165.322 166.026 166.022 164.333 166.606C161.181 167.715 157.912 168.766 154.701 169.583C153.825 169.817 152.95 169.992 152.016 170.109C155.927 172.677 161.006 173.67 165.326 171.568C167.369 170.634 169.12 169.116 170.229 167.19C171.047 165.964 171.514 164.563 171.864 163.104ZM172.564 160.068C172.506 159.951 172.506 159.776 172.448 159.601C172.331 159.134 172.273 158.609 172.156 158.141C171.922 156.916 171.689 155.69 171.455 154.464C170.288 148.276 169.179 142.088 168.011 135.841C167.369 132.339 166.727 128.836 166.026 125.333C165.968 124.925 166.085 124.458 166.493 124.224C171.339 121.539 176.184 118.795 181.029 116.051C178.694 114.358 176.359 112.607 174.082 110.856C169.004 107.061 163.925 103.15 158.846 99.3554C154.117 95.7944 149.272 91.5912 143.026 91.5328C139.523 92.7004 136.662 93.576 136.195 99.5889C135.845 103.967 138.18 109.046 133.627 113.891L132.576 114.125C132.751 114.183 132.926 114.242 133.101 114.3C133.568 114.475 133.802 115.059 133.627 115.584C133.452 116.051 132.868 116.285 132.342 116.11C129.132 114.767 124.987 116.168 123.236 119.204C121.484 122.181 122.36 126.501 125.045 128.661C127.789 130.762 132.167 130.529 134.619 128.077C134.969 127.727 135.612 127.727 135.962 128.077C136.312 128.427 136.312 129.07 135.962 129.42C134.678 130.704 132.926 131.521 131.175 131.813C131.175 131.872 131.175 131.988 131.175 132.047C129.949 141.154 128.022 150.085 126.271 159.076C126.33 159.076 126.33 159.076 126.388 159.134C130.007 161.352 133.802 163.395 137.655 165.205C140.924 166.723 144.427 168.416 148.104 168.533C151.84 168.649 155.46 167.482 158.963 166.431C162.232 165.439 165.443 164.388 168.536 162.87C169.996 162.111 171.28 161.177 172.623 160.301C172.565 160.185 172.564 160.126 172.564 160.068ZM265.326 130.996C265.151 131.113 265.034 131.229 264.859 131.288C264.976 133.039 265.852 134.791 267.895 134.791C269.062 134.791 269.88 133.798 270.522 132.981C271.281 131.988 271.923 130.879 272.39 129.77C274.608 124.691 274.083 118.912 273.499 113.541C273.441 112.782 273.382 111.673 272.974 110.914C272.974 111.498 272.974 112.082 272.974 112.665C272.974 115.176 272.74 117.686 272.098 120.079C271.106 124.341 268.887 128.427 265.326 130.996ZM266.435 116.635C266.902 116.927 267.311 117.336 267.603 117.803C268.537 119.204 268.012 121.013 267.136 122.298C266.202 123.582 264.859 124.399 263.341 124.866C263.225 125.567 263.166 126.267 263.108 126.968C263.05 127.727 262.933 128.486 263.108 129.245C263.283 129.945 263.575 130.004 264.159 129.595C264.918 129.128 265.56 128.486 266.144 127.844C267.311 126.618 268.245 125.217 268.946 123.699C270.639 120.196 271.164 116.226 271.164 112.374C271.164 110.564 271.164 108.229 269.588 107.12C269.179 106.828 268.245 106.361 267.078 106.886C267.194 107.47 267.253 107.995 267.311 108.521L266.435 116.635ZM258.379 107.411C258.613 108.404 258.788 109.396 258.905 110.389C259.08 111.907 259.138 113.424 259.08 114.942C260.714 114.884 262.349 115.001 263.984 115.526C264.217 115.584 264.451 115.701 264.742 115.759C265.151 114.125 265.443 112.432 265.501 110.739C265.501 109.396 265.385 108.054 265.093 106.769C264.801 105.719 264.451 104.317 263.517 103.617C262.524 102.916 261.065 103.15 260.189 103.85C259.138 104.668 258.73 105.952 258.555 107.236C258.379 107.295 258.379 107.353 258.379 107.411ZM231.584 143.781C233.219 145.415 234.912 146.933 236.838 148.217C240.516 150.786 244.602 152.771 248.922 153.997C252.016 154.872 256.57 155.456 258.905 152.596C258.963 152.537 259.021 152.479 259.08 152.42C259.08 152.362 259.08 152.304 259.08 152.304C259.722 149.618 260.423 146.933 261.065 144.306C261.707 141.679 262.524 138.994 262.933 136.308C263.108 135.082 263.166 133.856 263.05 132.631C262.991 132.397 262.991 132.164 262.933 131.988C262.933 131.872 262.874 131.755 262.874 131.638C262.758 131.58 262.583 131.521 262.466 131.463C261.707 131.054 261.357 130.237 261.182 129.42C261.123 129.07 261.123 128.778 261.123 128.427C260.364 129.303 259.313 129.77 258.204 129.712C258.263 130.062 258.321 130.354 258.321 130.704C258.613 133.973 257.329 137.184 256.161 140.161C255.986 140.628 255.519 140.979 254.993 140.862C254.526 140.745 254.118 140.161 254.293 139.694C255.344 137.067 256.511 134.323 256.453 131.463C256.394 129.245 255.577 126.91 253.651 125.684C252.833 125.158 253.125 123.932 254.118 123.874C256.103 123.757 258.087 123.64 260.131 123.465C261.824 123.29 263.633 122.998 264.918 121.889C265.677 121.247 266.786 119.729 265.968 118.737C265.21 117.803 263.75 117.394 262.641 117.161C260.364 116.635 257.971 116.927 255.636 117.102C252.892 117.394 250.148 117.919 247.58 119.087C244.077 120.663 241.567 123.757 239.64 127.026C238.998 128.077 237.305 127.143 237.947 126.092C239.932 122.765 242.442 119.612 245.887 117.803C246.354 117.569 246.762 117.394 247.229 117.161C246.295 114.475 246.003 111.615 246.47 108.754C246.529 108.229 247.171 107.937 247.638 108.054C248.163 108.17 248.397 108.696 248.338 109.221C247.93 111.673 248.222 114.183 249.039 116.46C251.141 115.818 253.301 115.409 255.46 115.176C256.044 115.117 256.57 115.059 257.153 115.001V114.942C257.212 112.549 257.153 109.98 256.511 107.587C256.453 107.47 256.395 107.353 256.336 107.236C255.052 104.609 252.95 102.508 249.856 102.566C246.996 102.625 244.31 104.317 242.442 106.361C240.224 108.754 238.765 111.848 237.714 114.884C236.721 117.803 235.962 120.897 235.612 123.991C235.262 127.026 235.203 130.062 234.853 133.098C234.561 135.9 234.094 138.76 232.868 141.329C232.518 142.204 232.051 143.022 231.584 143.781ZM256.395 125.684C256.862 126.326 257.212 126.968 257.504 127.61C257.62 127.668 257.796 127.727 257.971 127.785C259.197 128.077 259.839 127.026 260.306 126.092C260.423 125.859 260.598 125.567 260.714 125.333C259.255 125.508 257.796 125.625 256.395 125.684ZM259.605 157.616C259.605 156.682 259.489 155.748 259.43 154.814C258.087 155.982 256.219 156.507 254.468 156.624C249.973 156.974 245.42 155.164 241.45 153.238C237.422 151.253 233.686 148.684 230.533 145.474C229.599 146.875 228.724 148.276 228.023 149.794L227.79 149.852C227.731 149.969 227.731 150.085 227.673 150.202C227.031 152.245 227.031 154.113 227.206 156.215C227.264 156.273 227.323 156.273 227.439 156.39C231.175 159.893 236.079 161.994 241.158 162.637C243.902 162.987 246.762 163.104 249.564 163.104C252.308 163.104 255.052 162.812 257.679 161.994C257.737 161.819 257.796 161.644 257.912 161.527C259.08 160.477 259.664 159.251 259.605 157.616ZM144.835 197.605C143.784 197.721 142.734 198.305 142.442 199.414C142.325 199.881 142.617 200.465 143.142 200.582C143.201 200.582 143.317 200.582 143.376 200.582C143.493 201.458 143.96 202.216 144.66 202.683C145.244 203.092 146.003 203.209 146.645 202.859C147.17 202.567 147.462 201.983 147.579 201.458C147.871 200.582 147.754 199.473 147.229 198.714C146.703 198.013 145.828 197.546 144.835 197.605ZM145.711 201.107C145.594 201.341 145.652 201.224 145.711 201.107V201.107ZM145.769 200.991C145.769 201.049 145.769 201.049 145.711 201.107C145.769 201.107 145.769 201.107 145.711 201.107C145.711 201.107 145.711 201.107 145.652 201.107C145.652 201.107 145.594 201.107 145.594 201.049C145.594 201.049 145.594 201.049 145.652 201.049C145.594 200.991 145.536 200.932 145.536 200.874V200.932C145.536 200.932 145.536 200.932 145.536 200.874C145.477 200.757 145.536 200.815 145.536 200.874L145.477 200.815C145.419 200.757 145.419 200.699 145.361 200.64V200.582C145.361 200.524 145.361 200.524 145.361 200.465C145.361 200.348 145.361 200.173 145.361 200.057C145.361 199.881 145.302 199.706 145.185 199.531H145.244H145.185H145.244H145.302C145.302 199.531 145.302 199.531 145.361 199.589C145.419 199.648 145.477 199.706 145.419 199.648C145.477 199.706 145.477 199.706 145.536 199.765C145.536 199.765 145.536 199.765 145.536 199.706C145.536 199.706 145.536 199.765 145.594 199.765C145.653 199.881 145.652 199.881 145.652 199.823C145.652 199.881 145.711 199.881 145.711 199.94C145.711 199.94 145.711 199.998 145.769 199.998C145.769 200.057 145.769 200.057 145.828 200.115C145.828 200.173 145.828 200.232 145.886 200.29C145.886 200.407 145.886 200.582 145.886 200.699C145.769 200.815 145.769 200.932 145.769 200.991ZM145.886 201.224C145.828 201.224 145.828 201.166 145.828 201.166C145.828 201.166 145.828 201.166 145.886 201.224ZM152.308 207.12C151.957 205.544 152.074 204.026 152.191 202.45C152.308 200.932 152.366 199.298 151.957 197.838C151.782 197.196 151.49 196.554 151.082 196.028C150.673 195.445 149.856 194.919 149.739 194.16C149.564 193.401 149.739 192.526 149.797 191.767C149.856 190.891 149.972 190.074 150.148 189.198C150.498 187.505 151.315 186.279 152.249 184.878C153.183 183.419 153.417 181.901 152.716 180.325C152.132 179.099 150.965 177.99 151.023 176.53C151.082 175.304 149.155 175.304 149.097 176.53C149.038 177.873 149.739 178.982 150.439 180.091C151.198 181.376 151.607 182.31 150.79 183.711C149.972 185.053 149.038 186.221 148.571 187.797C148.104 189.373 147.929 191.066 147.871 192.701C147.812 193.46 147.754 194.219 147.988 194.919C148.279 195.737 148.863 196.262 149.389 196.904C150.323 198.072 150.439 199.531 150.439 200.932C150.381 202.45 150.206 203.968 150.264 205.486C150.323 207.237 150.848 208.872 151.49 210.506C152.891 214.009 155.285 217.862 154.059 221.773C153.65 222.94 155.518 223.466 155.927 222.298C157.036 218.854 155.752 215.41 154.351 212.257C153.475 210.506 152.658 208.872 152.308 207.12ZM135.203 121.189C132.634 119.32 129.248 118.795 126.213 119.612C125.045 119.963 125.512 121.772 126.738 121.48C129.307 120.78 132.051 121.305 134.211 122.882C134.619 123.173 135.261 122.94 135.553 122.531C135.845 122.064 135.67 121.539 135.203 121.189ZM145.536 184.061C146.119 183.828 146.645 183.419 146.995 182.894C147.287 182.368 147.462 181.784 147.521 181.201C147.579 180.617 147.462 180.091 147.229 179.566C146.937 178.924 146.295 178.574 145.594 178.515C144.952 178.457 144.193 178.749 143.784 179.274C143.434 179.624 143.259 180.091 143.142 180.5C143.084 180.558 143.084 180.558 143.026 180.617C142.792 180.85 142.675 181.084 142.558 181.434C142.383 181.901 142.442 182.485 142.617 182.952C143.084 184.003 144.427 184.47 145.536 184.061ZM144.368 182.076C144.427 182.018 144.543 181.959 144.602 181.901C144.66 181.784 144.718 181.726 144.777 181.609C144.777 181.551 144.835 181.551 144.835 181.492C144.952 181.317 145.01 181.084 144.952 180.909C144.952 180.85 144.952 180.85 145.01 180.792C145.01 180.734 145.069 180.675 145.069 180.617C145.069 180.617 145.069 180.558 145.127 180.558L145.185 180.5C145.244 180.5 145.244 180.442 145.302 180.442C145.361 180.442 145.361 180.442 145.419 180.383H145.477C145.536 180.5 145.536 180.558 145.536 180.675C145.536 180.85 145.536 180.967 145.536 181.142C145.477 181.317 145.477 181.434 145.419 181.609C145.361 181.668 145.361 181.726 145.302 181.784C145.244 181.843 145.244 181.901 145.185 181.959C145.185 181.959 145.185 181.901 145.244 181.901C145.244 181.901 145.244 181.901 145.185 181.959C145.127 182.018 145.127 182.018 145.069 182.076C145.01 182.135 144.952 182.135 144.894 182.135C144.835 182.135 144.777 182.193 144.66 182.193C144.602 182.193 144.543 182.193 144.485 182.193C144.427 182.193 144.427 182.193 144.368 182.135H144.31C144.368 182.135 144.368 182.135 144.368 182.076ZM149.272 96.0863C149.739 95.9695 150.089 95.3857 149.972 94.9187C149.797 94.3933 149.33 94.1598 148.805 94.2182C148.63 94.2766 148.455 94.2765 148.279 94.3349C148.163 94.3349 148.104 94.3933 147.988 94.3933C147.988 94.3933 147.929 94.3933 147.871 94.3933C147.521 94.4517 147.17 94.4517 146.82 94.5101C146.12 94.5684 145.361 94.5101 144.66 94.4517C144.135 94.3933 143.668 94.9187 143.726 95.3857C143.726 95.9695 144.135 96.3198 144.66 96.3198C146.178 96.4949 147.754 96.3781 149.272 96.0863ZM145.769 101.632C145.477 101.223 144.952 100.99 144.427 101.282C144.018 101.515 143.726 102.158 144.076 102.625C145.361 104.317 146.645 105.952 147.871 107.645C148.163 108.054 148.688 108.287 149.214 107.995C149.622 107.762 149.914 107.12 149.564 106.653C148.338 104.96 147.054 103.325 145.769 101.632ZM153.592 103.208C152.308 101.749 151.023 100.289 149.681 98.8884C149.33 98.5381 148.63 98.5381 148.338 98.8884C147.988 99.297 147.988 99.8224 148.338 100.231C149.622 101.69 150.906 103.15 152.249 104.551C152.599 104.901 153.3 104.901 153.592 104.551C153.942 104.142 153.942 103.617 153.592 103.208ZM140.982 96.2614C140.457 96.4365 140.223 96.9035 140.282 97.4289C140.457 98.6549 140.69 99.9392 140.399 101.107C140.165 102.041 139.698 102.741 138.822 103.15C138.355 103.383 138.239 104.026 138.472 104.493C138.764 104.96 139.289 105.076 139.815 104.843C141.041 104.259 141.858 103.033 142.208 101.749C142.617 100.173 142.383 98.5381 142.15 96.9619C142.033 96.3781 141.391 96.0863 140.982 96.2614Z"
                                    fill="black"
                                />
                                <path
                                    d="M121.602 50.6121C121.893 51.0207 121.66 51.7212 121.251 51.9547C120.784 52.2466 120.259 52.0131 119.909 51.6045C117.34 48.277 119.617 43.1397 122.361 40.6295C123.762 39.3452 125.746 38.2944 127.731 38.9366C128.665 39.2284 129.307 39.929 130.008 40.5711C130.825 41.33 131.526 41.5052 132.518 40.9214C134.036 40.0457 135.145 38.5279 136.663 37.5939C138.239 36.6015 139.991 36.0177 141.859 35.7842C145.361 35.3172 148.981 36.4847 151.374 39.1117C153.534 41.5635 154.702 45.0078 153.884 48.277C153.009 51.8964 149.915 54.5817 146.529 55.8076C144.544 56.5082 142.326 56.8001 140.224 56.6833C139.173 56.6249 138.122 56.3914 137.13 56.4498C136.079 56.5082 135.554 57.1503 135.087 57.9676C134.153 59.5438 133.394 61.1784 132.168 62.5794C130.942 63.9805 129.307 64.9729 127.498 65.2648C125.513 65.5567 123.528 64.9729 121.777 64.1556C120.726 63.6886 119.792 63.1048 118.8 62.4627C118.041 61.9957 117.282 61.5286 116.64 60.8865C115.18 59.427 115.297 57.6173 116.173 55.8076C117.165 53.7644 118.449 51.8964 119.85 50.0867C120.142 49.678 120.668 49.4445 121.193 49.7364C121.602 49.9699 121.835 50.612 121.543 51.0791C120.201 52.8304 118.975 54.6985 117.924 56.6833C117.457 57.559 117.107 58.4346 117.807 59.3103C118.332 60.0108 119.15 60.4195 119.909 60.8865C121.66 61.9957 123.586 63.1048 125.688 63.3967C127.614 63.6302 129.307 62.8713 130.592 61.5286C131.643 60.4195 132.401 59.0184 133.16 57.6757C133.919 56.2163 134.912 54.8152 136.663 54.5817C137.597 54.465 138.531 54.6401 139.524 54.6985C140.458 54.7569 141.392 54.7569 142.326 54.6985C144.077 54.5817 145.887 54.1731 147.463 53.2974C150.207 51.838 152.366 49.211 152.366 46.0002C152.366 43.1397 150.732 40.3376 148.338 38.8782C145.653 37.1852 142.267 37.2436 139.407 38.4112C137.947 39.0533 136.721 39.9874 135.554 41.0381C134.445 42.0889 133.102 43.2565 131.467 43.1981C130.65 43.1981 129.95 42.8479 129.307 42.3808C128.724 41.9138 128.257 41.2133 127.556 40.863C126.213 40.1625 124.637 41.2133 123.703 42.1473C121.543 44.1322 119.617 48.0434 121.602 50.6121ZM181.03 68.1253C183.19 66.7826 185.875 68.4756 186.225 70.8106C186.401 72.2117 185.934 73.4376 185.175 74.5468C184.474 75.5976 184.474 76.8235 185.525 77.5824C186.342 78.1662 187.685 78.2246 188.094 79.3338C188.502 80.3846 188.094 81.6105 187.451 82.4278C186.109 84.1207 183.598 84.8796 181.614 84.0623C179.103 83.0699 177.702 80.0927 178.053 77.4657C178.111 76.9403 177.819 76.1814 177.119 76.2398C175.951 76.3565 174.725 76.3565 173.558 76.1814C172.799 76.0646 171.164 75.9479 170.755 75.1306C170.405 74.43 170.639 73.0874 170.755 72.3285C170.931 71.3944 171.164 70.402 171.631 69.5263C172.448 67.9502 173.733 66.6659 175.484 66.3156C177.06 65.9653 178.695 66.374 179.746 67.6583C180.505 68.5923 181.906 67.2496 181.088 66.3156C179.687 64.5643 177.294 64.0389 175.134 64.4475C172.74 64.9145 170.814 66.6659 169.821 68.8258C169.296 69.9934 168.946 71.2193 168.771 72.5036C168.654 73.6711 168.479 75.189 169.121 76.2398C169.821 77.4073 171.222 77.7576 172.448 77.9911C173.966 78.283 175.542 78.3997 177.06 78.2246C176.768 77.8159 176.418 77.4073 176.126 76.9987C175.718 80.0927 176.885 83.245 179.454 85.1131C181.906 86.8644 185.291 86.6309 187.627 84.8796C189.787 83.245 191.363 79.3338 188.794 77.2322C188.269 76.8235 187.627 76.59 187.043 76.2981C186.926 76.2397 186.517 76.0062 186.517 76.0062C186.459 75.7727 187.043 75.1306 187.16 74.9555C187.977 73.6128 188.269 71.9198 188.035 70.3436C187.393 66.7826 183.248 64.5643 180.037 66.4907C178.987 67.1329 179.979 68.7674 181.03 68.1253ZM125.571 56.3914C125.396 56.3914 125.279 56.3914 125.104 56.3914C124.987 56.3914 124.871 56.3914 124.754 56.4498C124.637 56.5082 124.52 56.5665 124.462 56.6249C124.345 56.6833 124.287 56.8001 124.287 56.9168C124.229 57.0336 124.17 57.1503 124.229 57.2671C124.229 57.3255 124.229 57.4422 124.287 57.5006C124.345 57.6757 124.404 57.7925 124.52 57.9092C124.579 57.9676 124.637 58.026 124.696 58.0844C124.871 58.1427 124.988 58.2011 125.163 58.2011C125.338 58.2011 125.455 58.2011 125.63 58.2011C125.746 58.2011 125.863 58.2011 125.98 58.1427C126.097 58.0844 126.213 58.026 126.272 57.9676C126.389 57.9092 126.447 57.7925 126.447 57.6757C126.505 57.559 126.564 57.4422 126.505 57.3255C126.505 57.2671 126.505 57.1503 126.447 57.0919C126.389 56.9168 126.33 56.8001 126.213 56.6833C126.155 56.6249 126.097 56.5666 126.038 56.5082C125.922 56.4498 125.746 56.3914 125.571 56.3914ZM141.216 43.4316C141.041 43.6068 140.925 43.7235 140.749 43.8986C140.633 43.957 140.574 44.0738 140.574 44.1905C140.516 44.3073 140.458 44.424 140.516 44.5408C140.516 44.6576 140.516 44.7743 140.574 44.8911C140.633 45.0078 140.691 45.1246 140.749 45.1829C140.808 45.2413 140.866 45.2997 140.925 45.3581C141.1 45.4165 141.216 45.4748 141.392 45.4748C141.45 45.4748 141.567 45.4748 141.625 45.4165C141.8 45.3581 141.917 45.2997 142.034 45.1829C142.209 45.0078 142.326 44.8911 142.501 44.7159C142.618 44.6576 142.676 44.5408 142.676 44.424C142.734 44.3073 142.793 44.1905 142.734 44.0738C142.734 43.957 142.734 43.8403 142.676 43.7235C142.618 43.6068 142.559 43.49 142.501 43.4316C142.442 43.3732 142.384 43.3149 142.326 43.2565C142.15 43.1981 142.034 43.1397 141.859 43.1397C141.8 43.1397 141.683 43.1397 141.625 43.1981C141.45 43.2565 141.333 43.3149 141.216 43.4316ZM132.401 48.3353C132.577 48.1602 132.693 48.0434 132.868 47.8683C132.985 47.8099 133.044 47.6932 133.044 47.5764C133.102 47.4597 133.16 47.3429 133.102 47.2262C133.102 47.1094 133.102 46.9926 133.044 46.8759C132.985 46.7591 132.927 46.6424 132.868 46.584C132.81 46.5256 132.752 46.4673 132.693 46.4089C132.518 46.3505 132.401 46.2921 132.226 46.2921C132.168 46.2921 132.051 46.2921 131.993 46.3505C131.818 46.4089 131.701 46.4673 131.584 46.584C131.409 46.7591 131.292 46.8759 131.117 47.051C131 47.1094 130.942 47.2262 130.942 47.3429C130.884 47.4597 130.825 47.5764 130.884 47.6932C130.884 47.8099 130.884 47.9267 130.942 48.0434C131 48.1602 131.059 48.277 131.117 48.3353C131.176 48.3937 131.234 48.4521 131.292 48.5105C131.467 48.5688 131.584 48.6272 131.759 48.6272C131.818 48.6272 131.934 48.6272 131.993 48.5688C132.168 48.5688 132.285 48.4521 132.401 48.3353ZM184.357 94.5119C184.532 94.3368 184.649 94.22 184.824 94.0449C184.941 93.9865 185 93.8697 185 93.753C185.058 93.6362 185.116 93.5195 185.058 93.4027C185.058 93.286 185.058 93.1692 185 93.0525C184.941 92.9357 184.883 92.819 184.824 92.7606C184.766 92.7022 184.708 92.6438 184.649 92.5854C184.474 92.5271 184.357 92.4687 184.182 92.4687C184.124 92.4687 184.007 92.4687 183.949 92.5271C183.774 92.5854 183.657 92.6438 183.54 92.7606C183.365 92.9357 183.248 93.0525 183.073 93.2276C182.956 93.286 182.898 93.4027 182.898 93.5195C182.84 93.6362 182.781 93.753 182.84 93.8697C182.84 93.9865 182.84 94.1033 182.898 94.22C182.956 94.3368 183.015 94.4535 183.073 94.5119C183.131 94.5703 183.19 94.6286 183.248 94.687C183.423 94.7454 183.54 94.8038 183.715 94.8038C183.774 94.8038 183.89 94.8038 183.949 94.7454C184.124 94.7454 184.241 94.6287 184.357 94.5119ZM127.323 29.1291C127.498 28.954 127.615 28.8373 127.79 28.6621C127.906 28.6038 127.965 28.487 127.965 28.3702C128.023 28.2535 128.081 28.1367 128.023 28.02C128.023 27.9032 128.023 27.7865 127.965 27.6697C127.906 27.553 127.848 27.4362 127.79 27.3778C127.731 27.3194 127.673 27.2611 127.614 27.2027C127.439 27.1443 127.323 27.0859 127.147 27.0859C127.089 27.0859 126.972 27.0859 126.914 27.1443C126.739 27.2027 126.622 27.2611 126.505 27.3778C126.33 27.553 126.213 27.6697 126.038 27.8448C125.922 27.9032 125.863 28.02 125.863 28.1367C125.805 28.2535 125.746 28.3702 125.805 28.487C125.805 28.6038 125.805 28.7205 125.863 28.8373C125.922 28.954 125.98 29.0708 126.038 29.1291C126.097 29.1875 126.155 29.2459 126.213 29.3043C126.389 29.3627 126.505 29.421 126.68 29.421C126.739 29.421 126.856 29.421 126.914 29.3627C127.031 29.3043 127.206 29.2459 127.323 29.1291ZM178.987 70.3436C178.987 70.2853 178.987 70.2853 178.987 70.2269C178.987 70.2853 178.987 70.402 179.045 70.4604C178.987 70.402 178.987 70.402 178.987 70.3436C178.987 70.402 178.987 70.4604 178.987 70.5771C178.987 70.5188 178.987 70.4604 178.987 70.3436C178.987 70.4604 178.928 70.5188 178.928 70.6355C178.928 70.5771 178.987 70.5188 179.045 70.4604C178.987 70.5188 178.928 70.5771 178.87 70.6355C178.928 70.5771 178.928 70.5771 178.987 70.5188C178.928 70.5771 178.87 70.6355 178.811 70.6939C178.87 70.6355 178.928 70.6355 178.928 70.6355C178.87 70.6939 178.753 70.6939 178.695 70.7523H178.753C178.87 70.6939 178.987 70.6939 179.103 70.5771C179.22 70.5188 179.279 70.402 179.337 70.2853C179.395 70.1685 179.454 70.0517 179.454 69.935C179.454 69.8182 179.454 69.7015 179.454 69.5263C179.395 69.468 179.395 69.3512 179.337 69.2928C179.279 69.1761 179.162 69.0593 178.987 68.9426C178.928 68.8842 178.812 68.8842 178.753 68.8258C178.578 68.7674 178.403 68.7674 178.228 68.8258C178.111 68.8842 177.994 68.8842 177.878 69.0009C177.878 69.0009 177.819 69.0009 177.819 69.0593C177.761 69.1177 177.644 69.1761 177.586 69.2345C177.527 69.2928 177.527 69.2928 177.469 69.3512C177.41 69.468 177.294 69.5263 177.235 69.6431C177.177 69.7599 177.177 69.8182 177.119 69.935V69.9934C177.06 70.1101 177.06 70.2269 177.06 70.402C177.06 70.4604 177.06 70.5188 177.06 70.5188C177.06 70.6355 177.06 70.7523 177.119 70.8106V70.869C177.177 70.9858 177.177 71.1025 177.294 71.2193C177.352 71.336 177.469 71.3944 177.586 71.4528C177.702 71.5112 177.819 71.5696 177.936 71.5696C178.053 71.5696 178.169 71.5696 178.344 71.5696C178.403 71.5112 178.52 71.5112 178.578 71.4528C178.695 71.3944 178.811 71.2777 178.928 71.1025C178.987 71.0442 178.987 70.9274 179.045 70.869C179.045 70.6939 179.045 70.5188 178.987 70.3436ZM192.18 64.0389C192.355 64.0389 192.472 64.0389 192.647 64.0389C192.764 64.0389 192.881 64.0389 192.997 63.9805C193.114 63.9221 193.231 63.8637 193.289 63.8054C193.406 63.747 193.464 63.6302 193.464 63.5135C193.523 63.3967 193.581 63.28 193.523 63.1632C193.523 63.1048 193.523 62.9881 193.464 62.9297C193.406 62.7546 193.347 62.6378 193.231 62.5211C193.172 62.4627 193.114 62.4043 193.056 62.3459C192.881 62.2875 192.764 62.2292 192.589 62.2292C192.414 62.2292 192.297 62.2292 192.122 62.2292C192.005 62.2292 191.888 62.2292 191.771 62.2875C191.655 62.3459 191.538 62.4043 191.479 62.4627C191.363 62.521 191.304 62.6378 191.304 62.7546C191.246 62.8713 191.188 62.9881 191.246 63.1048C191.246 63.1632 191.246 63.28 191.304 63.3383C191.363 63.5135 191.421 63.6302 191.538 63.747C191.596 63.8054 191.655 63.8637 191.713 63.9221C191.83 64.0389 192.005 64.0389 192.18 64.0389Z"
                                    fill="black"
                                />
                            </g>
                            <defs>
                                <clipPath id="clip0_4556_66742">
                                    <rect
                                        width="319"
                                        height="319"
                                        fill="white"
                                        transform="translate(0 0.5)"
                                    />
                                </clipPath>
                            </defs>
                        </svg>
                        <h1 class="py-1 text-xl font-semibold text-dark">
                            Kamera Belum nyala
                        </h1>
                        <p class="py-1 text-sm font-normal text-dark">
                            Nyalakan kamera untuk melanjutkan proses absensi
                        </p>
                    </div>
                    <div
                        class="overflow-hidden relative w-full bg-black rounded-xl"
                        v-if="isCameraOn"
                    >
                        <video
                            ref="videoRef"
                            autoplay
                            playsinline
                            muted
                            class="block w-full h-auto"
                            @loadedmetadata="resizeCanvas"
                        />
                        <canvas
                            ref="canvasRef"
                            class="absolute inset-0 w-full h-full pointer-events-none"
                        ></canvas>
                    </div>
                </div>
                <div
                    class="flex justify-center items-center mt-6"
                    v-if="selectedOption === 'camera'"
                ></div>

                <!-- QR Code Section -->
                <div class="flex gap-2 py-4 mt-4 w-3/4" v-if="selectedOption === 'qr'">
                    <div class="flex flex-col gap-2 items-start">
                        <div
                            class="flex flex-col justify-center items-center p-6"
                        >
                            <vue-qrcode
                                :value="attendanceUrl"
                                :options="{ width: 200 }"
                            ></vue-qrcode>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 items-start pt-5">
                        <div v-for="(text, index) in textList" :key="index">
                            <div class="flex items-center">
                                <svg
                                    width="18"
                                    height="19"
                                    viewBox="0 0 18 19"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M17.0918 8.23193L15.8933 7.01561C15.8097 6.93369 15.7437 6.83562 15.6992 6.72736C15.6548 6.6191 15.6328 6.50292 15.6346 6.38589V4.66062C15.6335 4.42426 15.5857 4.19045 15.494 3.97262C15.4023 3.7548 15.2685 3.55725 15.1002 3.39132C14.932 3.22539 14.7326 3.09436 14.5136 3.00575C14.2946 2.91713 14.0602 2.87269 13.824 2.87496H12.0996C11.9826 2.87681 11.8665 2.85481 11.7583 2.81032C11.6501 2.76583 11.5521 2.69977 11.4702 2.61617L10.2631 1.39986C9.92685 1.06378 9.47098 0.875 8.99568 0.875C8.52037 0.875 8.06451 1.06378 7.72824 1.39986L6.51253 2.59892C6.43066 2.68252 6.33263 2.74858 6.22443 2.79307C6.11622 2.83756 6.0001 2.85955 5.88313 2.85771H4.15872C3.92248 2.85884 3.68879 2.90665 3.47107 2.99841C3.25335 3.09016 3.0559 3.22405 2.89006 3.39238C2.72421 3.56072 2.59325 3.76017 2.50468 3.9793C2.41611 4.19842 2.37169 4.43289 2.37396 4.66924V6.39451C2.3758 6.51154 2.35382 6.62773 2.30935 6.73599C2.26488 6.84425 2.19886 6.94232 2.1153 7.02424L0.899595 8.23193C0.563683 8.56836 0.375 9.02446 0.375 9.5C0.375 9.97554 0.563683 10.4316 0.899595 10.7681L2.09806 11.9844C2.18162 12.0663 2.24764 12.1644 2.29211 12.2726C2.33658 12.3809 2.35856 12.4971 2.35672 12.6141V14.3394C2.35784 14.5757 2.40563 14.8096 2.49734 15.0274C2.58905 15.2452 2.72287 15.4428 2.89112 15.6087C3.05937 15.7746 3.25873 15.9056 3.47774 15.9943C3.69675 16.0829 3.93111 16.1273 4.16734 16.125H5.89175C6.00872 16.1232 6.12484 16.1452 6.23305 16.1897C6.34125 16.2342 6.43928 16.3002 6.52116 16.3838L7.73686 17.6001C8.07313 17.9362 8.529 18.125 9.0043 18.125C9.4796 18.125 9.93547 17.9362 10.2717 17.6001L11.4788 16.4011C11.5607 16.3175 11.6587 16.2514 11.7669 16.2069C11.8751 16.1624 11.9913 16.1404 12.1082 16.1423H13.8326C14.3083 16.1423 14.7644 15.9532 15.1007 15.6168C15.4371 15.2803 15.626 14.8239 15.626 14.348V12.6227C15.6242 12.5057 15.6462 12.3895 15.6906 12.2813C15.7351 12.173 15.8011 12.0749 15.8847 11.993L17.1004 10.7767C17.2674 10.6094 17.3998 10.4107 17.4898 10.192C17.5799 9.97334 17.6258 9.73901 17.625 9.50252C17.6242 9.26602 17.5767 9.03202 17.4852 8.81396C17.3936 8.59591 17.2599 8.3981 17.0918 8.23193ZM12.5997 7.94726L8.36627 12.0879C8.30657 12.1484 8.23539 12.1963 8.15692 12.2289C8.07845 12.2615 7.99426 12.2781 7.9093 12.2777C7.8238 12.2765 7.73939 12.2583 7.66096 12.2243C7.58252 12.1902 7.51161 12.1409 7.45233 12.0793L5.40891 10.009C5.34607 9.94909 5.29591 9.87719 5.26142 9.79753C5.22693 9.71787 5.20882 9.63208 5.20816 9.54527C5.20751 9.45846 5.22432 9.3724 5.2576 9.29223C5.29088 9.21206 5.33995 9.1394 5.40188 9.0786C5.46381 9.01779 5.53734 8.97008 5.61809 8.9383C5.69883 8.90652 5.78515 8.89133 5.87189 8.89362C5.95863 8.89592 6.04402 8.91566 6.12297 8.95166C6.20193 8.98766 6.27284 9.03919 6.33147 9.10319L7.91792 10.7163L11.6944 7.02424C11.8156 6.90308 11.98 6.83503 12.1513 6.83503C12.3227 6.83503 12.4871 6.90308 12.6083 7.02424C12.669 7.08503 12.717 7.15731 12.7495 7.23686C12.782 7.3164 12.7983 7.40163 12.7975 7.48756C12.7967 7.57348 12.7788 7.65839 12.7448 7.73732C12.7108 7.81624 12.6615 7.88761 12.5997 7.94726Z"
                                        fill="#17C653"
                                    />
                                </svg>
                                <span
                                    class="ml-2 text-sm font-normal text-white"
                                    >{{ text }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#F7F7F7] w-1/2 p-4">
            <div class="flex justify-between items-center">
                <svg
                    width="178"
                    height="58"
                    viewBox="0 0 178 58"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                >
                    <rect
                        width="58"
                        height="58"
                        rx="24"
                        fill="url(#pattern0_6102_60519)"
                    />
                    <path
                        d="M69.1477 24V12.3636H71.2557V17.2898H76.6477V12.3636H78.7614V24H76.6477V19.0568H71.2557V24H69.1477ZM81.054 24V12.3636H88.6222V14.1307H83.1619V17.2898H88.2301V19.0568H83.1619V22.233H88.6676V24H81.054ZM100.335 12.3636V24H98.4602L92.9773 16.0739H92.8807V24H90.7727V12.3636H92.6591L98.1364 20.2955H98.2386V12.3636H100.335ZM108.956 15.5625C108.903 15.0663 108.679 14.6799 108.286 14.4034C107.895 14.1269 107.388 13.9886 106.763 13.9886C106.323 13.9886 105.946 14.0549 105.632 14.1875C105.318 14.3201 105.077 14.5 104.911 14.7273C104.744 14.9545 104.659 15.214 104.655 15.5057C104.655 15.7481 104.71 15.9583 104.82 16.1364C104.933 16.3144 105.087 16.4659 105.28 16.5909C105.473 16.7121 105.687 16.8144 105.922 16.8977C106.157 16.9811 106.393 17.0511 106.632 17.108L107.723 17.3807C108.162 17.483 108.585 17.6212 108.99 17.7955C109.399 17.9697 109.765 18.1894 110.087 18.4545C110.412 18.7197 110.67 19.0398 110.859 19.4148C111.049 19.7898 111.143 20.2292 111.143 20.733C111.143 21.4148 110.969 22.0152 110.621 22.5341C110.272 23.0492 109.768 23.4527 109.109 23.7443C108.454 24.0322 107.661 24.1761 106.729 24.1761C105.823 24.1761 105.037 24.036 104.371 23.7557C103.708 23.4754 103.189 23.0663 102.814 22.5284C102.443 21.9905 102.242 21.3352 102.212 20.5625H104.286C104.316 20.9678 104.441 21.3049 104.661 21.5739C104.88 21.8428 105.166 22.0436 105.518 22.1761C105.875 22.3087 106.272 22.375 106.712 22.375C107.17 22.375 107.571 22.3068 107.916 22.1705C108.265 22.0303 108.537 21.8371 108.734 21.5909C108.931 21.3409 109.032 21.0492 109.036 20.7159C109.032 20.4129 108.943 20.1629 108.768 19.9659C108.594 19.7652 108.35 19.5985 108.036 19.4659C107.725 19.3295 107.361 19.2083 106.945 19.1023L105.621 18.7614C104.662 18.5152 103.905 18.142 103.348 17.642C102.795 17.1383 102.518 16.4697 102.518 15.6364C102.518 14.9508 102.704 14.3504 103.075 13.8352C103.45 13.3201 103.96 12.9205 104.604 12.6364C105.248 12.3485 105.977 12.2045 106.791 12.2045C107.617 12.2045 108.34 12.3485 108.962 12.6364C109.587 12.9205 110.077 13.3163 110.433 13.8239C110.789 14.3277 110.973 14.9072 110.984 15.5625H108.956ZM113.023 24V12.3636H115.131V17.7102H115.273L119.812 12.3636H122.386L117.886 17.5852L122.426 24H119.892L116.42 19.0114L115.131 20.5341V24H113.023ZM123.851 24V12.3636H128.214C129.108 12.3636 129.858 12.5189 130.464 12.8295C131.074 13.1402 131.535 13.5758 131.845 14.1364C132.16 14.6932 132.317 15.3428 132.317 16.0852C132.317 16.8314 132.158 17.4792 131.839 18.0284C131.525 18.5739 131.061 18.9962 130.447 19.2955C129.834 19.5909 129.08 19.7386 128.186 19.7386H125.078V17.9886H127.902C128.425 17.9886 128.853 17.9167 129.186 17.7727C129.519 17.625 129.766 17.411 129.925 17.1307C130.088 16.8466 130.169 16.4981 130.169 16.0852C130.169 15.6723 130.088 15.3201 129.925 15.0284C129.762 14.733 129.514 14.5095 129.18 14.358C128.847 14.2027 128.417 14.125 127.891 14.125H125.959V24H123.851ZM129.862 18.7273L132.743 24H130.391L127.561 18.7273H129.862ZM136.365 12.3636V24H134.257V12.3636H136.365ZM144.972 15.5625C144.919 15.0663 144.695 14.6799 144.301 14.4034C143.911 14.1269 143.403 13.9886 142.778 13.9886C142.339 13.9886 141.962 14.0549 141.648 14.1875C141.333 14.3201 141.093 14.5 140.926 14.7273C140.759 14.9545 140.674 15.214 140.67 15.5057C140.67 15.7481 140.725 15.9583 140.835 16.1364C140.949 16.3144 141.102 16.4659 141.295 16.5909C141.489 16.7121 141.703 16.8144 141.938 16.8977C142.172 16.9811 142.409 17.0511 142.648 17.108L143.739 17.3807C144.178 17.483 144.6 17.6212 145.006 17.7955C145.415 17.9697 145.78 18.1894 146.102 18.4545C146.428 18.7197 146.686 19.0398 146.875 19.4148C147.064 19.7898 147.159 20.2292 147.159 20.733C147.159 21.4148 146.985 22.0152 146.636 22.5341C146.288 23.0492 145.784 23.4527 145.125 23.7443C144.47 24.0322 143.676 24.1761 142.744 24.1761C141.839 24.1761 141.053 24.036 140.386 23.7557C139.723 23.4754 139.205 23.0663 138.83 22.5284C138.458 21.9905 138.258 21.3352 138.227 20.5625H140.301C140.331 20.9678 140.456 21.3049 140.676 21.5739C140.896 21.8428 141.182 22.0436 141.534 22.1761C141.89 22.3087 142.288 22.375 142.727 22.375C143.186 22.375 143.587 22.3068 143.932 22.1705C144.28 22.0303 144.553 21.8371 144.75 21.5909C144.947 21.3409 145.047 21.0492 145.051 20.7159C145.047 20.4129 144.958 20.1629 144.784 19.9659C144.61 19.7652 144.366 19.5985 144.051 19.4659C143.741 19.3295 143.377 19.2083 142.96 19.1023L141.636 18.7614C140.678 18.5152 139.92 18.142 139.364 17.642C138.811 17.1383 138.534 16.4697 138.534 15.6364C138.534 14.9508 138.72 14.3504 139.091 13.8352C139.466 13.3201 139.975 12.9205 140.619 12.6364C141.263 12.3485 141.992 12.2045 142.807 12.2045C143.633 12.2045 144.356 12.3485 144.977 12.6364C145.602 12.9205 146.093 13.3163 146.449 13.8239C146.805 14.3277 146.989 14.9072 147 15.5625H144.972ZM148.527 14.1307V12.3636H157.811V14.1307H154.214V24H152.124V14.1307H148.527ZM159.689 24H157.439L161.536 12.3636H164.138L168.24 24H165.99L162.882 14.75H162.791L159.689 24ZM159.763 19.4375H165.899V21.1307H159.763V19.4375ZM169.773 24V12.3636H171.881V22.233H177.006V24H169.773Z"
                        fill="#003C77"
                    />
                    <path
                        d="M70.4659 35.8182V46H69.233V35.8182H70.4659ZM75.8812 46.1591C75.1653 46.1591 74.5488 45.9901 74.0318 45.652C73.5147 45.3139 73.117 44.8482 72.8386 44.255C72.5602 43.6617 72.421 42.9839 72.421 42.2216C72.421 41.446 72.5635 40.7616 72.8485 40.1683C73.1369 39.5717 73.5379 39.1061 74.0517 38.7713C74.5687 38.4332 75.1719 38.2642 75.8613 38.2642C76.3983 38.2642 76.8822 38.3636 77.313 38.5625C77.7439 38.7614 78.0969 39.0398 78.372 39.3977C78.6471 39.7557 78.8178 40.1733 78.8841 40.6506H77.7108C77.6213 40.3026 77.4224 39.9943 77.1142 39.7259C76.8092 39.4541 76.3983 39.3182 75.8812 39.3182C75.4238 39.3182 75.0228 39.4375 74.6781 39.6761C74.3367 39.9115 74.0699 40.2446 73.8777 40.6754C73.6887 41.103 73.5943 41.6051 73.5943 42.1818C73.5943 42.7718 73.6871 43.2855 73.8727 43.723C74.0616 44.1605 74.3268 44.5002 74.6681 44.7422C75.0128 44.9841 75.4172 45.1051 75.8812 45.1051C76.1861 45.1051 76.4629 45.0521 76.7115 44.946C76.96 44.84 77.1705 44.6875 77.3429 44.4886C77.5152 44.2898 77.6378 44.0511 77.7108 43.7727H78.8841C78.8178 44.2235 78.6537 44.6295 78.3919 44.9908C78.1333 45.3487 77.7903 45.6338 77.3627 45.8459C76.9385 46.0547 76.4447 46.1591 75.8812 46.1591ZM83.801 46.1591C83.0652 46.1591 82.4305 45.9967 81.8968 45.6719C81.3665 45.3438 80.9572 44.8864 80.6689 44.2997C80.3838 43.7098 80.2413 43.0237 80.2413 42.2415C80.2413 41.4593 80.3838 40.7699 80.6689 40.1733C80.9572 39.5734 81.3583 39.1061 81.872 38.7713C82.389 38.4332 82.9922 38.2642 83.6816 38.2642C84.0794 38.2642 84.4721 38.3305 84.8599 38.4631C85.2477 38.5956 85.6007 38.8111 85.9189 39.1094C86.237 39.4044 86.4906 39.7955 86.6795 40.2827C86.8684 40.7699 86.9629 41.3698 86.9629 42.0824V42.5795H81.0765V41.5653H85.7697C85.7697 41.1345 85.6835 40.75 85.5112 40.4119C85.3422 40.0739 85.1002 39.8071 84.7853 39.6115C84.4738 39.416 84.1059 39.3182 83.6816 39.3182C83.2143 39.3182 82.81 39.4342 82.4686 39.6662C82.1305 39.8949 81.8703 40.1932 81.688 40.5611C81.5057 40.929 81.4146 41.3234 81.4146 41.7443V42.4205C81.4146 42.9972 81.514 43.486 81.7129 43.8871C81.9151 44.2848 82.1951 44.5881 82.5531 44.7969C82.911 45.0024 83.327 45.1051 83.801 45.1051C84.1092 45.1051 84.3876 45.062 84.6362 44.9759C84.8881 44.8864 85.1052 44.7538 85.2875 44.5781C85.4698 44.3991 85.6106 44.1771 85.71 43.9119L86.8436 44.2301C86.7243 44.6146 86.5237 44.9527 86.242 45.2443C85.9603 45.5327 85.6123 45.758 85.198 45.9205C84.7837 46.0795 84.318 46.1591 83.801 46.1591ZM98.4125 38.3636C98.3528 37.8598 98.1109 37.4688 97.6866 37.1903C97.2624 36.9119 96.742 36.7727 96.1255 36.7727C95.6748 36.7727 95.2804 36.8456 94.9423 36.9915C94.6075 37.1373 94.3457 37.3378 94.1568 37.593C93.9712 37.8482 93.8784 38.1383 93.8784 38.4631C93.8784 38.7348 93.943 38.9685 94.0723 39.1641C94.2048 39.3563 94.3739 39.517 94.5794 39.6463C94.7849 39.7723 95.0003 39.8767 95.2257 39.9595C95.4511 40.0391 95.6582 40.1037 95.8471 40.1534L96.8812 40.4318C97.1464 40.5014 97.4413 40.5975 97.7662 40.7202C98.0943 40.8428 98.4075 41.0102 98.7058 41.2223C99.0074 41.4311 99.256 41.6996 99.4515 42.0277C99.6471 42.3558 99.7449 42.7585 99.7449 43.2358C99.7449 43.786 99.6007 44.2831 99.3123 44.7273C99.0273 45.1714 98.6097 45.5244 98.0595 45.7862C97.5126 46.0481 96.8481 46.179 96.0659 46.179C95.3367 46.179 94.7053 46.0613 94.1717 45.826C93.6414 45.5907 93.2238 45.2625 92.9189 44.8416C92.6172 44.4207 92.4466 43.9318 92.4068 43.375H93.6795C93.7127 43.7595 93.8419 44.0777 94.0673 44.3295C94.296 44.5781 94.5843 44.7637 94.9324 44.8864C95.2837 45.0057 95.6615 45.0653 96.0659 45.0653C96.5365 45.0653 96.9591 44.9891 97.3336 44.8366C97.7082 44.6809 98.0048 44.4654 98.2235 44.1903C98.4423 43.9119 98.5517 43.5871 98.5517 43.2159C98.5517 42.8778 98.4572 42.6027 98.2683 42.3906C98.0794 42.1785 97.8308 42.0062 97.5225 41.8736C97.2143 41.741 96.8812 41.625 96.5233 41.5256L95.2704 41.1676C94.475 40.9389 93.8452 40.6125 93.3812 40.1882C92.9172 39.764 92.6852 39.2088 92.6852 38.5227C92.6852 37.9527 92.8393 37.4555 93.1475 37.0312C93.4591 36.6037 93.8767 36.2723 94.4004 36.0369C94.9274 35.7983 95.5157 35.679 96.1653 35.679C96.8216 35.679 97.4049 35.7966 97.9153 36.032C98.4257 36.264 98.8301 36.5821 99.1284 36.9865C99.43 37.3909 99.5891 37.8499 99.6056 38.3636H98.4125ZM104.715 46.1591C104.026 46.1591 103.421 45.995 102.901 45.6669C102.384 45.3388 101.979 44.8797 101.688 44.2898C101.399 43.6998 101.255 43.0104 101.255 42.2216C101.255 41.4261 101.399 40.7318 101.688 40.1385C101.979 39.5452 102.384 39.0845 102.901 38.7564C103.421 38.4283 104.026 38.2642 104.715 38.2642C105.405 38.2642 106.008 38.4283 106.525 38.7564C107.045 39.0845 107.45 39.5452 107.738 40.1385C108.03 40.7318 108.175 41.4261 108.175 42.2216C108.175 43.0104 108.03 43.6998 107.738 44.2898C107.45 44.8797 107.045 45.3388 106.525 45.6669C106.008 45.995 105.405 46.1591 104.715 46.1591ZM104.715 45.1051C105.239 45.1051 105.67 44.9709 106.008 44.7024C106.346 44.4339 106.596 44.081 106.759 43.6435C106.921 43.206 107.002 42.732 107.002 42.2216C107.002 41.7112 106.921 41.2356 106.759 40.7947C106.596 40.3539 106.346 39.9976 106.008 39.7259C105.67 39.4541 105.239 39.3182 104.715 39.3182C104.192 39.3182 103.761 39.4541 103.423 39.7259C103.085 39.9976 102.834 40.3539 102.672 40.7947C102.509 41.2356 102.428 41.7112 102.428 42.2216C102.428 42.732 102.509 43.206 102.672 43.6435C102.834 44.081 103.085 44.4339 103.423 44.7024C103.761 44.9709 104.192 45.1051 104.715 45.1051ZM111.14 35.8182V46H109.966V35.8182H111.14ZM118.101 42.8778V38.3636H119.275V46H118.101V44.7074H118.022C117.843 45.0952 117.564 45.425 117.186 45.6967C116.809 45.9652 116.331 46.0994 115.755 46.0994C115.277 46.0994 114.853 45.995 114.482 45.7862C114.111 45.5741 113.819 45.2559 113.607 44.8317C113.395 44.4041 113.289 43.8655 113.289 43.2159V38.3636H114.462V43.1364C114.462 43.6932 114.618 44.1373 114.929 44.4688C115.244 44.8002 115.645 44.9659 116.132 44.9659C116.424 44.9659 116.721 44.8913 117.022 44.7422C117.327 44.593 117.583 44.3643 117.788 44.0561C117.997 43.7479 118.101 43.3551 118.101 42.8778ZM124.744 38.3636V39.358H120.787V38.3636H124.744ZM121.941 36.5341H123.114V43.8125C123.114 44.1439 123.162 44.3925 123.258 44.5582C123.357 44.7206 123.483 44.83 123.636 44.8864C123.792 44.9394 123.956 44.9659 124.128 44.9659C124.257 44.9659 124.363 44.9593 124.446 44.946C124.529 44.9295 124.595 44.9162 124.645 44.9062L124.884 45.9602C124.804 45.9901 124.693 46.0199 124.551 46.0497C124.408 46.0829 124.227 46.0994 124.009 46.0994C123.677 46.0994 123.352 46.0282 123.034 45.8857C122.719 45.7431 122.458 45.526 122.249 45.2344C122.043 44.9427 121.941 44.5748 121.941 44.1307V36.5341ZM126.509 46V38.3636H127.683V46H126.509ZM127.106 37.0909C126.877 37.0909 126.68 37.013 126.514 36.8572C126.352 36.7015 126.271 36.5142 126.271 36.2955C126.271 36.0767 126.352 35.8894 126.514 35.7337C126.68 35.5779 126.877 35.5 127.106 35.5C127.335 35.5 127.53 35.5779 127.693 35.7337C127.858 35.8894 127.941 36.0767 127.941 36.2955C127.941 36.5142 127.858 36.7015 127.693 36.8572C127.53 37.013 127.335 37.0909 127.106 37.0909ZM132.934 46.1591C132.245 46.1591 131.64 45.995 131.119 45.6669C130.602 45.3388 130.198 44.8797 129.906 44.2898C129.618 43.6998 129.474 43.0104 129.474 42.2216C129.474 41.4261 129.618 40.7318 129.906 40.1385C130.198 39.5452 130.602 39.0845 131.119 38.7564C131.64 38.4283 132.245 38.2642 132.934 38.2642C133.623 38.2642 134.227 38.4283 134.744 38.7564C135.264 39.0845 135.668 39.5452 135.957 40.1385C136.248 40.7318 136.394 41.4261 136.394 42.2216C136.394 43.0104 136.248 43.6998 135.957 44.2898C135.668 44.8797 135.264 45.3388 134.744 45.6669C134.227 45.995 133.623 46.1591 132.934 46.1591ZM132.934 45.1051C133.458 45.1051 133.888 44.9709 134.227 44.7024C134.565 44.4339 134.815 44.081 134.977 43.6435C135.14 43.206 135.221 42.732 135.221 42.2216C135.221 41.7112 135.14 41.2356 134.977 40.7947C134.815 40.3539 134.565 39.9976 134.227 39.7259C133.888 39.4541 133.458 39.3182 132.934 39.3182C132.41 39.3182 131.979 39.4541 131.641 39.7259C131.303 39.9976 131.053 40.3539 130.891 40.7947C130.728 41.2356 130.647 41.7112 130.647 42.2216C130.647 42.732 130.728 43.206 130.891 43.6435C131.053 44.081 131.303 44.4339 131.641 44.7024C131.979 44.9709 132.41 45.1051 132.934 45.1051ZM139.358 41.4062V46H138.185V38.3636H139.319V39.5568H139.418C139.597 39.169 139.869 38.8575 140.233 38.6222C140.598 38.3835 141.069 38.2642 141.645 38.2642C142.162 38.2642 142.615 38.3703 143.003 38.5824C143.39 38.7912 143.692 39.1094 143.907 39.5369C144.123 39.9612 144.231 40.4981 144.231 41.1477V46H143.057V41.2273C143.057 40.6274 142.902 40.16 142.59 39.8253C142.278 39.4872 141.851 39.3182 141.307 39.3182C140.933 39.3182 140.598 39.3994 140.303 39.5618C140.011 39.7242 139.781 39.9612 139.612 40.2727C139.443 40.5843 139.358 40.9621 139.358 41.4062Z"
                        fill="#4B5675"
                    />
                    <defs>
                        <pattern
                            id="pattern0_6102_60519"
                            patternContentUnits="objectBoundingBox"
                            width="1"
                            height="1"
                        >
                            <use
                                xlink:href="#image0_6102_60519"
                                transform="scale(0.002)"
                            />
                        </pattern>
                        <image
                            id="image0_6102_60519"
                            width="500"
                            height="500"
                            preserveAspectRatio="none"
                            xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAH0CAYAAADL1t+KAAAQAElEQVR4Aez9B4Bdx3UfjJ8zc8srWwEsGgESIAn2KlKkClWo3mVLFtUtucmJnTgu+aLEafQ//hwnX2wndhLHjmM7spXYlGXZ6h0qFNVYRBKsAAGCKAvsAtvLe+/emfP/nft2QYAEQWDbe7s7gzl35s7MnTnnN3fnd8/c9x4MhRAQCAgEBAICAYGAwLJHIBD6sp/CYEBAICAQEAgIBASIFpfQA8IBgYBAQCAgEBAICCwJAoHQlwTmMEhAICAQEAgIBAQWF4HlTOiLi0zoPSAQEAgIBAQCAssIgUDoy2iygqoBgYBAQCAgEBB4LgQCoT8XMqE8IBAQCAgEBAICywiBQOjLaLKCqgGBgEBAICAQEHguBAKhPxcyi1seeg8IBAQCAgGBgMCCIhAIfUHhDJ0FBAICAYGAQECgNQgEQm8N7os7aug9IBAQCAgEBFYdAoHQV92UB4MDAgGBgEBAYCUiEAh9Jc7q4toUeg8IBAQCAgGBNkQgEHobTkpQKSAQEAgIBAQCAueKQCD0c0UstF9cBELvAYGAQEAgIDAnBAKhzwm2cFFAICAQEAgIBATaC4FA6O01H0GbxUUg9B4QCAgEBFYsAoHQV+zUBsMCAgGBgEBAYDUhEAh9Nc12sHVxEQi9BwQCAgGBFiIQCL2F4IehAwIBgYBAQCAgsFAIBEJfKCRDPwGBxUUg9B4QCAgEBM6IQCD0M8ITKgMCAYGAQEAgILA8EAiEvjzmKWgZEFhcBELvAYGAwLJHIBD6sp/CYEBAICAQEAgIBASIAqGHuyAgEBBYbARC/wGBgMASIBAIfQlADkMEBAICAYGAQEBgsREIhL7YCIf+AwIBgcVFIPQeEAgIFAgEQi9gCIeAQEAgIBAQCAgsbwQCoS/v+QvaBwQCAouLQOg9ILBsEAiEvmymKigaEAgIBAQCAgGB50YgEPpzYxNqAgIBgYDA4iIQeg8ILCACgdAXEMzQVUAgIBAQCAgEBFqFQCD0ViEfxg0IBAQCAouLQOh9lSEQCH2VTXgwNyAQEAgIBARWJgKB0FfmvAarAgIBgYDA4iIQem87BAKht92UBIUCAgGBgEBAICBw7ggEQj93zMIVAYGAQEAgILC4CITe54BAIPQ5gBYuCQgEBAICAYGAQLshEAi93WYk6BMQCAgEBAICi4vACu09EPoKndhgVkAgIBAQCAisLgQCoa+u+Q7WBgQCAgGBgMDiItCy3gOhtwz6MHBAICAQEAgIBAQWDoFA6AuHZegpIBAQCAgEBAICi4vAGXoPhH4GcEJVQCAgEBAICAQElgsCgdCXy0wFPQMCAYGAQEAgIHAGBBaA0M/Qe6gKCAQEAgIBgYBAQGBJEAiEviQwh0ECAgGBgEBAICCwuAi0PaEvrvmh94BAQCAgEBAICKwMBAKhr4x5DFYEBAICAYGAwCpHYJUT+iqf/WB+QCAgEBAICKwYBAKhr5ipDIYEBAICAYGAwGpGIBD6Is5+6DogEBAICAQEAgJLhUAg9KVCOowTEAgIBAQCAgGBRUQgEPoigru4XYfeAwIBgYBAQCAg8DQCgdCfxiLkAgIBgYBAQCAgsGwRCIS+bKducRUPvQcEAgIBgYDA8kIgEPrymq+gbUAgIBAQCAgEBE6LQCD008ISChcXgdB7QCAgEBAICCw0AoHQFxrR0F9AICAQEAgIBARagEAg9BaAHoZcXARC7wGBgEBAYDUiEAh9Nc56sDkgEBAICAQEVhwCgdBX3JQGgxYXgdB7QCAgEBBoTwQCobfnvAStAgIBgYBAQCAgcE4IBEI/J7hC44DA4iIQeg8IBAQCAnNFIBD6XJEL1wUEAgIBgYBAQKCNEAiE3kaTEVQJCCwuAqH3gEBAYCUjEAh9Jc9usC0gEBAICAQEVg0CgdBXzVQHQwMCi4tA6D0gEBBoLQKB0FuLfxg9IBAQCAgEBAICC4JAIPQFgTF0EhAICCwuAqH3gEBA4PkQCIT+fAiF+oBAQCAgEBAICCwDBAKhL4NJCioGBAICi4tA6D0gsBIQCIS+EmYx2BAQCAgEBAICqx6BQOir/hYIAAQEAgKLi0DoPSCwNAgEQl8anMMoAYFTEBARvl0k/P2dgko4CQgEBOaDQFhQ5oNeuDYgMAcEdu7bV/r1P/vEuis+8YloDpeHSwICpyAQTgICswgEQp9FIqQBgSVA4M5HBzs/+6XvXNhRSs273vWubAmGDEMEBAICqwSBQOirZKKDma1H4IH9+3v/+utfeEHfui7+9fe+7RgzS+u1ChoEBM6EQKhbTggEQl9OsxV0XbYI/GBgYOPvf+5rP9a9rqP8mne+9XGQuVu2xgTFAwIBgbZEIBB6W05LUGolIfCRP/rkpv/2V5+9bWhssh5N9n7jRuaw1b6SJjjYMmcEwoULi0Ag9IXFM/QWEDiBgH6S/d1/9KmLDpP9lQFOu3dceNnnbv+pW2snGoRMQCAgEBBYQAQCoS8gmKGrgMDJCLz9v/7VpiPHB39yeHzMXbZh45/+h9teO3pyfcgHBAICi4nA6us7EPrqm/Ng8SIjsFMkevP//OwNI2bNf2zEaypXdl7xb37vtlcfWuRhQ/cBgYDAKkcgEPoqvwGC+QuLALbZ7e997GtXHB059ssTU5ONS7es/W9//PM3hnfmCwtz6C0g0HIE2lGBQOjtOCtBp2WJwN0i8Wv/8hsvO1CP/3ct3ZBeuaH6z//8vbc+uSyNCUoHBAICyw6BQOjLbsqCwu2IgJL5Rz/+zev2P9X/K1PTtcOXXLD2v/7FT75+oB11DToFBAIC7Y7A3PQLhD433MJVAYETCLzyjp0dv/zXd77+aD35OHX1rb9mXfIv//btN33rRIOQCQgEBAICS4BAIPQlADkMsXIROCBSFpu+9lD/gV+bmBwfu/SCzf/3E++/9Ucr1+JgWUAgINCuCJwtober/kGvgEDLEFDP/N1/e++7++v2T6l36yW9tv47n33blb/fMoXCwAGBgMCqRiAQ+qqe/mD8XBHYfVy6bHn924cOH/kll+cTjvirW7es+exc+wvXBQQCAgGB+SLQHoQ+XyvC9QGBJUTgNXfc3f3+r933s09MyZ+N9Wy+qj46+qlb1skvf/rtt4wvoRphqIBAQCAgcAoCgdBPgSOcBASeGwERMXcOTm62lc6fHT565KclyzNrzf237LjgE//nLS8bfu4rQ01AICAQEFh8BFYDoS8+imGEFY8AyJzf+bnd237tm4d/fddE8puT66+80k9P7+rNJz76V2+48tsrHoBgYEAgIND2CARCb/spCgq2GoE7ROxPfPnxSw/W5BfHR0bfyn7a+PrYgZu3bfjEtRs6vt9q/cL4AYGAQEBAEQiErijMR8K1KxoB9cz/7+cfveipCf/Rg770Dwar6863/thApX7gdz/xmu2/9xevv3ZyRQMQjAsIBASWDQKB0JfNVAVFW4HAbZ/Zu3XvuPv50bGJWyWbSiibPEq5+cyrzjvvb5jZtUKnMGZAICAQEDgdAoHQT4dK+5QFTVqIwIc/ta/n4eHJ9w5I+pOjHesuiKjWMJP9n3n15spv/smbrj3YQtXC0AGBgEBA4FkIBEJ/FiShICBA9OWnJjcf9O6Do+O1nzBkush732jU73zjZds++cHXXh9+oz3cJAGBgEDbIRAIve2mZAkVCkOdFoE7+sf7fvuHT77zofH8o7J2441xVEpc5ga78/y//KNXXvS1W5nz014YCgMCAYGAQAsRCITeQvDD0O2HwGcOHDvvD+898OFHR6ff5+N4jUDFqfGhJzo4//N3bl/7/RuZMxQtWNQP3UHC3+GCIRo6CgisXgTCQrJ6536xLV92/d9xYHTN793b/9YHJvOfn+ruvcmVS+WM2dH06KfftNn/53//msuPL7RR99xD0Se+ezANpL7QyIb+AgKrD4FA6KtvzoPFp0Hgk09ObvrzHx1578OD0+8TSTYZSoyrjY/bbPSud1299XO/8Morh05z2byL1q590kZThyvfPXgwnXdnoYOAQEBgVSMQCH1VT/8yNn4BVf/4/pHeP/7RE294cKT+EamuezFxZ4UpIc4n93dkQ3/8kUvO/95VzI0FHPJEV2PlshXu7KAxKp8oDJmAQEAgIDAHBMwcrgmXBARWDAJ/v3diw8d3HbvtoePuQxmXLqS4HBmXkx8fOtjp+a/ffdHmb16zgaYXy+CuNI0lNt3VhCuLNUboNyAQEFgdCARCXx3zHKw8DQKf33286789sOeV9x+d+KmsvOYlWdrd0ZCIIqJGlRvfum3Huv/7my+/6CAz+9NcviBFrh4lsTU9YigQ+oIgGjoJCKxeBAKhr965X9WWfxbb7H/w0LEfe3CQfzaLOi7PTRwbEUobU9MlN7XztkvXf/xXXrK1H2SuH3RfNKwSayKT2A4xSSD0RUM5dBwQWB0IBEJfHfMcrDwJgTsGBjp+5+79L7t/YPKDvtT10jzt6nI2JVZCJzfQ4ep/+dYL7bc2M0+ddNnCZU/qCQ8MTMamjnxKIQQEAgIBgXkgEAh9HuCFS5cfAn8/ONj5J3cNveGhYf4H9fL6G+ppb5pxTJGvk5sceCz243/2zis7v/XKvr4l+U9XnIi3zNaISZcfmkHjgEBAoJ0QCITeTrMRdFlUBL505Ej1P3/zqZc8dHj4vRx3v6SW9PROmyq4NCbvGrWU8++8fUfPJ26/4cJD8JwXdav9ZENtZNLJeqNrp+AF/skVc8uHqwICAYFVikAg9FU68avNbN1m/717pt/2YK3yT6c6el9eT2zVcE6pq1E0NTwd10a/+/6rej75yzefvw9kvmT/ixr+ADli7mw4d3Ff//Dm1TYvwd6AQEBg4RDAerJwnYWeAgLtiMDOfVL64+8cvfHeo5PvkaT75Xmpc51LYvCox1Z7TpWIxntK5mu3rO24Zyvz9FLaMI2nB/jlFZPYbc74TUs59pzGChcFBAICbYtAIPS2nZqg2EIg8Gcg8998cO9rHhyP/mkjLb2sTpSwLZP1CSXekc+OHU/9+N/85KXrP3Pe9vWL8mtwZ7KjLCLsvYki2xtz1HWmtqEuIBAQCAicCYFA6GdCJ9QtawTu2CXJ/7r/gWt/dGTwXc4kL6G03CtRRMSWLMVEzlESyxNvu2rtp1//gvWP3MgL+x+v0FmE3IvzYhwzl0YnJ1f7r8WdBWKhSUAgIPBcCJjnqgjlAYHljMAdIva/791704Pj8UcbnZveNF3u6axRSigm73PyXCM/PXR3tT7x327qTX/QCjJXfDOHbQKWnNkm9bqr3n234ElDa4IEBAICAYFzQyAQ+rnhFVovAwREhP/oU49e9MDA8Z9wHL/YJx3r6mQjD8/ceyEhR7nkWRLzd993zcZvvv+CnuFWmVWpimeBGDxtGO7YvLk/EPpiTUboNyCwwhEIhL7CJ3g1mvf7ByaufECq/5IqW99jo7VrjShbZpQyttiNkJkeHShPHPn4L1zR8fEfv37T4VZjMOgeswAAEABJREFUxNYykS2LN93Hskr4PnqrJySMHxBYpggEQl+mExfUPj0Cn+8f7/uT+468brLhX0EmXU+Mt+ReiMURzYhlP9hB8pUNZB9s1Vb7rPZeRLyeMKVkqINL9URPgyw7BILCAYGWI2BarkFQICCwQAj87ZHx9R/97rEP7B5NfyaKezbXibhBDTi/uM2x3Z74BvHIwcdLY8f/8Beu3vDln79x86L/tCs9T1BCZ8G2gZiU2GxInO19nktCdUAgIBAQOC0CWOlOWx4KAwLLCoGdAwMdv/WtQy/ae7z2pnJSuijPJSZjyFhD+rkz5xw1GlkjiZKHfvJF277/azdsOt4OBiqhC5EnppiN7RGKOtpBr6BDmyEQ1AkInAUCgdDPAqTQpL0RuOuAlH9t5+ite+rVj0altbfgXXQSk4PDW6csmSYXZZRQRJXM3dWR1f9wS+fkg8wMHm29XZlzHh66Jzx6MNtuYRsIvfXTEjQICCxLBAKhL8tpC0rPIrBLJPmlb91/9WPHxn5McndZHCclYyxbZsKLc7i+jqzFbW7stGF773tu2PKjX9qxA7vx1BZhjYjg2cJBWzKR6ZDIV9tCsaDEakIg2LpCEMBKt0IsCWasOgSUCz/8149csocq/zru7XuvTbt6nOCWjhOasp48pORrJEOHjvLxvf/r5y+O/uy3rt94rJ2AyvM+L2wyMfDTjSl7MeHHZdppgoIuAYFlhABWv2WkbVA1IHASAl8+erRv38TUu/N6/Xpro3IURQYpZVlObJq3tse78yg2I9XE/oAaWx9luMMnddHybL1Onsk3oIiDbqVjw1NlfVDBeYgBgZWBQLBiyRBornpLNlwYKCCwMAjcMT7e94s7J381TjZ9xHSev2k07qHhyNB4VKfphKhhq+SlQjLhnipNT//Jz23r/sztt3K+MKMvXC+T28h74gYJg9DxDt3Thv7+/uClLxzEoaeAwKpBIBD6qpnqlWPo/UeOVP/V3+x99fjQyKsS7/rgjRsvQvopNxUD71w9cyKmJCkd/+D12x67/dbtI9SG4UoiZ4WnoX5mLHdih2H9OHVU2lDVoFJAoB0RCDqdhIA5KR+yAYG2R+BLR6T6ti8de+8B2/WfhtZue+HhnvU8DgLnKCOSjMp5TJ11T2umj+al44/8IBo9/Jvd6bEvtath2GZ3m9d2jLpcpjxhmyFJbqKILm9XfYNeAYGAQPsiEAi9fecmaPYMBHbv3p3+4ud/eOPxkeHbuiI+L03TooU1hsRLkffiyflcxJjJNC7t+tD123bdftVV+o66qG/HA9so86K/gENkre3iKOluRz2DTgGBVYfAMjM4EPoym7DVqu7dIvHbvjN+86C3v9PZt/m1HJeIGnUy+p+V5Y6SLKKUYsqsfu98uBHVj//wPdu6/vfv3rzl8XbHzEmWMzG2GKApUzV3PnwXHVCEGBAICJwbAoHQzw2v0LoFCIiI/ZmP3X/VSCYfKRu+NEpL1MhzslFEqCvEWoPUk2UkbIbiOP5ub7X0cAvUPechjY+wpeCV0IWYy4PDk936AHPOHYULAgIBgeWEwILrGgh9wSENHS4kAiBsvvIv791+NO76z43S5vfnXZd3DMCBHS6lNBVje51zMpFQJDmVXQZPfWoqnRj9yjvPr/7J7TdubqvvnD8nLqZeF5IJ1AveqXd44r7uoaEyzkMMCAQEAgJnjUAg9LOGKjRsBQKXfuz+zcO5+UXXqF9q4ogyl5MxlmIbk8tBfYbJY9udyJN3cNudP9hb6fje77/soqdaoe9cxpQ8rrMT/Y9iPJEk8NJ7IpeGT7rPBcxwTUBgFSNwCqGvYhyC6W2IwLdHRnqH0urt9XTtL2TVNRum4oRqMVHuhUw9oZQrxCYnb2pEPE22NnKkc3L0zz6wpfTXtIyCRL7uRSYFbjrIPMUDyza47OuXkQlB1YBAQKANEAiE3gaTEFR4NgJ3DkrnT31233unG7VbjI0SY2J44J6cOLKRhSPLJPDQjSdKiclnjTyKzOGXXLxm1+0v2Tr07B7bt8RmeFegTyQknoki2LfZCfW1r8ZBs4BAQKAdETBLp1QYKSBwdgjoB8Le/bl733jYVn+tUS5fVosr5KVEBLrLo5xyk5FkEVV8Sp3TlrpruY8bU/3Gj3zyJVuzr57dKO3TSuJ6nSifIMbTCjNxHG8ensi2iIhtHy2DJgGBgEC7IxAIvd1naJXpBxIzH/jEgzcempr+QMLm/HJSJsJedCPLKIoiisjgHxN8dnjrAnSYxPFklrm7X75tw3d/qY3+JzUod1axK1vnQOMNEsJ+A3JseoTtpocGCcafVRehUUAgIBAQwNq4QkAIZqwMBG76+13nHa7zL/HGLW8eTzsjm5coAm+bOCYHuoszS3DQ4aU3qB5l2GxH7aQ50JNN/u93DGy9azmiMLSJcsGWuxA1PBvyxlSdkfNTO92zHO0JOgcEAgKtQcC0ZtgwakDg2Qh88cDomv5G6ack8y+MTMyxsWgEEUP67pwtkwHrWdC4z+t4Gs1FTDZRSd3D77/2gvtvu40dLcNwJZG3RupEnDMzEXHExm6BseE9OoUQEAgInC0CgdDPCqnQaLER+PxuSX/hW3vfeIS7/rHv3XohZ8y2kZP3CfahI8oIeeMp8oZSUsnI+rG83hh88pL1tc/99os3PbnYOi5i/y6JuUYkDSK8QoAQmz7DZg2FEBAICAQEzhKBQOhnCVRotngI3A4/+5/dt+vlR6ey9yQk3S7z8M7LZE2J6r6hVE4xtty98+SNo9xnFOFcPE12cvadl27s+s7iabf4PTOzdFXihghlwhiPmbRocirrFClKUBhiQCAgEBA4MwKB0M+Mz5LUrvZBvvHphy98arr2i2nHutdZ9nEalYgbVSJXpTzyEAeCY/jlQs40QOoZOYLj7uipa7rtn/3r69fvpmUeWPCiQaSBNwpUULgxHaMTtd6HiOJlblpQPyAQEFgiBMwSjROGCQicFoGPPTmy/VBNfs6k1etzm0QO/JUL2Np6qrkGsYG3KkL6nXMG0zFo3ZB3bmrscK9tfOX1aysP0goI3liPHQenn+hXMYY7nZhNnaOjeLJZAQYGEwICAYFFRyAQ+qJD3OoB2nf8z+4f6f3te469oz/vek+9tG7LtC2bjMvUgMqZzUkSMDsccQbTISEjIHfH5Bv1hq2N3HtFxf3Zr75k6zSaL/toLQjdkOgfpIG9xtgOZ+x2qlHvsjcuGBAQCAgsCQK6fizJQGGQgMDJCNyxS5L/3339bzw80XhbaswG49goYVsSsizETMT4ZyQiViEEdmQ4dz6fPrqhHH3jxVdHT6B0ZUTJG975mhrDzJpE8NLXOo7Cf6WqaAQJCAQEnheBQOjPC1FocCYE5lr3sb2PXLVvvP4ejvmmzNjU+piMJzJ4O27hjjM6ZpwZn4DQ8RoZBayETo3c5ZMP7uiNP3H79u0FAdJKCB7vz73UBK8XZszhyNh1xLZ75jwkAYGAQEDgjAgEQj8jPKFyMRD4s0eGt+1p2J+WqONGOKDYWI9A4YaMieCTC5E4CBV5xvZzIWghAi6fHt13fjX+/OffePEhWklBCd25OpPaD4FtJrIbxurZOpA8HmdQEGJAICAQEDgDAoHQzwBOqFp4BP7oiaHu/3jvE2883Eje3LDdGzxXweQJOQFncYQBQebw0osbUzzp+2QlOQNCJ2xL57XRe3Z08N8xY18erVdKNNZmJL5Os+wNTx1b7usmJusb9uyhZKXYGewICAQEFg+BYt1cvO5DzwGBpxG4Y9eu5I/uP/iWw7l9j7elDd6kJveWGEQOQiPn86ZnruReXCaoy/FOPQOxZ568H9xcrXzhc2+68mhRvYIO7AWErr8Wpy560zDDXPVCm0rrx8In3ZuQhGNAICBwBgQCoZ8BnFC1sAj81QG69snp2gel3HWTs6WyNwnYS/9bVJ4ZCAROhhhCRWBqvjfPyFLmXVbbuyNOvrBQ3nkxRJsc2EnGIjVY7GdVwnNNQpHZlE8lXbNlIQ0IBAQCAs+FgHmuilAeEFhIBP6/h4euenTC/gyZ3huwzZ46YyhnT1lE1ICwb1CMcwKZO88gciIG3XvnKaLc16dG9m2K8zu+dNuVQ7QCg7CbZqIxJmxTiHI6k7BhsnarL/t1FEJAICAQEHgeBAKhPw9AoXr+CPynxw6v+/OHnnrzkTq9TkzPGhA6e2Zy7Cm3BGEyeEduQWQebikxbkvkSRz4zGK73bksqz9yXkx/R8smnJuisYmmWPwQiW8wSXGxAAdj7QYiG/7XtQKRcAgIBATOhIA5U2WoCwjMF4GP3X+k+hcPDb/tyWn7DmcrmzxbY8BXsbOU5IasF4ijzDpqqDCRpAk22BvwzDOK4Z1PjU3u35Lav/rH77h8gFZoyG19irwcE/FTBaEDB2IcjOkZGq31iIhdoaYHswICAYEFQiAQ+gIBGbp5NgIgIf7dR/uv2zeRv82UOq+QuFoCoRPjX+SZVJTc1Tv3LORw4rDL7BikDu/dsiNxmY8MPfzRl2z9/G36Qv3Zw6yMkqE1daL8uHgHQm+ahOceImOqY1O13oMHT/2ke7NFOAYEAgIBgacRCIT+NBYht8AI/PS3n7p8ty//dD3tfgmbatnAycxB2uqJ69fRIu8pdZ4SR9hw13fqlsQy5S6nKIooqtd9bWzogU0p/+nPX7RmdIHVa6vuLr4YWxEio975yaZijI13JhB6GRD1+c7RUrM8HAMCAYGAwOkRCIR+elxC6TwR+P3dx7u+c2jopZmJXuqSyjqhGGzNeG/e9MQZ7qeFRF6w5a6DGRAYbkcREosURcblPia366de3fNVnK7oyNh96O6pTHov08wELJrmsjEpG7vO5EmlWbIUxzBGQCAgsBwRaK6cy1HzoHPbInC7iPnzXYNvGMjLH4qo4/yUyuxB3h7etyUDAmeqxxlNJQ2qR0I5ttmTPKVSIyXrDKWMVpmXzJuDfaX0L/+fjRtnvNa2NXlBFEtiW/fisfWu3cFDB2bMJiJrN+SUd2ppkIBAQCAg8FwImOeqCOUBgbkiMHX34R1PTdZfY5LSFZbTss+EjLWUF59cJ1A6k74vz61HSthuZzLe4p06uIvQrt4gdkJeeP//+8rL76RVEmIvDSKpEwuoXI1mHNjEUbQFXvuK+V/XYFSIAYGAwCIgEAh9EUBdzV3+waODmz99xL9nPO179Vjc1ZnjDousBzmrKDIOh5zIp0SuQuwTnDM5m1FupykxdSrnE0RTw3sqMv4nt23lFfHfo8LI54+GjovIEbwzd4RNd8ajjuAhKErTS44OZVccPiyV5+8ktAgIBARWKwJYbler6cHuhUZAf9r1D+89fM3+oamXO07Oy22iH2QnBjkJnE5mQ4XPiXOCJ04UgbdMoYawkBhP4jOKyVHeqB/tKsdfLypXyUHIjpPIcS+CJx41uumox8b0NYSu8en0Gi0NciYEQl1AYPUi0FxNV6/9wbmPEuoAABAASURBVPIFROBbjTXXHMr4J00UvaBMlFSdo4QMMYi8oCa8K5cir2V1Iq6TcJO7LDzRGNvslDlia+G/Z3f8u/dcuuJ+s53OEKSW18nTBBNleNI5qSXbKI4uz220/qTCkA0IBAQCAqcgYE45CycBgTki8CcPHVjzhQePvBivf28op6Vu3FhMDmQNz9xhD9kj9R4eeMHsoCwSUs+dWJojItHSyBgaz2Wgp5R+fUV/77xp9SlHSesNw2YCXnqmWJxcaQxvPjY4rP+VKqA9uSbklxKBMFZAoJ0RCItDO8/OMtHt93fvTv/jI9k7B5L1v+Aqm7ZPc5lzYWJmOJzgdbEE55wsyNuIIfKWPPKOM2I4o4YbsJTJ+IiMy0d58vhn/seHrthDqyyko31K5KN41qnh+eaE9YCSxPCaesOtf/JJbHqcqAmZgEBAICDwNAJYXZ8+CbmAwFwQGBwvX3Z0ov4qY+LzycSxVwYCmRsIgbLZGLLWwvFseuigeioC61GpS9BK80yT0/XRalL+6huJlOW1cNVI8eMy2HL3Qic+CKifPRAwPKCqOvZ91DMSfmBmxd4RwbCAwPwQCIQ+P/xW/dW/+/jIhf9z99QHpsudr6jHaTkzMTlIxglNS0R1MuQgotvvPgNenvSnXT1FoKmYIsnIohVLs10+Ofzx//H6iz/PzILGqyrCZre2uzyMVxXjhmA+Ioi8iYHlKhtzKVEp/EctTUTCMSAQEHgGAoHQnwFIOD17BO7YNdDxB3c9ceP4ZO1lJWs3eCLO9X05XExiJmNt4ZkzM7xzoSSOycBbpyIoVakQNY9Ejijr7en6wbv6aIpWaxA/AfgmBGQO0E5GwWDz42JmHz7pfjIqIX/WCISGKx8Bs/JNDBYuFgK3P9B/03jc+Y/T6qYb2MdGf5edY095KpRbAR8J3ol7YvFEbCjzDNIW8jhnJxSrb44y8Y4SnsymRvvv/NKbL9rJYC1qw/D7n/98uthqseURyv1x9uKYiZiZPBFJFDEn8aX7DwxfvFtk0fXAkCEGBAICywyBQOjLbMLaRd0/fOzYeWPZxMvEN7Yzm8gYSwbkA+opVFQHs8jgAF56+miYYnjqKKA8zylC3oDsJxrZWLUSfbu7l2pa144ydGTqvJ2C9wiLqByTm0D3w0Kz30XHGaJ67MaaHjJ+azQyUkZRiAGBNkIgqNIOCJh2UCLosLwQ+N0DB8q/fe/B14+naz8sScfmnBuUs6WMSoQX6GTxvjySBjHejVsVvCd3HFHDJNQQhoeu3jm24JXQPZHzaNX/5F299eE/2sFcb0c07rjjDjtUn6p0EsyixQuJM6Ps3BE8BNUsHpCQEjPjjTpy1la9jS5nLvUungah54BAQGC5IhAIfbnOXIv0FhGeHkl21Bruddj33WysZTAOueI75oIsTgXKqSCZjc1TEJMIZVlWePPWWgJTafRRtfPx//7T14/Mtm+3dLfpvLIj6h68gShfTN2qMj4pxAPi5ZT/kAYeOym42N24UMR1UwgBgVWEQDD17BAIhH52OIVWMwj83u6j2//LA0MfyqONr3LSl0xLhbIohfdN1AA/R8RUdkSxF5A2aEg/rF6IdmAoiWIyeJOuO8oxOyrDe6+PDj26KfV/+hZ6+uta2rpd5P/70v3VA+PTN/7UO26ZZlZjFk+zTZs21cX7o15kDA9PxecQNGWgJgzsomjroYPH+1AGtCmEgEBAICBwAgFzIhcyAYHnQeDP9u0r/ec7919RrzdeHIn0ePjWbAzehWuOyLA63ILSmY6kmc4kOEGdfiCOGbTP5OHV5855XHjwP//E9Ud5kckSCswp5jR9WV2MwS6EzKmDc7gIGLg13eVjPvejJ1+mA4PEyVjTC8Q29/cTNkhObhHyAYGAwNwQWDlXBUJfOXO5qJaATHjvVOmiCWPf56t9N48naVxPIsq4gzLppQj8kvgaecnhqettZSnCu3ESS44ZujkC9RO7jGKwk7AUe9cTtanDVW587OpOmkCjtowjDX+pSdOJrizD3sPiq4hHh1H2bgwQiQA6FWJDHhlr406Kom3cOVFdfE3CCAGBgMByQkBX3uWkb9C1RQj8l10D6z/+gz2vjzl/obHWQMAxhpx+AM5ie90ICGdGOQFjw08XiPrsWgpeKrK4kBjkpCe4+UBRcnRLZ/zAxUT6qzPatK3kP9x5Z+fAyFiFbfTk4U2blkRH7w3I3A0Ds+IBghk5RQWwsjEJx3ZH5lyXFgUJCAQE2huBpdQOa+pSDhfGWo4I/NHdEv/ne4++6Eiy7R/VOi+9uGFiytlQDuI21hFTjZx48iYhRrkFkSuZe7iYKgQ+MvDULUQ8U06GIrIUZ/U8mTr2l//p5ZftZWZPbRbuELEjU/ZWb7zpS3j3jcxLQuiVUnY4ksYTTJm+sweaFsJkga1jpqhavvKpQ1ObBfpRCAGBgEBAYAaBQOgzQITk9AiANHis4+iWCfGviNms896dpiFcxzOVqh+O+mYrBp0TeWy918UfvXjDhu+NbsATAbVfeOIbP9p6fDq7FBsStdfdck19qTT0x3trzvNxET8FJj8xLJ6fig/JGcNbcvbb+gnvOU7UhkxAICCw+hA41eJA6KfiEc6egcAnBger/+nOgy+uefumUuQ6RU4ldEb7WUEWUc9wW2lSSHEoeAm8jnrBO3SIq7usMfLDf/6yvidvY3aoaLtYN8lLsji9ipN0YEtf35J45wrCxRdTFhENivNjih4BPWbNYd8DrI5jN1m6bGpiokPbBwkIBAQCAooAVl5NggQEno2AYEv3wMHGRblN39RRqmzy2DvnyIBeTmoLgikK1P0uRIlH65tpUaSnEBARuEnIYCu+nmfHSrH5foettuXvtt8tEk84d1FOHAlF/XjHn8OEJYmM1w/lrtIx7/woAGuOqUAipwnq4yiyl/K070ZRiAGBgEBAoEBgoQm96DQcVgYC//6+I2t+657xNzaitW9qxB1ddRuRAxkLiB20TAapgaknS5PGtURzSv4QZPVT7WgO7ndkJaPGxOBjV3Ty59+4htry0+3fuGvf5iG2VzeipHR+b9dxkKiDqUsWTU7HxLshxiY7hFg8KX6E9+jElmySXnj0+ET4PjqFEBAICMwiYGYzIQ0InIzAzn37Sv/1/mPXTXu5NYmizgz75R6EnnuwMxoqySBpRkEyK8hqFLCPaOZkgTfPeCDIfSO3bPZ+5CVbB5nb78Nw+/ZJaZLkxlKcbrLEk7fddPGSkrlCJokcFyeDyGfgdCREwEoPhVhrN2YsWw4SJRRCQCAgEBAAAsuL0KFwiIuPALbazT3jndsmbMeHo+qaW3IbR1lcpilOyRnlD6aTCZ2h0qwgW0QpWujtpTVUnDWvyakm48OVSD6/bbLalj/1+pmjT114PKMf6zDl3iqVDrJzDVriIDUaApH3s0jN4EEIeUSBFkxe0TS2W4gv8KOjJRSGGBAICAQEsGsaQAgIPAOBTx+j6u/ec/QlruFemMRxhbDN67wHoWjDJkETSIWKIDieJJpFiUY8GCBBgW4Xg5QYYimXRlY//ML13U+8chstOVFCoeeNtcTeaBJ7eWTYpLF5wtVqS/YJ91nlOn33NDYz+kX8xCmIA0NqYp8i3SJxHAidQggIBAQUAXWhNA1CFDAAAiJivn14+IJpk74trXZdWDeWMhCI4L2t4PlPsPVOSAllNBvAPHjJSyqgbyLBbaUZRpEK3pzrN6ktHgqMr9cjV9v1T15w3gC34Xb7bpF0zNpXUTW9HA8xeZr7xwb7+mq0xGHbNqrHsTkoWT6kD0JAlAwDTOihcyDMlqNo6+RkXkVRiAGBgEBAgHSdCDAEBE4g8Dv39K/5iweOv408X+XFgYcFdNwkEj2qFI2VsE9ktI0KETOToJGIEOOfkpDVlPRxwFNtcqK/bKKdXenkKLVh+OKDBy8ZrbmLjJg0z93E+T3lkVcSLfk7dGaG820GyPsRJg9wNQoQYyLmAs04js8fPDIePhhHIQQEAgKKQCB0RWEpZBmMoV/V+oPHxq6esJ0fpqRykRgGdzARR3C+mSz4hImJiudARjobUaFZLTJKNnDS9RwiDlyY58TeUSQ5yfTUEzdvKH3/pevWtd2n2/EQYoZq/kWJjbcasTZzbuClF60bY5ArTFnyiHfnx8S740zeMSjc4yHJQwtmPGfhNQg89C2g+QvDD8wAlBADAgGBYmUOMAQE8H5c+LP3Hegbzepvc+Q2+iQhsTEJiIPIE+M9uApIpkBL9FgcNKMCaiEhj3YCISV3rYdzqU+NEZoY4nopqTzw4evO10+3ay1K2yf+jweeWDcylV8XGdtt8KfhyR+IY7fk2+2ziKTkhojkKGAtPmsgIHTEmWpoaG0vW3OJHx8vzxSGJCAQEFjFCJhVbPtKMn3etnyBKPkfDwxcP+Gz10mFOxuxpQZILcf+OWgaOYGHLkU6O5hytoqeoxnpd821raaMCr25IjwQJPDaLUg+y/LRShJ9MxqsDus17SZZnlxq4+g6y6YDzzCZYfN4WuuYbpWeeUnGoMdBkPiJD8YRnpTwjESClMgmUZJeOTHluyiEgEBAYNUjoGvuqgdhtQMAz4+/dl//xlGO3h1VKud5dpRLhhfHEXkQMrxEMoYLCgGTPAMuwfmsIKtMXgjyaFzcYN4TyDyr5Xn/67Z3HX7XlZRpbTuJ/kcso1P5NbGx58Ezt9PODTsr+/J1tOSfcJ/F5cq+vunImoPYdj/xeQOFVusVcQ984yS+cPDo+HrModXyIAGBgMDqRaBYb1ev+cFyReAbT1L6v+8+eAtF1debUke3ErqjnChKse1uieBdM8hDBXvzuIQhJ0ell+a55kSrVWauYe8JTwNTNvaPvGNHZZhb9E6azhAm7juyZgyEbm3U60h40uVHLu3tOHAlte7hAzi5NI37vffADM9WzMTMhRV69HDdTWQ2NYQ2P0kUFxXhEBAICKxaBMyqtTwYXiAgIvYzR49eN04d743irl4nJTjk+hVn5QfGu/OiGcqQgq0tJHJESu658eRAMCIR2jEZEL9BDSujiwEtGspZL8XjwfTo0bKb+lpH7I9RG4YjE9lLTbnjeuG0DMV91pjef/OWtGUfiJuFSCQ7KrkfIPG5QaEB/gwiV4i9MeTxHt1buah7mDBpaBBiQCAgsGoR0DVi1RofDCf6jYcGy3/80PBb4461L865GhPr15pT0HJMHuQB7iAGcRDyRgzeoxuK1eHGeW69uo1g7BhkzhCh4obyTOqUezYE7sf1Qnlj/MDLt5Tvfk1v7xidGlp+9rH7j1QHM3q5pB07ck5iEs5Z8oNxlrVsu30WlJLLj4qXA+R98YtxBhPCwB6RxFrKjU05SS49PDoYPhg3C1pIAwKrFIFi/V2ltq96s/W98Z9+f+C6hvOvriRJF3lHBkwceSIDb5uL3eamF+5A8QqYEgn4mvQUDjoRoTHeuWu5gM7BN6R1Fpkoz8k6h84iHyfJEh38AAAQAElEQVTlx161o+sYt+F2e+7kAhtFl0D9iichJ64WeTmYJknLPuFOM6HK64axi7LXCY0JE7QjhRc7Ikg9FSFJ08v6R6c2oZ0tCsIhIBAQWJUIBEJfldPeNPqubzzZOVKbfEN3Ob0SZBAlFreDkjCIwoKhDeUkLOThaXvmgkj0INy8Hq1xKmiDC1BPpHzCZNFPhJoIZA4nnhqeJ+MoemBj4kZoqcNZjFcjfzEb3krMuvkAZ9jVUpF+qdeLr4udRReL1mTLFqpFkX3SiT+BHRP+YRJwxLhMcRJdwszX9PeHbXcAEmJAYNUioGvyqjV+NRsOArd/dzB7oyTVt9lyV7UG75wjBiRCxhuynsmKgyfoCCUkzORQjWLyhCBctGOkwg5lIHYQDRxzIhwsPPzIeUqZJMvrx9580Zq91NfXsq+A0XOEz+/enQ5M+es4ijfCQ4c58NCzfCgSf6DW19fyLXfGjkaamn7vZdgTYD6NHWLNGluKX3YkP9ZzmupQFBAICKwSBAKhr5KJfqaZ//Cuo2uP1RpvqXT0Xt7ghAXvyXOfwbtm8JohA6K2IGZDcNVRIiBrJXMhIlThSGhjUIoso5SRoh2jBO98yXihCNezSIPIH37J1o6h2xjMr83aSI5ORJvG8/xKY203CJOgPuVZY8hkbuBG5uf7eh0tRfBejmLLfRD6OR2PcZgVZDEfHCXl5KbhodoWEbFaFiQgEBBYfQiY1WdysBiLPn9uz9Ct3lRf6ZOOqAGa0F+Fc7GhBgncQCYGaxvwtJKy/qcqhHL9VHuOQkG+QBFtNItmJOxxHSiHuaB1h5StpUajNpTmta92Ch2mNgwTJrlGomSHNxyrDYy/CNdwwzddsKFtdhO6KvkAER/FA1VDsda5KAQnQgzsDZk4vsBbueogUUIhBAQCAqsSASxfq9LuVW30h752aM3w9OTLKtXuzdO5EHFEeN1NDZeTtyAIkAQh6M0B/sbWO6FEyKPAM9oT4Rykj6PmNClKUSeEnEYwIzOj32w0Ndm9a7PKELVZ2LlvX+nQ6OSl2JZYT9bAOiLscJMXN7VlTZJTq8PM+BevWTOBaToi3j/9kAGMuahvHsmYDmPNVfkIpUVxOAQEAgKrDoFiEVt1Vq9yg79+vPaOpHPdaxuUExgMFAwSB/l6ivH6u3lLeJzn8LU9Mf55Mtg+JwS0LM4FeRVCGxK9Rs9yQgdEnsmjvo4ddu+mH3vHju4n33gxnH+UtVPcP1q6aFLiG02a9jooJtCafV1ROBZHCV4VoLANIjP7zkQOiXNjDExFZ4y50EzEA3JDnq2Jy6XrB48dOU+kmJCiPhwCAgGB1YOArsSrx9pgKX3kM4crxyemXxWZ+ELl4igGiYMgCr42lohViAR8IWyQMghcZgTVREWeisA4MjH4g5FDpqhjHFUyQw2bmIev2pDq/30u2qSdxEV8kcF2OxQrqb3ETOSdWDbjkrusnXSNiAbgoY+AzL3irLoxQ19kGPNEyEdpcsXYVONyFMWQ54uhPiAQEFhhCARCX2ET+nzmfHF0/MWUrn3NNKWR8gBRnRzuAg9CMI7hiZuCL0By1DBMIGV4f03/NdJ63yQREAs51lZCWsJgRE1JCEFQ5qiR146VEvPA9ryv7f4zFt1u3zPmruOkvBX7CoZgKyIIXWqA4lhnqb0InZ3vZ++OkLhM4JUT+xmcdb4YeEfENl4blUuv+u7DB/XXgVAfYkAgILCaEDCrydjVbusbP787HRg+/opSpXudiUsk+OfAW8rRrOwuQAjCSIiYhCGEwEIMMcTFP8KRSIpEuEiKEmSLE/X2lXS85ONvvqjj2Jt2cMu//kXPCPsmkr6pjC611nYzXp/DmqKFF5pC/phpNNrKQyfKDoPHn8Sc4T06jjiBnoXOzYPBlLBN4uTmWub1R2ZQ0KxpyTEMGhAICCw5AuGPfskhb92AT0z5VzdK5bfrz59lIDEGGxcC4mZQcuQN3h9byk1OwjkZvGNneIKePBXkgXrCNTkT6UMAjrjKEaGdlpNLCg/f4Bry2ZR1tV1XddijaNB2MbPJNZG1l1riyAALrwSJJxHvbE28DFJfX9u8Q1fwRreu099z3+WdG2l6500P3eJ1h/5mgOLv9HVJmm6rk7kU11hIiAGBgMAqQiAQ+iqZ7HfddaC8f+D4K9d1dlziQFw5CIzA0gxKZuYCBcZ5M0ME3kYNQVCIahyRZ9IUGa2gZhCcqjBSvZ0YFws5cbWY5dFyR9R2n26/68CB8t7B0cujyKgnC7Ph8TYtoyz3Y+LNyJWkTylNC9vheCtzXk3skySiry8EICMKQXlIE3PSGcCOg7HRhQeJIlq5IVgWEAgInAYBc5qyULQCEXhw/8TNjaTzxxppd2qjnJgdCAHT7yLinMmSBiEvDu/OQcjGUYzteP35VoY7LmiRwwN0xpIjJg/RK9AJqDBHgr58ijQibzLK3fjxEme71tJ02xH6kankkrpJXhhZ0xPjwQbvp6lpjqHMyfhrL+udYGZPbRYSw3uxk3CISIrXAQLkQeWF6iKMFHPA1sZp8oI9Dw12t5n6QZ2AQEBgkRHACrDII4TuW47AG/Hu/MlDB1+9pqtnawNs7BwImIgsyJnZEiPPOBrDoAjQBIMykNObQ0twWrQg1hwEKfgDZURaTwgoRb55BTLowNUS4mPv2rKlhuq2ilBoOxtzCewt2QIDhspNybyf6O6yBWG2ldJQhnM/kDvahxmawumJyMiJCBnAj9cFVCrFNzay7EKUWVSFeK4IhPYBgWWKAJaAZap5UPusEXgytx/M1mx7jzNrE+/LZCguCKwGBmhEQsQZ6LtBwg2UO0qcocjHlHFCDkK4wsAnN1SnSOqU+Bq8dw/STrAvXUa7DsoZtxKPk6Uxqsg0lbPpJz94+ZpBZt0UprYJ+tvt9w6OXU4lu2WKrZkgWGYS8rA5B1sy++NdeRWc3zYqn1Dk6vO7Rzpivs87f9zhiUowf7nx5PDPiiPrdS5hT5Rs59S+7UnCRJ+4OmQCAgGBlY6AWekGrnb73vqZw5V9Q8Ov7Kp2bHUefA0CYyQqwgwSB0I8I5ogzzP1hPpZYa2DgMUJVxXSvFhvIRVUgrsZAs8wT6L4YF9PdQSlbRVr3NmdibvAGuoiPIQUGEBDRp6gPDYparFgHx5l7RYZ+nnJn1RCh67uhH6YHNSRwEsvythEabl005MPHFpXnIdDOyEQdAkILBoCMyvxovUfOm4xAk/VRm/xLr2BbRJnVGdj/fw04hk6RwoeITiIEE9MngwIxQhRrdEYQ/axxHn9ANf8xlvgqycbbotnvsCzjYUM/lkSPOkYPIhYQ/oRgYZLYcwCj7tQ3UV1+0Se5U9CaWypeOiPnnUiMB+CWRDN46kkTuLLG0SXgOTD3zggCjEgsBoQCH/sK3iW9d35vqf6b1lbSrfATGYs9Fj3kZ1nLDphYnQzK8g2I5g8d76OJkMb62vabuvaWLORDfVBWaiOiIy+dwbxIUdkmLPYt6eHrgrG1cbxhpM9XmSSREuKAxEAp5nAzBRZ7jOWrkVR+BsHCKsmBkNXNQLhj30FT/9Iw7wzWXPBB2p2XSXjhDlmauAd+PxMZlLPttmHJ6acTPEO18NDZ7zHJbJEYz9/w3nHbrsKL+WpfcKX7j9S/cGh0StNUt3iOCYvDP0N9GfoLkTiHXa1607a10O/duPGya44eoidG7H63FEIVCfCLAjmhojxxOKZbJTEL77zwac6KYSAQEBgVSAQCH2FTvO77jhQvndv/3VJFK0DbTEcZ3LOEZv5TTloD6TBQI2JiyMVaZNSSJnQ2Tg+XinHk9RmYaKS9zKZrZZNhTQ0jSEDTJgZdnmCMVkuypLUtsGS7IOyx6EgHkB0FpBDZEZebUJeYylNXjSd80UieHLRgiABgfkhEK5ucwTmt7q3uXGrWb2DVLup1L35xyeirqpPStzAYp8pIDbS4zxEiS8Cn4A8lL5PiMDL9ZTn2ThL9mBHo9Z2vxA37qNL86Ryo48qnZ5iYgM7QIBMBjxOBKOcEannvs0JvR7vBtD72OcN/cyCzkSTsw0pdXsRchyRTZKtcbn0TlhmICEGBAICKxyB8Ie+AicYHpk5NjF0c0e5tM5aY7KsURAWM8NL9/O2GBxY9KFpU5pHLQSX6P/ncixlntDzdpHbRcz39g1tZmP7gE+hFrM+nBTZmYMIttxduSJq0ExZ+yXd5ui4y9wTeOzALsjpVVUbmZniJHrpN+7p720/K4JGAYFnIBBO541AIPR5Q9h+Hbzqc09cPxitf8skVapiE3Imgx9dI+KIvMxvypU+8H6WVJpPCSjhGSF4hvn0uMkmHi0l47olTO0Srnl0vDcqVa+ypdIm4RiqG3JgRALpCXnipiihNzLRinbR/Nl67Nixo16J/UOW8lHysMILiTQFU9C8QBzlmJa0o3pFXrKvaBaGY0AgILCSEZjf6r6SkVmmtmFhN6N1f6PL6htFvPHewRIhZibDhqyxNP8gRRegkII/9EyFmmeNrsROVrdtK3b4i4btcEjqa61hvD/n8qnqNDUvygTBSw7ETiosatruIMY+LiJHmdlBivmdTQtlmYvZIPFrjY1ejLbhb70AJhxWKQKrwuzwR77CpvnNn39y/UNT8Tt9qfsCjhjsnZNEDot7gyQnYj//KTfojciT4OWtx0OCiiBv4dimef3Qu6/o67+NGbxIbRMGam5rjcw2x5F+5ZwMKBucR7phIdC9UNRLjrJaop8eLAra9xDldk+eu8cxDzUzq3+hruDBjUDwmHoylImltFR+zdfv699KIQQEAgIrGoH5r+4rGp7lZZyI8Eg9v7kUxRfEcRyBcomw2BtjSJTAcGAIzSMUft9sH+hTu5pJCKmLIjtYsTKm5e0id98t8X1PHd8EGNY+rRO0hcbNIyGHGhaPmDkAibO2jnsv7J3I6tkj3vtJndxit0TnBXJCfcw94QklSeMrTMS3ojz8vbf1rAblli0CbaJ4+ANvk4lYCDVu++rerh8NTLwus6WNDZuYuskpNw7viokYW+0xM0VY8GkeQTnCgv4s+pm9eQReOvxCypwft2TuL5XTQ/MYYsEvPbRmYE1cqlwZl8obCAYwdhcs6QYCHnlw7mcERK6vnadLcH0XXIkF7lB3QMqleLc4N0bihfXJpBjD4+nEwUykBgWYc29MHFeid9/1wNF1KAkxIBAQWKEI6J/8CjVt9Zk12nDXJpYvxSKfYEUnE0Wk28neCzEInUDC4rHQzwcawcXoB0cQR3FspijPxU1RbAZ6h3tP+d/AZlq1LIkkXm9tvD2xtkzQE/wNndWnRaIF0AzFyIkjcfVans8TJHS4BJEzfhzTeRhD4VUBEzMVolbpXMMgagbRT7tfX6PaVc3zcAwIBASWEQJnrWog9LOGqr0bfnjnvtJ9g/nrkuqa6yPmkjEe3jlRA66bB5kLp0R5gyJ4p/OxhMES6uWrhw7+QFd6NMT458VPnAYd6AAAEABJREFU9lXMyM/fyG31gbijU+4Cb6LLmLBtQUSAhAxpEFLXVhieeqE/O2Tr3ZObnNa2u3T1+KfwsLZLiKdZPBk8aOn8NAmdYKeQBjWGE7vORPa1O/ftK2lZkIBAQGDlIdBc11aeXavOolrOl0823BUNMamSq+6ze+dJhUFfWuZJiO38plyIikcCQZ8E91/JkeHYkuSO2U2+7uLOGpq0TbxjlyQ/6h/faJjXYqOCBMSt4pGqkqwkCCnIEAChLq9fjGZa2eZyWV/fuI3obkzykJDzeEcOxTHTeAWCI7QHvcM2QikZa9KO0mujyfhSVIQYEAgIrEAE5rS6r0AclrVJd4jYJ6bqN/qeTS+cLPdW2SSU5EyGI7IWu+8SUfGl5IQpm6fzrESYsSVB38bHFHmiRGrwDqfqTLX9XaZ+jNoodKQT3VGUXBinlTUOenuIw45FboAJnkYsHkYieLcpHn6ssKG8jZQ/C1Vi577n8ulHhbPMY3tBPOySBFfGJN6SUEZsHNWFWSqdV/koeguIH43QJMSAQEBgRSEQCH0FTOcXvvzE5kf6R69kcRUlKGwkk2MiFkyvCsHvJH/SOc0zYAR4fqxjaN9IvXj9ObrD1qRt9V+mOuN7yPjzDPv0+Yw2xAaOrEVDWPR8rdujvqNDDoHK7wdJTzExnHEpBDkyxkBA6ijSPHZrIpskt9z5UP/m9tA+aBEQCAgsJAJmITtbmL5CL+eKwP6Gu5HLXS9MSKoxeNWzo8xgSYenZiQlQw5LPVxPeOos8bl2f0p71m5ZQOMO5Q79SiHi8jr5+sGyzduK0A9Njq8Tyc9juKuqO5Q+bRTYBUOMkImjJ5E7bav2K9Rt98jy3exlxILKDVTH9BSKMjPmickLTvFg5z2ZJEmudUI3oCTEgEBAYIUhYFaYPavOnI985nDlnoH6TWlS3m4Mga09KTkJFvOCo4ROBMaiTqKlJ4rOOVN0hy6U0jFE83p46+LzDNu/w6biJpuFrT/qq4iHj4xt5MhsOL3ZMKRQs5kys2ExyfG0f1n9XWAzfVeeuSdIfIY8LNLZIezJFLOFc7XPkDF4yjPUS4ZvufvuJ7pREWJAICCwghBYVgvXQuC+0vqYims7TNL74sxU+7yIYQNCL2aVCe5mIeqxNQnNEJOl+QSlCI8OxBAJ3tkyoQTvoE2eT2/rKQ2NbdlSR3VbxI49Q1WxdIFJo/XCSmrPVAvaswHxNcuZ2DCbpBRFsK5ZthyOW6O1+8m5e0nyaZYcfN0kdCmUN8RskGMyJiZnOI6r6Str5fI1FEJAICCwohDQv/QVZdBqMkY/wX1gvPbS2ETwzqMoY5CTMaBYBgy6nDuk2GonTLMyMGrkBH2hak5RiJkhIHTR/j3ezQtZQ5M3X9A7cTuD5efU78JflFrT66NkuzVRBSoT6xCCw8mCU0KNQNDGCEsyYWENLZ+wdStPR0LfxXPVIInHc52HNZgfmCAQxj2BqUcZEMBJlEQ7JOJXHzkiVVSHGBAICKwQBLDSrxBL2sKMpVXiy8f3n3ffcP2FubM9nmJyUUINtuTATATiZsrIQAhuKqFelGtV5qOmMgSu1yEEhM4Yx6CMvRuukB9BVdvERmTW+zjZQSYqg8pAaHSK0CnBEHERktQYnNDyCo5+KN49QOIaAmZXe9UAvA0hFcKdgHfo5Nmwt7ZMqX317mOHLtY2QQICAYGVgcDyW7hWBu7ztuJ2bK8/OlZ/mUlL1xorJXE5lmyLxVunFH44O4yhIsT6T4iU0H1Rjqq5RkY/2hcEOYzn0ZP4iMxgWUxbfSBuz8jUOm/MFpCYdcBARJVWgcqnRCYtZQKSQmnSAkLfKRLpb87THENyydoB3AM/NCRTRE6Kdy0zVs12KThHBQkbG8XxDjH8wrtlnp+SnO08pAGBgEDLEdDVv+VKBAXODoGTW41/o3/NIxP5rZR0XMJRlFh4ZeIZtBWjmb5H90TqjUOMMNZ3Q0rmHu/YaR6BSW8ZBpETsWZJyAte3DId7ShFbUPoSo73D45v5DjZ4LBr4cHYAl31IYROBEbOoBT2ADni4hMIad2Y+X3QAL2eayzv2VOZTh7sONfrZtvfyJxZ1/gBPPSjzOKFhdQ61rnHnOnDjOGihDwZJmu7cd+8yu86vJFCCAgEBFYEAsWSvCIsWWVGjEfZZbGJL7ZxXMqwTjvLIG2IA22xkIcIYXqxoDOpf5qjjMjT/AODKnimG+UIEETuyY5l9Wh6prjlyXDvcCWO442JjTtJcVBFoRVDTo5y4gQ1zJbZJGBznJyoWJJMmbk0PDzds2uXJHMdMCfa1WhkD8CIzGPHBraQEnmzP7UUgkikDzA2SUrJzVnMr9i3T8LPwVIIAYHljwBW/OVvxGqzQLdnHzw6fInztNGztXXQdA4KslioI3jpumarh0YgMoYYELqKMJE854zTWQXGGAZ9srZmjISMJ84x7Dj1jLTNz75mqXRCz02WbPE7MQK9C5X1MCMykzYTgxYGMEma1ZbeQ4+jyA6P1Hpq8eNdTX3O/fiSizYcc46+T+InBU46ZpuYQd5qKOOgUuxHYNLYGrLRZomjdxzJ+ref+2jhioBAQKDdEDDtplDQ5/kR+NvvH9q4e8RdJY56xHvKsUhnWKwLsta1WiLstkfoCIs5MeieCkEBoWmRzOtQPBmgK/HYbi/EiZjpK364LZtXvwt4sffcA1s34w2D8V7JDLc6yO10Q6AWTQlIFS8R0gYXKS1liLM0T0pxeWC8MefvhzOzt+S/lzXyA3g08eIdgbZ1omCKWklkmHEOIUvCNoGffmMufOuBA1JGoxADAgGBZYwAVrllrP0qVV1ELvZJ6SrmqIN0ncaq7SyRBx4OJMtisf0eE88yPMo1gvNJl3LNz1242W9B6kL6D3zpX7SpI7vttuf+xN3cxzv3K4EPP9w/0WvFrAehk0BXKSzn03emxU2xeBCIIyZDSxyMSRtJqRRNTNXW7tq1a87b7qmJH27UGz8wLJnHFg6IvbCEMfkqmLAZJGAwR0as7aM4fuPhqYFLiobhEBAICCxbBJZ84Vq2SLWJ4p/fLel3jzVe4OPSxWKi2GJ5TrBqW+iXg09zZFg/c+5A6KB4xna7hzfmKALJC8FdRct5RCEQOsiAmoExPhPJtWsrqGmWtfp4zz0U7R+YWBcZs85Av8IrhWd6egWhPdpQU9iLiw3XtJCWMpTLtSyCw1yOqptdaV3PXMe+4cLeMTzTfcs5P4TbQuSE0R4WSiFK6kUOmJCxiUmT62qWXnP/kSPhe+lzBT5cFxBoAwRMG+gQVDgHBHYOP9n75Hj9Ss92A9nIKvOwrtoQh5VaIotFOwZxxyBeXbaxJQ5Cx/Yq3E5B7hwGO01THQ/DYAwmNhBmtGKJWTwyLYqnDjt+/mDKEa2xhjsUDaPMBo0B0akNUUYnieDEEUX1WmEULWWYnJx0JolNnCZbKEnWz3VsZswF5XfX6/nj1hqHXXdCGR65sJeiU6SCLRXtX5gJ94XhyKwjNq+eHKYLtTxIQCAgsDwRMMtT7dWrdd2nFxNXLhJTSTPWH5KJii1lK0wWjIQ9YyzSDfKmTs5YysFsxJ4I3jphK74QnM016oftvMmp+Sn6GN2yMy47DGoYmGufC31dWjfdkpYv8mncWbd4oAEuAIWImTwxSSGGDHJGv3Enjjw7cng8ytj2SWyX/FPfF198cU5ipjhJ1w5O1C4+fFgqNMdwXrp5vzTyr7LzUx4+OiIZzD17vDeH7Ugox7uI3Ah5rADCURKVSlc12Nx6+PDhOY87R3XDZQGBgMACIYA/5wXqKXSz6AjofzZy55PDF2TOrCeTWCVrx6AlvCs3WKitEBZuHNiBGxx5JTC2hCoIygv/HOc09yAgQUH/HqnHuBjEYxv/OJ4q2uY76GSTThdHWyQ2pZyFvJqr5iuFAxOghBIDEZR4wCMk2g51nu0a702KyhNxKTLM7Nas7RyXKJLpPD9vLD8252337du5Fov5SpZl+xjPLxDYaWEn7hWGwHQBmavoPSLMhm20zsbJG56aii9fCnvDGAGBgMDCI4A/7YXvNPS4OAjc9+0ja54aru1wXk672AuG9ZBnRnAVqTyzfH7nQuAKgvfnWGTCGG6b76Dn4uFlyjoSSqAkKaGB0eiZIDAx0YyoNZpH29K3dg8l1ILQW0nHicwkiHVzbqK++ahwcefaXY16Y2fk89wyNlLQ2ey9ofeC8UyFCCohsD9N0uQaL/Z1jw4OdlIIAYGAwLJDIBD6Mpoyz24zGXORNQaE9WzF5dlFJ0qwbBfUdaJgnhme6U08HGBjJ2Nr2uY76DmZspDrJRJ9y09NVU9Fh2k2aK4pgiaxjSpPjU+X8KCyRH8bs3oQ9k/sJN5615Io2nJ0tLZ5Pjps3sxTkrmvOJ8fxgOXgLOpwIEJgZF9WkjP2JgoitaxNa+eGHZXUAgBgYDAskNgyRetZYdQmyis2+1f7h+7UcodN3CcVtXbAv/ACQVtwa0ULMrPVFU9aAbfokWzSpCoIJlvxJCkN49InpFvDFvKJufb50JcDxLkb+8/3oGHnm4DFgOZnehW8PRBJ+NxoqaZEdTFkekg5u57iGyzdOmO4zQx5b2fspbWZC6/6NFD43gomfv4Jal/L5+o7TR5zXuGdRDtjXFg3EAnBDgRG/LWJJzwJdPCt+waGOhAsxADAgGBZYSArsnLSN3Vq+qPvj+w7qnhyUsaXvrEmOgEEro66wkjo6L5kwSlpDJbdHJ+tuxcU2YmZhUhxqa78b7GzraFhw4ijo6PTXXHxnQY+Of6xCMiZ21iKTIdkVBffBTb9Wd91cI0vGHTppp4P2rYJNj+vszbbF6/s/7iK7YM51n+2TzPD2CiqBBCAByYPZw2j4oRppGcELO1fVEcvX56TK5FyxADAgGBZYRAIPRlMlmTUWObTzuuobSz23GENRhUClLFGnwWFgjawCWDB9r0UHE6x8i4rnD0BH1CxDUa5OrDeTzdFu/QxwcHU7F2rbVJB+kHAqGvAC1i1Rwnp4mCKliDVkLGsH6hfkutMr7kn3RnZr9pXcewiG+kqb1icLRxxXw+7Y7+JJbazkYt+wyRIxhYSPPDk0QGRjOMZ4J3TlzcHWJsSqm9ui7ypocOjK6hEAICAYFlg0Ag9GUwVbfvlOizD+zflrt8i4nSmLD4EjMRQTRVoTMENNOmJ+QMTc+6SsAGEBYv1rhG2Zba42df81IpYniZlstcGHyWFjHawSQmiTz7tVwzwBllSxyjJB52WT5iI97sSa6eTIfmRaovu+aC4Ua9/mknblRN4cJGOoEMI8fUDKgi5Xc2vM4m9jXHR0dfKCJnWCOa14VjQCAg0B4IhD/W9piHM2pR6uzfPESdN3LcvYHhTRULLyHMrsTInhy1WOXksoXLq78LqoE/V3yr2WVTnNeGp/Oj9YUbYx49seuQyJ4X2SjxAqRmgAAxUfO5x6PzGUG1MhgKiggiQ+rZkawZjYCKX3QAABAASURBVExLPukOlfvJ8x4Sm8Rp+UX7j+aX3y0yr4cLa+LvTU9MfzfinMjXmzh4T+wVHMwnBi3wITzOMHZ/bBJxnF7i4/Q99z01dBlACTEgEBBYBggEQl8Gk1TzdotwvMOQqZCSkDKTrsVn0F2rVc7QZO5V6JgZB+3BO+/zvHHFw1c6PW21iLcpyKkLHGUKQgdeM5oWqmlepTgBgZEK2lARtAYnTJ1Z1pgXiRbdzeFQd2Pj3vunQLdTUWQvFvaX9g4PV+bQ1YlLbr1q/YTL3De9c2MGf/Ei6B12N6eQiZnJoAKWk/dCnojYmK44jV48NpW9dN++fUv++oGIKEhAICBwbgjgz/vcLgitlxaB27Hd/rG9E9e6qOs6Y+Mye08suvQ+Ww9GkQqS54ynv/I5mz+7AmMbdGKJ8Q4WGZdnr72kr3Zbm/zHLA2yVTJJn8feMakQbnHGO2IAIwx9yRORpkhmosAWX4iQtomSdOOXHx7oFCk6mGm1NMkNmzZNb1rT8aRzckwisyFK01syql4w39HLtexTvta4O0VH7DMQtpA66CrCAAf2kwAZCHvFzBoTp1slSd5+zHTfgMtCDAgEBNocAfzltrmGq1y9eN3A2pGaXCjE3QYBJANEBPLsOLMs69L87EotmW2g+fmIYHwVEsKy77vLcXt45yL8tQcPVoWw7Q4VZ03UbIGbZmYLT0mbwDSrmdLErqnVXM83iOwpzZbghJnFMA3mXgaFKY4ivvrwwOhl+/bJvLzkrms2721M17/mvZsGbZPg38k3Cs+eKAgQJkNRFJXjJL56bMq9eOV9jY1CCAisOATMirNohRk0SfGGUlS6JI1LXVlsqRF5wjvellkJwinG9ti2JQhbqqWRaYuvrCkB18h1sUl6PLxy4iZNMTT2IDBfeOhCTB67HChEjgqhmeCRCkVx2stxtGXNUVKHFmVLGx3zoBMeII6Fo3RT7vnSvDQxr19vu5E548z/LUj9AQMcHO4ix54Ukxzz6MWBwokixQzigYvnCFCWzrPl0juPHnEvW1oUwmgBgYDAuSJgzvWC0H5pEcima5td1tgkQoaZyVhMmTIUtSrMDA6FNGeNyYyNG63S5uRxp/eQJW/Kxpji/TeDlIp6wRHY4VhExrEpzSNOmxGnzEwAOvVsN3JptCUfjKu76jiY9pgQ143hKsd2h49lLc0zbLl6076pev2zwjQtp/Ql5DGfWsbMhCx5vNrRMiK2UWwuMym9+f6Dx7dQCGeFQGgUEGgFAmCHVgwbxjwbBP7o7sOVOx48epHkjfUR5VTDy+tpi/fB5tymzWOwkwWn84hCzNwUeL3Qpm48tYWH3tVzLOEo6THWgpCJPPRrGjqjc/PkOY+Al4hJQ0mMPa/WiFI9WWq5cTNP5Z4GXS6TxCaOS/E1+49MXbJLZF4PGBcTNVjcp+qN/F7BpOk94YAS4XYyMFzttyi0YHwSQ04IfjwRW9sdl0svGxquvXTXrvnpQCEEBAICi4aAWbSeQ8fzRmDE5D1eaJMhUa+TxGP5hec0744XoANjmszH2Lw29gRzLkDPc+9C4jjiyFYMsxW4mdDtRGcoI2KmM4ainomJIueo11sTnbH9IlZu6Y2P5Lk/Djs4ic12E/GN1cHJeX0nnfF+fvsV5z8xOVn7KvrFrooUnrgxhhhGk2KGDDPj/BSBDtHFUcm+Q7qHw9fYFnHez67r0CogcHoEzOmLQ2k7INBIK5dRz/pX1rt610/YhKxUqJSXyfpzmzZtfbLMxzYhhk9nyKkHRxFlHFnOfMuI7xRbppLOemwvJGs7Y7idgucMb0BM0Nk0hGxegtveRZ6RRGOUmzqEyOIdcupy1CfUyKuUmS7bKFWu+dN7++f1W+qn6HauJ3ntB3Fj6rvGuWkycVfa1fHjewbHX37kiFTPtauT228jaiTCfyMN/6MIrJ7gaYzx1EggcWdRyY6EPel7dsUFbXC5pcxWKlTpfumR0drb7t9zZD0KQwwIBATaDAFd59tMpaCOIqD/Gcv//tGxTbVGVvw3mlh7tRjrLhdpOx3ydlHGmIiIK0R0hvtaUE3UPBbZ4qDnKkYfAEBupTjtM176doqgz6LJkh58ZsZ9LgPQqaZzH0W0jRO+bsROddM8AjP7Sy/fsLc+Vf8iHPLiVYn2f3KXGPPk0yKvbfDotrFcKb16YDy7AudcVITDikMgGLR8ETjDwrd8jVoJmh/ZM1St5Y2tdZevMTYiXT0dCzxKT/5ZdNQ6i0+3+LdKG7aNmMh0MlGklA3yOqEKypBXbdVv1zMLTJkAKXlm1BmIJy0QvExOI7OeouiKTceoTC0I5Ub3VO6yw3jNMmYx31Cpo1Qq3XzgyNC836VvIqol4j/pG9m9Mdzz5u8aABtEwrOQZ0MCIZWTbEeZjdP0qjhN3vbA/lE4+ydVhmxAICDQcgR0FWu5EkGBZyMQSbbOMp9vmMtKQc9uEUqeiYBzJsF74C4msgS6ppMDMzVxLFhL+f7kWtThFG1wRF7IMCUNl2+bKk1gn15Ll1Z27OC6eD7ovQxjZFGt0zi6gqPohnh8vAtlc44ML/2yS9fvrdemv+i9H2dGV8UIT8Oi46k8XYI2wBQ7GOtK5eh1wyMTt9x9+LDuhmhFkIDAWSIQmi0mAmYxOw99zx2BQyNuY83wJZLEVbz+JcJMecI/uJQMtpl7zwt8pV/g/ubRnbHYlDbcRWyMJwZaEBBVk8i1Y0/EENQRwZkXU+QY/C8Mpx7vj4kbIPSMyDpiG11+dCKa1ztrmkfoW5s+hQeUI2oFQ1MxdmNcTt6494mR7djyNvPomvAeZwps/Ek/PfXDxDe8BU6kY7Al9dBhPXAgBKV1IWYmYoMdIuBUKl9iq+X3cV66Yb56UAgBgYDAgiEwr0VhwbQIHZ2CgL4//+v7D/RlWWMdYxElwmKq4j3WdyUkap+Atb5dlBmv5zHgKrNidZJSIB1iZiLEE+Qu1AwFkTX9UC06UY9aG5m1f/fg/nW4Hh4/CpY4RnHc78U/5b3UdWhVNUnjy6PEXv3YMZrXgwbDS794x9qD9en8C+j/SBMBjNIEAZnnjrg2TkrJDaOjjTfuOjx03nO3DDUBgaVFYLWPFgi9De+AQz8a6Zy2lQszW13nbEJkYvJgIziU8Jo8YZFvI62FPEO5NtDocw8dBVi2ItjBEG4qBDIuMoydjYK0ilQro0JpvC0uiqk4IyoSVDPEJMmWnONL+vv7U2pBcH5inHK/TyQfId1FIOwqmGQDl9JbwfEbYRvPR611RBPV2NyRTdW/aH3eUCy0w0L0MNs5niSa79kJc20JmJCPS31xZ8db+wfrr7j/yJF5PVzMDhPSgEBAYH4IBEKfH36LcrVU62uI7QUsXPYOhIkF1YunyFoycEHB6osy7pw65YIC53TpQl6k5ObIg6VnP5XOp8CEegw3434q22sWJRo1e0KANbOWEhljSt5GF9TLZTwoNMuW8njNhg3T0Hs/SL14j94cW2xaSl5weGD42n6a3wf2GE85my5ac0wk/1wjyx9ubv94DAM0gAMBQYGgAEeUIaNHrRJMO3YLLk7L8VtNo3Q59GzJLgZUCjEgsEQItP8wpv1VXH0aPjVKF7pS9UWlUmevhWcGZiE2hsQLWQHRMLcFKMyFHszFU0bLVeLI2NgwR4AJXGSgEBNImZpq0kwAgMgxGWpq74kYZThRnscZyAuXg7XiOO6ui7l+Okla8kl3Zva9FbPP5a4f2hbcKmQpStNLbSV++8hY/TwQKdM8wlbm6as3rP9Gfar2KeMaI0YywOEVgKJX5Ah7QgVWgARlhshY8hBnbCmuVl40MFZ7zUNPDuK1PKpDDAgEBFqGAP46WzZ2GPg0CNyxS5I7dh1fP1VrrHEeFAMiZ7QrFtPigJP2ilAvb7lGnyDwkAXLMDfvaWhFCKBqHDVqTkXzkJlswZI4bbI4MgzP3gsZdKMPB3FsN/UfzyqoaUnMDB8WR085kWkiJmYm77wtldMXHekfu36+XjohdHXRWIexX6g3su+h88ziAYcLQKgYj5kJDA9B1HvwBHZENrJbSh3lNw+MZTcdPiwtwwmahRgQWNYILITyzcVvIXoKfSwIAsf8aNWZaFsepetyi3emIBYBr1MhBNYSYmptgFdYKKCpJwEHFCt+UdaqA9xDvOb1lthYAWaesKOhSAEsAUGdICQoqKd64wt0n61DM5zp0WoLXI08YhrHF/z1w0fOb9UPzBDVjuV5vid3btxDcYF2HvcC23gbxfZ1Y0drGzAP0BQVc4zMnG/rXfuITE7fYbLGU9Y3sBOUAz0h1BVCOgI//dl3HUpxdmxNhFcAcbX0/iP5qP7gjEKr1UECAgGBJUYg/PEtMeDPN1xepjXWmm1pHJWJmVzxyXYsrMTEzIQDtUMAiRRqGKiGmwiKFactPSjPEc36lkTI0umDoFiFQOLNVHMMGidIkaKYiagU83oxdE3fILXk++g3bNpUY2OeyHN3XHWESmQZiAtFcRq/5MDx0WsOQk0tn4/09dFUNTV3Zo3si+LcRBNFIS9CiM2ugcnTGT1BHQpA7JW4XHrJ8FjjNffsP7YBRSEGBAICLUAAK8NzjBqKW4LAriPj6xou3854P5mBUXLVAkSOrOaIkC+E2iNgWWdhPqFea7WyIGhmoVk6Or02qmyzBfYX0FqJUssIzC1w8q1EBCYjg8IoylOR/AWPjx1tyXYyM/uecroXD1ADxAInXUDo0B4sG8fJDpPGbx4dnu5DPbQ9vb1nU6rj3HDx+qdKhv4qr9d/aMjnGAVkrsdmDzqAnjHeqqsQ0AaZk+OIJE63xB3ldx0bzV4Stt6beIVjQGCpEQiEvtSIn2E8/f75lx4/sibPar0MUiKD6bGGmI2unVhcCTwDrwiL+Rm6WbqqQg8s6Y546QY9/UidREDJnaUeQqRkVBxxQF7BFZghol1AhHBGpFMQJfairz56aC1IExNBSx6cj+CE82EMnKmehbqqHXMUlaJb+g+PXP4Q6Xfa0GIekZmzDevSh7OG/5TP5cDpuxKMLKhSQVJE0Dtww47BZXEpecuhfPjCVmFVqBMOAYFVioBpkd1h2NMgUHvgaAmEsh400gWB50OU6wwxkcUyakzhgRZr+mkub0kRM9xa41XLlox/8qAOzxbM6lefXPrsPJOQgcDjJfAQEfvinIEywdvU1BATnNRCSuXS+S4pbwdpwnWnJQ9rpTJMDgQrNKV6ifdEeNLIhShKogt9Gr0iPTIxr/+0ZdaoLV1dI+XUfrley74qzk8wG1QxpBkVN8VvVrTUQw9PKDFxpVypvGJsNHvZg0+NLog+2n+QgEBA4OwQ0L/Ws2sZWi06Allqu32cbueo1OkwmhQrpZD3WCwpIsNMloVAotS6IERY36EKMTNZsfG+0XpLvqdNJ4XSQ8TGCJ57sGdOQooYK1vjeYMhMtsWGUGtEhCRIa2j4pxIyCHnipRYewB9Yfs9jsvdYkvXrx+ir1pnAAAQAElEQVSklti5fTvXEvZ7fZYP426A+g6aIxHB/RClpbT8+r0jtZv3icz7PT8z+/O2rzsQx/x3Wa3xffZZHSgQERNBhDTgKB4ZB9EU5waCJjaJt6bV9B2Do9MvClvvgCfEgMASImCWcKylG2qZjtSw0Yas1H2NT7p6PFsyEEsWTGPhlcfknceSmkGweLbQRmaBDgTNGDqa9AdPjZSxxcrUwpCmBD/bR2IbMRWfxvZkhCHqVBsqFCYqEoHX6YAtgawNhGAJOB+VORma+S13ErSNcF4httWuRly65RuTgx3UotAV293kGgeJMyjpiKRBkc4Dnl/iqHQNJemHjx0bvwDzAGPnp+Rm5qkN3XyXq03+X6lN7jHeewFOAjQAKSnSjIcfEgeMHJHxeKvuSf83QMccReX0xXEp+eChxsil0Ac3MIUQEAgILAEC8/7jXwIdV80Q9wzUe3NHm63hSBdOAamo0GyKBbxdwBAoglWeXJ7ZPHfRbxAxtUE4OyVU+2fL7LUCUwS2CFKCwBGmclK6+Nt7RjeBoFryN8M225s7/6j3NKX6kJIrbhLNM0i0Wi7dMnis/vJ+WphP42/r6RmrVKpfx/v0v3V5jm6xK4D7jwkB4zIeiAyEoAeKyQgRQxnNQ59qqRy/YqJWf/MPnxja3CrMKISAwCpDoCWL0zLHeFHU37lToq88MdzTqDfWsDGRxyhYN0m/J62C1RIlbRSZSAk9c844n9srPkEoaa1+AiYhOttbGgxEzxToz0xqiejhhAgIPdoCvrpyD83/w2c0h1Db1NtvDT3gvR+hQi/dCDdEyDMzRZb78D79jYNHR+f9vXRCYGZ/0wXdT3VF0SfzzH1NJJ/GjOM29BgRDbClgVFxbsgIk/FMrEIIzESR2VyqlH5iZLr+ij1DQy3b2YA2IQYEVg0CZtVY2uaGHrj8aJr5vNeJK+mSKVgUBTo3Rf10zaGgTSKWbCLV0eA1OlPp4b4nY2pxMEwMFfTlN5KzjArrrJy4RLuhk+ieCX2XnMhN9+0dnvd7appDuJE5K1l+yOdyGIrpPjcRXh3onVFYTWLKFXtz/9D0y54kSmkBAjO7vu09j1cj/lReb/xIfFan4t254oOlQxhDzwoRSpqgYWyBbpTEl9pq6V1jY6Q/ONPy+wNqhRgQWNEIFH+DK9rCZWLc9GilHEXcF8dR2TPjnSRj3YYw1kgG4yBFro2sYWIs2sYkEZm4o9TpF4REFsJARicqSE6JWnbyDa/nzxS9gImJuOkBC2iq+dlElMXx1d9+7NA6bCHjhJY8JOz3YDvkCdwOdcF2txR6qhqgdRBtZMymUin+ifGjo7rNvSA6bmWeXlsy3/O1+id8lu8R8Y51XJC5eOSQGpyrMFRRIZyL4mf0t95LLzs27d78wycH11IIAYGAwKIiYBa199D5WSOQW+nxwtuTKKr4Z10lIHc4ZVouemitCPaeVSMlPY7imKK0x1FXpbVaNUdvEkoz/+yjgidKN4U88wGpsKuwjfBA1bxakEjRmilNy9uzqHzNPUQRipc8dmVrjlljHxNP47ghMD4e+HCUQgRaGk5LyUuODE+/fM8eWrBP5G/rqx5bU00/52uNT5LzB/CuHChRoYLeq744gxKIel9oWUHoeOhgE/WmHaW3Dk/4Vxw5IuG/WQVGIQYEFgsBs1gdh37PDYGv7R/oamSNrYa5JLgUjg+OhEWaEIoS5AWC07nHBbtS13BduNnamOO4yySuvGCdz7EjeK76cYNZ6J6jF8WwKc/RgJTYta7oSJ8QGAdIlJQ25Ta+aQO1htD162sxy2Pi6Bjw96JK4o7QVKAfIW9stLZcKr3jicbBrbADitO8AzPn157XubcURZ9qTGVfdsXP0ILQDePBB6IKYBTW0TAJHnp43dlQwbv2OEkuT0rJe/dPDVwMnSyahhgQCAgsAgJmEfoMXZ4jAneI2LufGllb87LeiTGE7UoWxrLIWDUhiISlE0xD7IlYqKXBFKMzCTOxTVLieI0XarmHDk8RPIdY6PfcB4UPMD53g5maZju1U7ff1dYoIZO88Mv3HeudabLkSUTuYcn8btwLeJ8NDZlIvWLWewYESpAkSV4am+THHji6cD9Xy8z5lm14n56Yv6pPNb4qPh9lvSdVmMhD9Bv8HtrgjMR70ns1YiY2NilVyy8br/O7v79nSL8pwEsOXBgwILAKEGiuzavA0LY28eDBJBe7zpPtEoYDI7reNYWJZ1SXImeK40xRCxLVhqEDY2whQ2SiCITelUlUIZS1KtYvBpMINqOVTZ5HCdF61sOpwszE3JRmDSOZERCmN5ZKaXLlPYeP6/erYTyqlzhG425/I8/uF5FxgskE9dQefYwR3DfFnLBdAwJ91/Hjsg3t0GJhlNTvp8elnh9ktdpfZbXsbpB2jfB0qbes3gqqC83gx4xhoRh2E8hDL47iNeWO8rtGJqZe3t9PLd/NWRhEQi8BgfZCoCWLUntB0HptavWeTheVt5m42ikckS7MjEVQvw7EnnXNJGJPzcDNpMVHZnitWLSFjcWmQheUbrmHTmScEJxFHBhiIJryDFY4JU96BgG+M8VnnXi0jON4bYP4NZ946KEIp0ser7yyb6pk+B7OG0cYjCpqFOZBuV2zpPZZQ1Fir6Q0evcPDo2voQUM127kyc295W83arWP5bXpe41v1FlyIjA3Hh6KkQzujcgYKhYXeOpaqJ67jeILKt3VDz41dezmXSIL9o5f+w8SEAgIUPNvLgDRWgQc++6Mou3WphUp3B3GsgwB6Zgi11yqm1oyEhUkLYgFgRTjMhVaMYM3ubNBtNgfeCpGfa5DDZTivTj46MWnB2cRKtJCUb1Sz54pWn52gi19smB0G5kXfPWgacmntpnZR4l5iFy+FzPQkGJCYFNhI1LMSjPL1SSJ3j0yPPZitNGKszPyLFpdubV7qLciX8mn659wjcZu7/NcCkL35EHg4qEBYmSYlNh1cCEc2cRpmt4ymcv7hvcOb1xovc5C9dAkILCiETAr2rplYlzD0JoG8yVsk7KQJS5I/WnlBVkPD90zz3iYKGhZFMJCXIwuBH2gq6eoSmyqRWGLDrr/bIhBLFQQ+nOpoToLKp8pKDqLCMJiR5XujquYzUuBgzmLixa8yTo/fDhv5D803o16JVJwJWEeWJ/P8RDIM6TOkd2edFR/+gf7R7fRAocbLlg30FuKPpVPZX9usuxRuNt5BD0MMXk8ZDiIx9MV6RZJcdfivoHn7m3cUeqovLU2XXvHdx47Fn5wZoHnJXS3uhEwq9v89rD+rx4d6ciF+5htTFiUGQsfYWF+WjvQD8MFLQqYhHBS5Ft3wPIMPQhLNZZsTyV4ZWnrtCEaJDxnkAGhg+GeSxEGboWgAbIFjJrilM5CGB5n5jOKS8nGBttX/PE999izuGzBm2zfvr0WWb7Xe3cUZqs/TML4U4YwjBIlUozKxsRJmrx6dNr9uIigAQoXKDKzu357z4GOSqyffP+kazj9Ops3rBrgHi0IHXcJUp0Z6EmCsVVPGycbknL5/aPjUy+CXi15dQFVQgwIrDgEFvSPfMWhswQG3bFLkkeONdZ6a3ucsXAvsRhiUSYsjERMGpTb4Rs2F8SZMi1vjTR1KpSBAgJ9PHHymT3DcNJQ0KL4LsKzheHMC2XPr4LaoPL8LU9uYYzHqacG2Sju6H7lA0crm1HQklgy5hEn7glirhM1bREYL7iDCEE1JfXaTdJVqpY/9JWHDl2D4gWNzOxvPL/7qY6U/7o+3bjDZ+4gZsGjnFQIu0qkL2RwszCIXeCtezx0eOhl0spVcVfHT9/1SP95IPWmAQuqXegsILD6EAiE3uo5Xz+YeOPWkuGyJ5C5gJmw+BHyTwtRcUoaWrv28czw8L3geEFZ6CrEdu9ILW7lwszM4jw3wGmZonRmUSNUztzqmbUezr+1hpwwVTqqO4jczc9s8xznC15ciycG6jX/sBBNNe8NwVa3xzhCwAJ5Ao0SsbGUJOnlcRz9zJ2PDnbSAgdmzl9wwdrd1TJ9vFF3f4dX6NgsKYZujgQSZ0BtIFCMcLuA85lMHJc6quXXDtez1xNRBAkxIBAQmCcCZp7Xh8vni0A9TTKyXWRNqWEb1Ihr5Dkn45kMVj+mnCzW6chZ0jXRwesRal0Q7NwKzTjjXCO2jmI2Zeu46w/27JmpaI1+b9nW7bxP8hxeYGYcSYFjTuodUoEeEel29AnRc8hzRkENwKemMOzOM8ZUJWTFps5Gb/wPdz664CRJZxFevGVLLSH3PduoD9o897hdyOnDBmwX2Kp3i0U/AluFJY67un5ijPwH9DcPULygkZnzG7b0Plq1/IeNqak/lWz6gJHcE1s8WBigZ7FvYInxIGRwRngwYnZEiVlTXtv5C994vP/NojfWgmoVOgsIrD4EzOozub0sHs3w3lx8T2RNCpYgMZ6wHiMycUHoujwTGZTMVBQJtSgIxhUszMQMwvRIPNZlm8TGdAzVu2NUtyyu7Sg7aJSRkpqBGiwELSHgccG5SJFnZJ8pKHr+CLuNUWcSVyOfJnzNwGTjvOe/cOFbMHYkShQ/LN7vJ9HvjWEMuMFQi9RK1MNW6IkzQRWxWV/t7vpg98MHb9TThRZmzm7Y1rk7je1fTE/nn/C5x/t9DE7FRCCjuhB0UhGcQxjvatL4Cp+UPvS1B59a8A/uUQgBgVWGgP61rTKT28vchneduXNbLBljsBpbuFoGy56HJwPPCkzU1FdQpkJIqQ0CPKoTWrDl2DB3dI3VkxOFrch4uIXeTRJ51xyeZxJB6klA6MjMOTonFOH5y/scfWSUVLsunxLzBpy0JHLH1BHJ5SErMmVgIp5foIeBNO1GEfLNiFIum+iaOCl/5Ht7j25oli7skZndiy7o2N1h7B/Xp2ofk9rkwchnXh9SHZTzOhwOrPe4N8TekuEorlZLr6mx/enP796dapMgAYGAwNwQMHO7LFy1UAjkDVeq12rdrGuwrsAq2rmeI20mejxZUNGiqFrMDq15VRcpe8krWUlKs3UtSa3kUGQavp9nhlbwA1UPzWm6EOKwB6A2E/qO4rjsLb+4VdvuN2zaNG2JdkGnUeiDx8FZC59tsTWWxTm8+k/eOlHL33IOW++znZ5Vysz5TRd2PlGx7k/r09lfuCzfL+LxKNS8nKEaU/MfQWOGeO87unuqt7lR+4pmq3AMCAQE5oJAIPS5oLZA1wjeG/7Rj57qgSe+nrDSgYxIt9kJjAFHBgljJN00JtLz4gx1qEB5i2KhBLQpUkOCBRmrNXvidRO5tOw3zhUNzn2NfP24OJeJEq9ASeCq2Gr9fMWyJYGxJrLUwFvhuk2oY+3a1xyZzF9JLQiMbXeocR/s3WfFZxbvptVT11sE84F7hotbRc8tMaE9i03WpN0d/7B395FF21nAOO6mC9c9UYlLvz89NvXvaLrxoyjPGywZ4WGLxBjCExcRY/nhhHIXUVrp3F5d2/OrPbqGhAAAEABJREFUX77vwA4KISAQEJgTAviLmtN14aIFQOATD1E0OJl1YZVLQO4gR116qZny7ACagSBqiSYqmm+VMDc1YAJhQGVR8a677n1LPiA2i0NsfcMaHgWWHjJbfCKdUfvE+blmrDGwmMlaS84L5d6TNaZ7ymcvv33nTn25TksdKjE96XK/CzdNjU8ZHGdqMBIoTQJdjTG41cjEcaSfen/fd/YcWX/KJQt4wozt9ws7jlYT+8np6fqf5LnX9/0NJXRMjupBxX3jdVAoySYqV5JXNDj/wM6d0hIsVZMgAYHljIBZzsovd927usc6qFK62Far6wkLmyFDkRisv548fF547qRBHU3RjMqJjJ60QrAkwxMkgcLQlNXLMkx4R9r31/cfaMnPoc6ikJOdjFzjsKFGw5AnBnBSyGwLEMdsdg6ph9cf24jqIuTgnZMxIHZjou51bx0zG2+aQ5fzvuS6bT2jMbu7TF4bNj4TKzn6xBwRgzTVXhUCGjNiYs59VI7Lna8ba9D7F2vrHUoU8UU71o5dfum6/21q9V/x9cbnmepTjhuUkcM/IkBJli2hiNhHae+aNT/ZWHvw3cXF4RAQCAicEwLmnFqHxguKwKSLUmGz1hiTenh8uvQa1iOGmUmQa8aZ85mkWdayIwhj9sEC+jKDPMQlI7W8escdYlulVmfdZZHlCWaTK07MTMzQDdSmcb56OecIc0WNrEE2iihJEsK8cUcp3TbtTEve/zKz+Mzd63O/F/ZlkJnISE8WkCdKisiG4SX3dHZUbuvbc/SFRdkiHrYyT99ycc+dPpffn665L2NSpqACMVYfvd8jayhr5NCAuVIubS13lP7h1+8/dCkK5hvD9QGBVYUA/qRWlb1tZWyW+J68VL06ikudpF6KieCxYBHmGRIi5OFhqqfZVNwjUUHSoijq67Eng0VYvCXx0JENmUrHWiqXtz9x2VNdLVKNJkx3TWr1o3ljetJE0A3Y5YCLoR94D2oJZB4RfTawO1HiiGwOJGpCbCrU4HLsq73vvv3bj1wyj97nfGlpYt3exnTtO8Y1phjTYWAvIaPer756UMlxnhMqFQ+khqzFfXcVJ5Vf+M7ewxfMefCzvJCZ81dc1PMtU8//RWN0/ONRY/J46rCj4B0ZIYpjzBf0ns5J995vsB3pL3/1kYMt3fE5S9NCs4BA2yBg2kaTVaiI85zmuethYjgqIHGswDLrSnITEMbi28zNHHkmbVHCqg/zqaPjHCQSSRR1WlNOT61curNkC15rixt3Ig0Clk1VoSvyUJFU5qPN7PVM+q/ZEzPyTKacxBdP5PnLqAXhxhs5i5i/7Zz0g7wdpNCCmYu0OGh+9hQEqpCgXTWJ7Rsm6/Yn7j58uFK0W8QDM7tXXrL2MVPz/2F8rP5HtXq2Hyp5CKZLCPUg9pjxqJHYxL7JRMnbF1Gd+XcdeggItBkCgdBbOCFjbKoNic8jjiNhU7xTdEixtNEsrzfVE5yra+VJ4B0T47xZ0bojvF/S3XXBrgIZipJKF6WdV2WGW/b75jcQ5YbyIZc1jjEYC6ABLxyVMWj+ATNAHn0leE+d+Jw8JZiz5lfvI3Klibj6U//irscum/9I596DsPmur2X3UF5viOA+wfwI5gXWgygJujYfFS0xqRikToi9MWvTrvIvHh2R94ngIlrcANKWV1+94Yl1k32310fGPphPjf4du6lJpkxyPIdlHs9iDC89Lm2OS+Wf++ZDA9dTCAGBgMBZIWDOqlVotOAI3C5i/u3OQ1WpNcqChfWZAzDJSUWC5bd5KiiXZrZ1RyiseqgCmqo+4Dmb53lf7vNuLW+FKFm89uqtdS/6uTUpMDOmeYuDrBZIJSn6gb1FetLBVNPShQ1nFv2d9EljnsjeclnfuGf5GmyfKKYHNTyjJBdIUOEFo5iYmRQXFWY2UWS2VCrJh76x5+jNwIlpCYLuKrz2us131scbvzYyMvEH0PuwYXYeW/A4EJSI4ii62kf27Uul0xKYfS5DhLYBgXNGoLnanfNl4YL5InDFQxQ5H3eI6Sh5isljCXPM5CyT159/BXFTIYQaavpaLKgTlDZJhVoZRG+dGBpY8nDsnInYRemmaW/XobBlscyuZiQfZfHQQXVkpBpxDqbT3NwFfQB9FkMqhFlT0bxwzD4qrxsypdv++bcfaMn38XPvvwk+fMIQOdxKhZm4ZYr7hzFHjJyDzp5hBzuQusBbNxRxGpeS6rU52V+4c++xJf0e+K3Xb3qynozfPnn82Ed4YvxbZZc3yrijrPfAWEqlavltX3vowKL8XC2FEBBYYQjgb3+FWbRMzOmrkmGikjGRIfAzY7Fl5qb2M2lxVtSheN5khD4WITLzjOfHWICp8j/uO1RpqUeVR3UmOwa4EEmhpQULrD3pA9Wp/QrmToXh7HaVkhewT1/cCgzianYkz/lujF2HpiJeiHR+gAITF0qjsJgvteJEHg8/1prOrkr6hlrdffD7S/xhtDft2FG/9eotX5war//zian6n+S5P+C9z7yIYfYXRxH/xN13P9GynR9aiSHYtCIRCITeomkd6zgWM9s1VVuuikTE8JUExxyLrydSciw+/asTxF6oWI1Rh0xLoxJBUwEQBPRlUg2JPBvipNxBNu79gxb+r2sxxdPM9jggy0kDaJ0Bn36SWjXWormLI4JnmxlLjiLMWI0M1/DiPqacS5idiBPLa445/4HfvvPBHlri8JKtW6clz7/lnBuAMuBrIVZvnDy0ZehLJABDUOaR4qy4z1BNpLVRaW3aWXn/sLPvXYoPyWHQE5Gh6Kuv2/qD6mj+/0yOjv+T+nT9Sxn7CWcljlPz8omSfYGI8IkLQiYgEBB4FgLmWSWhYEkQmMzS1LnGGkO++amqYlTBGjsjxTlOZ9JmDusZqnE8UbrUmVPHZhAGFypALYospxHTmtSsLRWFLTgI5zUic8wT10EApA8gqhuR7iTQAoRmbzofs/TCgjJEffdrTBR3VErXTkn5mgUY7Jy7iG1+b5a5B1h8LvC8acZuxYEQQJx0QgiYoIyYsckt5L2YOIrOK1fS99Smo1eKyEn3pjZcfLnxxs1Tr7x809/X65MfddO1/5XVGw8YJvJZ/sLvPvxwS15lLL7VK26EYFCLEAiE3iLgM8orU35qc2YnDYEFMzh/ERiiA4tw5LGAcQovMAYhMcFLKYRRH3l489K6aWMQVyRCoADSd/0O+jK8Ow8P3Zs8yjnfPtFwG6hFoSKdU4aTo97H06AocuxIQAjMETRCBse5RrWZSYg4J2cd+o4KSX1Oqc9Qbii3VetsxwVHvbzv9p27OmiJw0t3bHiCp6f+xuSjIxE3RHReCI+N0FsweQUCuL+MhyVaZ3F/YcfBmxjzGZNwkqSlynXeJL/4vaeOvxykrsAtqRXMLK+56oKHX3nhun+ajI59qD409THK3ETW4PC99CWdiTDYckOgdcyw3JBaYH1rua6geWfRLTMZ/TS2UPF+EweaDSiazbZPOqOU0Oy/GdXAnNjuXZvnWct+XKa+hTJ2POq9rzEzIc4ot0AJbEeE5Zgr7RIniMgx6Y/t5FlG1thSJS3dTEnpJmpByMV9q9HwDxg2Dgg0NVAlMT+F4s2S5rEohy2iGRQxERuulpLoRS7jd3/34PDlIHVLLQjM7F589bbHSg36uPXuK6mJp1ugRhiy3RAI+jwnAoHQnxOaRa5weexd3o2ltIhYvIq1Fotnkerouv4SFtiZpVaL2kAYKuG2UeXg8Xm8nFZRamex5DLZuH+ytqZVit7KnHdVeEh8NmyhWwEuCSm+KvPRi8UQq7fLBG+WqICAiODsFhKhlpyQRcDe9YVHpmo/3govPRrcdDir5V+1JNMMhaAutOYZMUVKM4FxRiREmMumzHjuhtdE5egt9Vr+vh8+MdSy3xbAnMmLXrRj7BUvumL3i67dcZBCCAgEBJ4TAfOcNaFiURHYOzppfZ7jHaUut1hQdbQZLwmLGM60TAVZJYrZrJ62hahCKkoHSNUM6OlyKX/i/kPdf3S3xK1S85Zt3dNeaASaKddCK1q4AFMJPWqiHEgI+jCj4jFoMXdsQOpxqVyyL3QxX48mSxpvvZVzvAn5Ot6lPwEMsNOu2s6ooFl9EinSmbJTErVEK5mYTV+Sxm8dzepvuG/f8JJ/yO8UtcJJQGBpEFjWowRCb8H0wQvn//X9wymWzBLh/TPOCy2E4B3BY2JmnAvEQ6TpOKGcdCFGSftELP7s8d5YKMedxBJRnHavzdOO7bY81nyd0AJl8aQ0wnm+n1xeN2A2RXMh1GDMgYFgRoiQzhK67lB4nLDODySHJ++iNJZKx+WH6/6d/+HOR5cci5418lBWb3yTXdaIinc4mCDcX1wI4WgKIQSGFBEPlF4F958zlryJrYmTHaVq5acHpqfecPcS/DxsoUc4BAQCAnNCwMzpqnDRvBD4BJExLCBzEzOW1VlCxzpKzFrCcKyaQwhJM9NOR6gEDaFZUzucIq8KMkVREnuOL6gTtWzbPXLRuCU56LyvzQI5i7FqOR9RW9V2NbhItTMtRMoGD2TIe5C6CrMtV0rpS6YdvRDjL+nf2o2bN081svwLPneHDJSFWjg20ZjNQ+XnjMxMbAzEJklsrymV0/dJlrzksEjlOS8KFQGBgMCZEVjkWrPI/YfuT4fAQ2Rzm3RQFFcIi7/SomevWbRmCFHziKUX74GxZ0rtEtQLNWRm9CO8OxbyhkhQJmIpics9PirdVLN+K7Uo5PHYiHWNJ3yeT1DhoQuBUOetDWYDdhIZT2RxwugRpsNyKsShVuDZOo4o9wmJrcRR0rHjaM4/9jv3PL7kDzidUfTdfLr2LSMuE8YcQaByMyKvZc0THD2MQsKMCsyuwCIHEY5JTFpJ09LLJh3/3MGDIy/cJUv/dTaoFmJAICDwPAjoevQ8TUL1QiOwpRv77GQ6OYoT7HCie11EkehCCqJoliGDIlARjqhvnmJVxmkr40mqwB+FJk0NkYH2TEoCeI++4eHByXUg0ZbcX6MbNtTWVaKjLm+MMnBr4qkazk/QFSgbfQAD7Re5mdiscYKHMvXSQYpaYikCJcZVsPqLJuvJS4GHnblgSZIX7Vg7Vqtnf5dn7iBBc4yvRwiRbq3PKqHlmmccZkUbqY1qB8EKZtuTxvGtk9P5e8b3H7tqt0hKIQQEAgLthAD+UttKndWhzGRjOBGbrLVxqcPrigkCeNpyRlYwMULMIAhdVVFCKCG8my3S4rw1B8Gws56dEjrjXCOrfnhOyeGdSlJee8eeoa3//aHBlmzP3sbsLt/UcYQajcOWgCERCTBW3ZGdcxSCxazPKEzYOMEZIRSlRepA186iDbaqCZ66zWOy6qVXu6/A/v+7/+P392xCwyWNa8rlb05PTX5TxDkCFh7qOxY8eAl5TB4zE3NTVDG1q7kDgTJhItxzejeygS1x2pdUSj8+mfmfHnhyUL/OFuk1QQICAYH2QAB/3u2hyFjb274AABAASURBVGrSgtMo8jbqNWxTZkPMIAGSYltY6YGJAQfOZ46CFNVFqWZbLYU+UIKRUU1VN5wWicAWY0ziyZ7vUtuyT0bH1oyDnAagl4NKSMBN83TVBcYKekK/p84F+mXI7IwpEAU22hgXibXltJxeP+bo1t27l9azvfGiNaP1nD4l3h0n7CAQeejOxMywhFTVE6I6FydFzcwBNjTL0Z4N2ThaX66kb5/KsvfddeDoZfDu45mWIQkIBARajIBZzPFD36dHYFqiknC80ZqkRFgklQiwbpKwEjsEafNKlOpqqmspPCUmbha3+qhqQPTmMcJk8I+gm3p8DuW66udElw1lfj21KJQsDwPaJ533NdVzIdTAbGCO6CTvvNmr9s/I6mcddC6RLdoYzBlJRFlkjcTx1uE8e/Nnxp5Y8s8WrK+m384atXuZHNQDoUNZZhygaGETUo1a8kyZtc1gfgX2eLZMUbwlLpfeNzHlPvT9p4Z2gNStXh8kIBAQaC0CprXDr87Rk7iRENu1lu0p3g2zLqfA5MQqO5vRFOVtEFUTDz3gkBLjxKigQPMC9T0quFj8efOPBkY33CHSksW+4ccnvPMHc+cnwWLQeAEiax9CHsbCbD0pBOaTnhsvIHIhqxhAdGtb26I5RWxLSVK6Zngqf/nOfVIqLlyiw/Xbe0fq043PwUMfNt5h3lRbiEboOauGwIqTpVmORsj4mTrC3BJbipN0c7mj+q6R6cZ7vntwcHsgdQohINByBJYxobccuzkr4J1JyJg+a5GiFyyGRMyITEXAIqs5leK8nQ5QSolbVbJY660nirwBkaECUQnBoJKjZP2X945tSx871pL36G/ZtKm2ubcy4L2MEvCESk/jqydzEhgMdtadCORO9ADHlQABxSD0GE5whFS34B2AUEJXgjfYdzdx5fwhb9503+Cei05cvEQZV2982WX5vVaU0PMZUseEzYyvOdED7EMlkeZB4gTx5PHO3VOzHkaRRX2Cx9HS+ZVK5X0TY/ltd+0b2YL7WCsphIBAQKA1CIQ/wBbgnhmO4Dl2ijCCTgFWT5mlCOQLnYS0RKU4Lc6auVYfVadCS2RYuFj/Vb1CYyZCpDiyJYnSy6bSqCX/oQaA9Zf0dR7PhQbAr3jsoHkHLuxtdoOsmlycaF4z+n3vQjCXikGO7Qv90RkLBYziZJJyKa2+4HiDXrtzYGBJ/+OWS6rn7W80MnjpNKIPG6ofFUFzeM1T5JsHtUfncvYVQpFHFUzAsxHOtIHOMluOk/SCcrXy3omp6Xd8f8/QZpC6dojWIQYEAgJLjYCyyVKPuSzGW0wlv7JnKnLiqlgXmclgaWyugeABIj0zOJ9hj2IRPUEdKEe9tqEWBehMhQpE0LwpDCW1XODJMQsIXii1EewzV9e860PTlsSUsiFsuz/lSBoETGWeWhh0oKLdqEeuKYqK2dG50/8lT71x+K+kxJ5bIYcLYlRaYEScGLHpxvHcv/qhp6Yu0euXSnbs4Hqeua/mDfcjA30wTcXQDE1ZJ5TVF6cT4QSZo5xwlzLw00oQtiYg9qKChKMoKpUurXZUPjham3zr9/cNrEcbrSzahUNAICCwdAiYpRsqjKQI6GJ3x77RknNc9Vgkm/+9p65/KqRrJ8nMPwIJsLfYzjaktUKePIOeSGmEWhJUDx3es6fc5NBHdWHop14eqAz7zx4NPNsI5HXBF/ZP9MFm0wplS3kyaPLawz6fxrY7OB1KsEBXqMyaPyEC/SEF0c2mqBRIswYW6RU4R1nh4aItsihAOWwmEKNjQw7k54CN4mPgmTMa5UW5JUGdN1Fqyr3XHs7sW796dGJJ/5vZtT1+73S9/vd4yDkORobGHg9f+YwgT0K+KNXHkaao/jp5LFwwPo5Nm2GLICcsJMbEcRpfUemofnh8sv6O+w4c24Q5bzZFmxADAgGBpUFA/1aXZqQwSoHAJ4gMi6kwRanHotgkdAJtYMHXFRKLqi62WGVRxiDziBikzjgTEIXol4S5aEitCoyBBTrk1pESF0M3Ir2VYAPynomcsWTj0vqvH6xv+Wx//5J+CAzqFbFnqDrE4h6mvDbIkgFLgZYMDekZIjhvSpPGCO0IgQspajBXOEE7mhEhDc2jRdaCzC3wYAgeaYCPBenDQaectc6Qx5Vi4KXHnZvGpPTGb+05euPdsnT/ic0NmzZNN+ruC7WG+wExe4N7zbKDrSqFduShq6iQ2mRQRwRTIAJBnhj/6EQQ4KJXemvTpJxeV+ns+Nnhsdo7vtMkdXOiYcgEBAICi45A+INbdIhPHeBCIuNd8dOZfGrN8jvThV619nooRHAUYnikBIkMl1F3yUAet+T76DfeyNlFvaUj8CEHmBn0VTiZRQq9aFYEWp8c9Vyl2VpbNc9ObnOuee1BpDnlMeVw0/2O43X/jrsfOnTFufY11/bMLJd1bd5fr2d/S94dVUQEigkxsGDgovqpvbMC5DCYzAiSZ0dUol/0gKWEbZKk8VV4p/4zjdHJ99+9++g2eOr22ReFkoBAQGAxEMBf4WJ0G/p8LgQG9oDQDaXCcIOeq9E8y5f8cm5SX5MScEIQZmI21uWNC7O6aQmhKw5bu5Pj8JL7mcBZWgABB+H4PBEXkMrzNDv7anSGKdeHIEs5xYZ6ojR51f6xxmu/8sRQ99n3M7+W27ZR3XH+1UYju5NEGh5gMB6+CPMlqiK6R0JN0cpTCnDSjM16RjsVKh4GhAwRmyRJ4qsq1fLPjmfZB76/d/BCkHpEIQQEAgKLjgD+Ahd9jDDASQiUI4KfRGnEFJ1UvOyySkwMBvDE2GomEsbiD3ePsah7vFP2HvTORDUn2z712GDLvo9eLqXH2fm9PncT0BKs7gkqE7Q9IR4FKoJUhZ4VBCUeMvcIKNC7Xg+vF1g5GxmTdm4ccclbvnl4/GVLtfXOuPu2XrbxcK3e+L9Znj2hmAgbYDGrIewUj+11D2UFeBFEU7xGQCsUNqNCMitoKsgXgocWsvpBufJFlc7qT03kjZ//4ZOHrgKpx80LwzEgEBBYLAQCoS8Wss/R70RKBjyYMIP5aDmGp3VmpSgmgj0oFD2DgMiV0JHzRZ3p/e7h8S300GAZjZY8Tg91jnmf78syNwxNCWqREAJ003xToDMmhE4WNJmNs01nz+eVojN9+PHWkrdpKU6rVw015PUPPHh0yX5B7irmxqae0l1TU40vMfOkEjEVtyMTkChkluKFUcKwWKWJHOrpFAFZw9knIjwYkM69MHm2Fr76BdVK5f0Tdf8P7nyi/5qlemihEAICqxSBQOhLPPGJIfhIEhshs8RDL9BwSoeedH1nzaJX9d+UpIh8ccZY2BmemmBxTyvVPp9Ubql1JC35+tptV3Hjyr6u/ZTnT5E419RR9Wx6nGrCrBQPILDg2bHZnmYI7dn1Z1Oio2B4AJdxQp4jPNl5iiLbbavV1+6dorfu3De8ZK8mrlpfHcqc/7vcy0MntIeKDO/c0NP2CkHhQk60ejojBE+eCzFFGyaHNFfBPeCMZU5LG9OuznfmbD9a2zvw1p27Bpb0+/cUQkBgFSFgVpGtbWGqErohibHeLV/suQklY+EuOI71HKs7EvFCDOOIsbgjHydp1Tl+wf+87+hZETq6WPB4Xkd8FA8fTwpJnVRXldlRND8jMlv2jHSmurj0GVXndCogSx0D3it5YTLACJ1GNokvBLu+4atPHb9CBBW0+IGZs4vOSx+anKx/1YuMNEcU0pty1t5mGWOKtaR5dvKRobyKwXxrSjgXRnuDOxxlHudK7iaK15UrpTd5a35l2tffvpQPLhRCQGAVIaB/v6vI3NabOm2HjJB+JgoubOvVmacGTFiziWaWfJxRsaAj47wnwqKem4SicufFDx6fuvwzhw9XqAWhYuMjUq/vco3GsP6oCuiGDHTEVgm2il2hEYgUZsgJ3xTaF+ULdRAS8haCcY238M615xwHgS4cx2ly3XEfveP/7B7djsIliZd0do64Rv7prN6424r4CDqSBx6YO4aejMlFMXQR1ICocY4T4ETwygkBd3KBGLKo07OmAGHc3h7z7zWlhMSUq1HacWNHd9ev1eq1n7vrsWPnyRI9vKh2QQICqwEBsxqMbCcbYwaNsEQglGWOPQNWlWZyYrlHETw+EICWM+VY4dnGXbnji6a5s4rSJY+vPq9zZNu6zj3e5UO6pSzqKUOoqWWhD6aFwEmFQGVajCDAphgDGQNRVsTdQDp2HEd9IPVbHxgYvXHXruJrjYuhwil9Ytx882b7WFarfcHl+SCebgCJWg8CFxVtPpvOKq9lRKeeEYKWaIKUIWiBRxUikLoUpG5JOCrFaXp1R2f1H06Q+5lv7BkKpE4hBAQWDoFlTioLB8RS9TTBzMazZaZliX1zuce6TwzfzICTDBk4deybi7gmufFEeGKxZIm8ISfWNNi84Pe+s69Vv+vuzu9O9hqhPSD03MIDNUroIC2GHaRGkQaDA58kyC5Q1CFkZixLGXK+wK45tkPC1qaVy8YkfvcPy2PXLtCwz9vNdT09Y9yof6E+Pfl147KGwbwRSFhfCfgzXK11cqJecyjRZFZQx8gzigk3hQiTMCNrIxOXLih1Vf9BZuhffWPP0ZtFRIHHFSEGBAIC80Eg/CHNB705XosFLALwiHPsoG0uUxOYmscmEWINJ/24GYMYmA0xxNiYvPfnPXJ0bNNOkYhaECIXHQad7MHuQc2qbsT4R4UQqdazeSYqSjWlcwpnbqz9qRDwEio8c2L8a16FEjI2qiRReuMjh8de9a3+8SX5zAEz+yvXbtrv6o2/y73fC3+ciJmIIEiZWXN0SmCcqSB5Zjy5mHGlShNeRlMmYd2Ox2NDZDeVy/F72Nhf/8ajR16+c2dr7gsoFWJAYMUgYFaMJcvEkPKEgaMoMThlWWOvBKSOlXph6u1qWnhhsM6zIxAnUc5kyVBMREm56/xGtfu1g4OT63C65PH8kY5h50W/jz4SiZC+LzbqqZOAz1XOpBLISCAgqDO1OlOdXm3hrWqaw211aOyBjkreJDl47Ew2TTaNx6UPfubJkbffOTjYSUsQNm2i6W0bu++anpz+tM/9uM6rEq/qajA+OB3HZhQkKgBtJqdnpwoDUwbGs2LwiGcoB3oCIWJmYtwVbOPuqFJ9talW/v1k3+GPfGt3/5I8xFAIAYEVioD+va5Q09rTrClmcJ+PmcB07aniWWpVLNtFWy6OemAs1ETgJ9KAnQgCs4OohNI46cy8uTHPa0v21SzVYVb0Z2Cv3thxoOHcIed8pnqpCEidQED6/hgcNNv8GSnjXAXJPKL2oKh5zRT94IxxAtGxCyGK49hcMpa5N3/2wdFLgCEaaOPFE8YtWV9THmjk/ov1TB7yHn66ABUoVOCDPOnMqiYqNBM0/0xB1TOLYKVejZqnI8YkYUtsbCVN4ht6uysfrTn6p1/b/dRFT7cKuYBAQOBcEDDn0ji0nT8CJcvq0CbMs7Qo91c3AAAQAElEQVQ3/z5b0YM6rMIMsubiE9u2WPSJPB5Xiv9AhogMMUXekfU5SZxGPu667j98+9gmkJRB9ZLHPkP72ftHc5dPk+oFDQwx/hExI4XQKYFxpoJknlG91ea7eyKBZ54bQw1L5JG3eYreY8oNQfDCIuI47Sy/fMRGH/70E2NLQnD6YzOX9HU+XKtnn83EH/HQiGC6etesnzfQ82eI4Fzbgf6LHMOapoheWtwXRgh5T4xdG4t+LHA3zpNxQow7hDiiOlx1KZW2VHo7P8JR+fe//uiht999t+jGDoUQEAgInD0CWELOvnFoOX8EakyMxS1iIkRa5qFpQvOopjAcXRCSbwoMJYaXh0JivLhOy+naQ+PTF3356NGW/GocZVE/9NmH1wE11daAVA0zJgKCVMtOFcapCpIFiEVPgo4wlj4MCQoEeQaxqRDyhDITGYqs6cHpy76+f+D6nftk0f+3OkLYvr56PGs0vjg13fg+1KwzMzEz4UDNgDypNM+ePqI1TrTmZEFR0frpMrSbuR+Ke0Jr0b+JIsq8N5iXns5K6VXlUvpvjycHfyp8X10RDBIQOHsEzNk3DS0XAgFrTQSftTPniAQrHSgQy7m+Uc2xvOkZCpErKrUBvB7Rd64QQkvjLEiSF0KVOfXB0Ml4C+9Ldcgot55qlqgOF92rF4a6BB6nYUOZ8TQdW5qMY6qLgfaxmWjIa475tCXv0X/8ss7hF643P3BZ/qQwHECQCQsePhxwh96RJ4rhPbJiXojOSU4GJKSeJoGP5gRacZHiZTGzlizGs57JQAghN47EeuBD2M1gihpMcZ6YrrTzikbU8Q8/tW//q3bu27fopM7M+QvWbXqEGlN/LLXxu8lPUwa9dNfF4CGNgU8TA0ZiIAztNRocZvME5JoCMylHsWNDjizEkEdegIIwKoAroV+TCyX4ZymljEqplLqurqzt+42GRP/1i48ee/vO8IE5CiEgcDYI6F/i2bQLbRYIgbzORli/Z4wF7aQ+nz7THGPJ46drNasidGo5LX1QNXhGC12TVQNhHGeEi7rmEVnSOikactEIfNn3G194vOd2AcOjZCkjM/uytbvJRLtzEbxOB5mLkLEWakA/5HFEHlEzKshqPCmrp3MU7YUBC5MCw80cIYHDisnF+PogZFCgdVEUxZUkvq7GyXtN1L2DliBs3sxTl67tvAde+mdByAehCqkwkSaFUBGeWaLnRcWpB1by1zoI8jQraIUSelqYmI2WIoM9ChttqFZKP16qdPyb+trDH7zroQNrUBliQCAgcAYE9C/oDNWhaqERgPPFhn1Lvrq10LbMpb9Kd9fVx0388pcODy/JJ7ifqWP3pO+nxvQuyetDHi6nx06JgW+sHqgD2WQQUGtxWeSF9L0v0Swp0TyDx/UqSE4Tm+MyeYznUC/GUhxHPZUkeuPf75v4sa8endiA4kWP2Hof2Lyu8qnp8amvWdeoR5IXDxyCBw6VkxVQnT0KVDSP7IJETAP+TrgcJ/bazr6u/1irlP7wyw/3vym8W18QeEMnKxSBQOhLPLGWibHLaJd42LYZrhxH3dNTjSvHxtKW/Azs66/dOHnFlq49zHzMgjAZ4jEhKgVIzEXSPAioVXNapqL5xRLGWEwnEybeKROD1qqldI1ruFd+9kcHr1yKX5FjZtngOg/mjfwL9Xq+p9CJn7YbvP60nieVP91iwXJQhay1Zm1npfy2zq7yb413Dn7k+4+MnfIDRQs2WugoILDMEQiEvuQTWNPFcPnirgs4PFs8llBThM4c1NTiomKbuRZV4npnx+v/0Vd2XbxTWvNjIhVn78mnx7+VZfmIE9XNkTWePFTNGaTKQgbv1o2aBhFi8ihHcmZTz7JWdwaEfdFacJwVjwE8QQnsGni2lAnhnbJAiKu9va+Ucse//F9HH78OBKtK48rFixs38uRla7u/kY1O/gXVs0OGhPCyiIo5R54QZFagjUD8jBDwI7VP05m2aDqnqGPUOeGaTVNKq1dXenp+s1by//MrD/a/+f4jR1ryU8JzMiRcFBBYAgTMEowRhjgJAUvMZHTpo1UUsNJTU+B1cjWJ1o3n+fVjx4615NPupbz70HTd3zPdcIMgRzJGZ0X1w5RgenA8TZypP03NQhSpHur5al/ASB/6iJmhm1IpEcpMOYquds6+5SsHpjbREoQLN3QcFW//fqKWf1mIJwgQMA4qzeGlmSzqkclaS94LZ3lunHfd5TR6c1d3+beHRsw/+v6jR7YDO6ZFC6HjgMDyQSAQ+hLPlcNSbdQLW+Jx22E49YAzjsmX13ZMJ5V3//Tf7m7Jf85x21XceOmmnvsTL3ut93h1nsMDhyijqqsJsNS51Mcu1VnZYtZbR9XcIjrRrlWe7sAjq4JEY9GGKUc+Z0MOROZMhDSmKcJ5pbNPOrt+/u8e7//A5/uX5qdhX3f1pj3G019mjdp9seQe2hAX3rfq/XyErvVn0w4GnyFyxhRTBGJPgI3lGsdJXqpeFfd0/tupUunjX37s6D+++4mh80HsYT07A46hauUjEP4AWjHHPM99yFboPI8xdVlX0S6w6JJ4Tyy+r2GjHXv2UKLlSy0Vm+xjw49CkykPfVQKHaAoz7Ausk9PlJJ90WDuh+YPsGivz+xDQJIE4UKMtSAvA6/UE7xScs5RFIFKvaNqGq83cfSOz95/6Iad+xb/++nMnO9YW360Pl37Ypb7/aQTSEsbdEjnPAYVSpKI4jgm8UJMVK6U4hf1dFb+7RT539n58OG33n14bB3aWzReFjEoGRBYSATMQnYW+jobBErwtc6m3fJog0W1sEdvpJNFywsLNDMrghIQY4RX553dm3ZMxV3/5IE10y35/e7bruweurqDvy/10QNsGsIRkQVFWKhokBLDI54Rhs72aWpHi7lE0DlDntEPoyt9vmsKTjRiPPAVkbHEUUykYtAC19dNQqZz3c1U6vjVv3v0oavvELF6yWLK+X3Vw5t60ztqE+Ofk7w+EklDYuhimBWpQhZrfEbHEXYEIoMMRsqdkPPAEdg4m1DDpuzj8pq02vnj5e6u/zY1Vf/dnY8cef0D+0d6QezFVXplkIDAakAg3PAtmWVdploycBsMysRs4Hk6smK2/+KnHt+8FKR0OsMjy3s8yW54wdNSeIBoBTItOFcfPvQU0owzBc2TeR61LwjiibGKfHFA37MpsjNRdxDYwGsXT8xMXWl8DZn0HZv6h5fktcVAX9eT8Ir/plbPvocZzEhxgm4gTWShL+IJW1C+kBH0PdPdzCCaaImmKsgzs41sdF6lnN7W1VH6rdF6/gs7Hxq4evfx413QcdEfeqBCG8ag0mpDIBD6apvxFtoruNuMhacJD8txiZJ07YXYKn1rY2io2gq1Nrv8YduY/hY1GsfgoOMBw8NLB2ESQx2QJ9x2DyF2VAgJzTto1/BugUIxSnGKTpsp+i+IEinKTo4e1ziINYZiT+SS8oaoZ80/uOOR0fd+o3980b/GdSu23q/o3Xi3abi/kFr9odjnrvidfuhroG5Tf0wwQXBOCM0yKuykOQYlc7Xdw0vXLgCBjkAWGCSQ2DOxN3iosOQ5JmfLaR5Xro06O/6F7a7++aEh+rWdjw9cdffhwxUQO5TTXoIEBFYmAuEGX5nzuohW6Wr9TDnDcM9oaoylRg7vHO+Eozii8amJ6//ppx9fg8WWz9DLolTpd9K395UfNsRHsX0MelD6oIK2Ve1i0JlMs6YomdNBu3mmPD3SbM1M18UpDiBLMBWaCRnsagAjgpdMxhoiJXZre0jkzZ+6v/+mpfgKl36VbeP6rm/Xs/zTee4PiXjQKRG8Y3pWgPqnAvmsFudeoH0WV2lGpTgpDjo/KnrCwMYYUy2l8XWlzvSXktT89sS4ef8Dh8d3DIp0iuijpbYMMh8EwrXthwBWhvZTaqVrhAXw1NVo2Rms6p8sZzKAUam3WVMEPnDG8KasgTclFHWtefG0iV/zDaIUDZc8ruH4fnLubp/l455zeHkCMRBLxluKIJ4deZMTMS1c0L7U3QTrmRNCQKcpjJEY5U8LWkEXl+fEuM7ZhMZNSqU1619mqh3/4k/vH7thKX50ZsfaysHejtId09OTX2SXjxjxBTsa6Pt01LOTRa15uvZccnplIbjdihQXz/aM7IkoqEckhxJHTI6NfiKechP3mHLnG0o9Xb895tx/fXD34E89dGTisiMi1UDsACvEFYWA/m2sKIOCMe2JgC62Kh7qWRuRx79cMuquVnoadXpl/+PjHaha8phevm6AyTzQyP0xIbATWEP1VAFvkgqBWFFH8w3ap8pp+9GKgpWKDChJ06dbQjNSL91YS5lXFAnnTMRMSRRf6Wz0/j898ui2pSCpdGPHE3jG+dssc/dA5bq+3wdEM1jRggedA1iq01CIItMUnRXBnSQkaKRlzEXLQhcUIW2eo8GaUpq+plop/frxyenffOSxo+955OjEFYfHRD8Vnyy40qHDeSIQLp8LAmYuF4VrAgLnioAutvptsJwMOROTsw1InVxcNpWo79Zf/fpjFy4FGT1T79uY3WW9yX1C/LgjbngDb7wgBUNWDEUQMQIPXUj/0XzCDLc8VxeMEQrGQqpNT/njBHNavTBOqAbv0ztHMc5r3lC90tMbd6/5YN2UPvyFIxOL/j59B3N968bk+97J3/jcPc4iueqrYqDTgkbcNDFsjCBqv/YvGED3+h0KcsyNK4QwRwzkCHcYkcUzT+SEIm+JfUIiCWUuxj2Xbkg7O3887e34jwP1xn97bODoP/naw/03PzZWfN0tQtchBgSWLQL697FslV+OiifKCuqeYoHWhelpGxhZLWmKNiMsT3qGRLNFfbMc2baJqvfzKaNWNEXEwRYVJsMRZbmQjeLOSeZb/ri/f9H/i1A6TejrruxJme5nL6Ps1a9TlD0JQ+D/IUOkDEJnY+tpBpgtUgg0r+ms4Hw2e7oU1UXUOlVBvWHLXHxH3Ysnwl+wQGUbmbJYfv3ndx18+d1PDHUXFy3iYXtv78iGjtJXfZb9rXNuL4nPiYQ8FEVEbmbw4qQ4oEyeIXpFU2ZanzaBlbiu2U57OtEIJ4w5McBDBTAUVR5/WzpzqC6uKwpxMMW7dUuEByJms6ZcLr28VC3/47Sa/MbB/qmf/foj/S/Z1z+8bWBAOvBwyRTCikVgpRqG5WClmtaedsFpEDbssCQXCgrruqHTYIix/FgC2TG8RPUU4XkQa7khC09R350Syml25Sp6WOrD0/oSGQzOkDNE1RViVGCf4YwsvHP16axPUVKlSZN0TsXpu//lZw9sacVC+o4tXcc3JO6LNJ09GmWUqa4CnDNukFjo6yMyeUSsDHEGU89UxZhdgzlUYeCmQkhVBCluCvKYa02Lc7Q/eThnGIpRURqDsCLLBH4i3BjkVdfYUtTd9QJfLv+rP9176NU79+1b9IejSzaW9nak+f9yU5N/Ja5xCCTqcfeC1A2pHdCwwIxxKQNt1gAAEABJREFUTxsIkSct0AcQMSD3QogAC6mtejfNCtNMQCZHoYpHXiBaowkuJxXWblW0QoWZPPBywEjbG6Cm7bXKo8BLBB0TyiklZ8rdttR5a7mr69ejavW/7Jtw/3rX8WNv23W8cdm+Yelpxf2oegYJCMwFATOXi8I180QAe5Tz7GHZXm4Yiz0WVQNhISzxWIlBRqU02VJjfuU9RFErjEti82gjqz/kfDbF6vmCNC1YQLwQgzlVWqHX7JgMymPoREjlhJDyYyEuy0FbhrpKpaviOP7Zv989csNOAXPR4oZrt6w9GEX2b7JG/lUSGS5mlzGn0BVkSMRMTAZqMxFyUJ2KIDieJFqLkiJqsWY0VdH8/OS5e4F6xMxkjemspMl1HR2l91U6Sv9meHTqN54cGHz/kyOT1/aPS9/dIjGFEBA4KwRa1wh/aa0bfNWOLOCxVWq8FO6YxfvfiBj7FI4d5YlQqdSxebohb/reo4e6WgHNL1+94WjJNL7rXa3feOet98UnzgWvCDwxeX0QwcLfCt10TAuCjCBgRuiCG4gJKYHMmazHDg4QNZxSw0RRR6XyGpukv/rZL9x3CUgVLWlRw4sv7H0kMfR/pFH7vpVsWn/ZDcQO7hYieMo4AEELXQ1SCO4BJqQ4YyKU04mAK3AdbNMSrdR0XoIe9cnxOfoCPngOkWJMUX3YlKLIXFopR+9My+m/fGqo8VuPHD7+c8O7jr30keHpbYcPS2Ve6oSLAwKLiIBZxL5D16dBwGHtYFIX5jSVq6AIPIll05ABQeoqqouoh93ORJSa5Mp/u/Opl2GRbcl9eVFH9UHv8gfxOnjKQjmGXmwMeRCpF5y0W5zVCSkDTxEmtil5E8fVpPRKSUs/96WDYxctttrMnK/p7L7bCP0fn7n7jPgaUCOexRA5Ut1UEeiqCWk6I9qOTirQYm2jKS7T7Dyl6Al9NFM94mQmNkfX+dX7UFRXtkSY+CSONpXK6RvTavmX48703/Ufq/8/D48df8fu4cb1B45NnbdLJHw6nkJYagTONF5LFs4zKbTS69KSYEMXbulKN/S57IM3SR4LJlZqAEFiLeXMVOOYOnvWXzzp5N2//uih3ue6fDHLr+5d/1iFGt/mrHbcisOrXkdY18nrIg9iX8yxz71vpR4QuOpGhhxeMAtF1MD7/nrSQb7UtaZS7fjAF/YM/MzfH53YcO79n9sVO9by2OYe+bzJGx9ztelHjMsaVrvwoGrMNUFHRmqgLwvheLIIalE4S+qMayBFI1rAMNvnbFp0DULHw5BnLubZQROPgX2RGvJxiTgt90Xl9Ja0WvqZpCP9f/vHpv/97uGpXxh4dODWh4enLwheewFkOLQBAqYNdFhVKuTFWoV93FVl9dPGGiychAXTwVXHZgVyhliUkDx5kLtNq9f84V39bxZ1N2lpw63buba5I/6Byxu7BYQEFbEdyxBZWkVOMxq4EITTrIAn3Pww2IxaHsUM7HLcVvpJbo9XGWISMiZdRxy/6ysPHf/QzsOH16HZosYLenqGezpLn/WZ+6s8y/cQQCQ8tamaqqNg7psp/gigiZarILuo8dljPF2iOdED7kRiQyoCMhd46SqqP+wgy0zWcppE0flpEr+uXC79g7ic3t4/MP5Pd48NvufRobGX7h6pXfTo4GD4JToKoVUI4A6e59Dh8nNCAA66eGHw+jldtnIa66JJIEl4SeBxrJeM5dOSCOP9r6G0t++Kiaj84d/YM9RJLQg71ttdZcq+7V1jSPCo4aRJPi1Q5ZQhPc50c4ORWugUzQhOSctVnJ4ATaaE6s5S3ZQoqXZdFFXL/+jvH5t8/R27JCmaLOLhsr7q4ZTNXzXqtU+6rH4QM+2gEulce5CizMw/IU9qjMoi6nNijOccBxXQhdmS6uahbE5MOVKHMvI4U8GDCZE+ghrYEjHHyZq4XLm53NX5c3Fn9TcGxt1vHRqc+NX+Qffu7+wZuun+41Nbdouki2la6Dsg8EwEzDMLwvniIlD34rHI1Rd3lHbu3WNZdESGibCQGrjB+oEvS0we5xkWUbGly37nq3teTi0Ib928eWptHO+s17MHhKgGtTRCcNYCfZ4eEng9faLcgjPVSQrdxDl4kSgWPScqHgCAJcUJRXGylZL457599NHXL8XX2W64sPdAxNFf5w33WfHZUSI8w0IjgejWQkHuuAsEAuVxxNzPWIMEzQmPUlSkOC5IFPSigqTZ78yJwT2nUpQXh0Ir6ETktQ3w1EWSUcJQlpmJ8PCpdRBmE6XGxluStHRLWqn8ZNLZ+VGJ438zMlz7JwP7Rm7bfXzqxXsPT19w4ICUi+7DISCwiAjovbqI3c+765XXge6Lkq2tPMPOziJhUI0BqWNdJDIUE1OElTEiwnJvKYsS4lLXpqmGe/Ov3HWgJYvg5Vuje2Lvvoqd6zHCAm6ZQDBCSKh1QUfXP1eIMiKwm9UFPiOpjha6apnowVhyBu/U0S6zCdlS5WW2HP/aJ3dP3qDViynMLC+9eM2jkfF/mWf1r+c+G1UA9fkCKXkGaaoZMEl1FWZwpCGBriooLtTTFE2L/HwPxTgznWi/M1kkWoNkNkIXNkzMhhiimKpmBGKHgjOtUAJ8BQZ5ishxBJsSI1HSYZP04qhUfn1crf48Jcm/PTw2/RtPTU780uPjAz9x5+4jL3qkf3jb4cPF//zGM52FJCCwYAiYBespdHRWCCTOe2FSD/0ZK8lZXb78G7EjwYYmCW49sVjfHTHOBQupIOdzR6m1FJeqt/yf3cOva4XB6qWvKyd3ZvWpJ63P8piJjBcqFvVWKIQxi/fmHsSHvAN0OROIhICfFGLEkwqTJ31oUtJUIQQWIs/MaVq60SXJP/onX3/8ZhQvasRwrm/72vvxwPZnrl77ppF8HNQn4jO8ks6gqxDakHfQV05VBaYRkxR2aXpq7bmdCZqrIHl21AoVjEWFoImeqyCr0ZMlB/EFeRvyeOjw0I6kqWXzOjygoLHirPcIdDaGuTOKoovScvVVSbX6U3FH+V84W7p9cML9sycm0p+7e9/IW3cPTr5g38DERpHF/70AqBfiKkAAS8MqsPK5TGxBedrpPZzUOh74pQXDt3xIJRv9FTbBwkjwbrhYIpXkDdZIi6XToJSoo2fdleO5/cDtOxf/F8/oNOHa880Dsa/9wPh8gl0u+t76NM2WrMiAQCKvJEKkhI5X5IQdBKUWsriZmuKhDwgSN5gHu3jTvMWKhA1nFFWqnV1vaXD0D//Z1x+9FI0XNepvvq+5oPs7Mfk/cY3ad3w+PWnESaQKeeiJ1wRsDBGrXSpUBM2hlHBHEFPThqJijofT9aBjqJyuSy2fFa9EDg9ccFcKq0aQk/UFzlCy6KZ5jRSnmteco8iKiXujpHw5tuRfm3Z2fCjpKH/UWfMbx8em//WRkYlfvG//wNt3HRy+btfAwMal+B/zCmXDYUUioH83K9KwdjUqEwRjG1h683bVcTH1KtY/GM8iGAaLOlY+jyVQid6Tw7JpCU4c+bhMGaU3/48njr8aDZc8qpe+xtCn65Oju8Q11KUkKhZyalEQjOshzVicKXYAVPBQRDPCMykpEWqjZnOyICbDMQPXiq12vHY6Tv7x3+w7dtlM9aIlSuprq+5bxmf/3U03vmZ9NpZAORA7nFlPpsCUoa0KKqCJWilIlyICQgKEMyLNnQMMrvcnY9cDWejW1EvzzZyHaioo0ftYBa1whmOzheaJmCyZmX/FSIbIVNjYTTZNr7OV8ptttfqRBif/aqThbj826v/ZQHzkZ7+/f+gtDxweueGxw8V/GINrKISAwFkhEG6Ws4JpTo1Oe1GGHXfvqOFF3GkbrPBCXTxPvukEi55gURclIlRawcKeC+U2Ya52bx6ZnHzbr9x1YE0rYHnx9vS71jd2krhJqAkVBNK6yCcNrZoUby2A2UnFRVbbzUpRgIPSCnFEdY6NlCobTVJ657f2Dr3zjgOji47tZX1941vNuq9FOUh9qr5TsmzCYpqtgberrzKgHynAuA9IhZqkiGSB4iwamp7apZbo/Tgren6yFK1PLpjJQ31qCiFtiuhczNQXhYQA3seGSWEQF4WwmQw53OeObSxRvJ5L/3/2zgIwiqMLwDMr5xd3EtwJTo0a7i7BCU5bihR3glNcijsUKW7FabCiQQsUd4vL+dr8b0PDn9IkBAoVOsu9zO7ImzffzM6b2T0SXSnOaKgDj+Y78Ub9AAcmEfEucdhji63b6dsxDS48Sipz5W5s4J07d9T/JINBK/1QAhkSUMdxhgk08u0Q0Mk+ioIVCeYxuNXfTh3/ZK0MTGSMAnMSlpECIoOTkWFfjrEE052ARIVFrNaIXE4R6TQMwxo9Kq6/n9jy72hT9YAAm4+O3SsS5bGCYBn2dxjxvE4Cuz8FdpAgCgjsClX/oYADlBiMRAYh9RE8QxBiYGSlSWo65HGpToTlEThVpCWE0fI6H6LVNztxO7HDlpvxIc+reUsnISHYkdMYeJRR5LmSIBwCc+0cAmsVMBg+0DhweviZPLcBGpKa8DzilU+eq86gJNQGsWoOqAd4/r8qNe6ZMEhGGNbefxA1HgQhBYqpfYMgxEgC1hI8mldFhlB97QFDGinQRzJUqIraJ0RNU/uEYNDAQlmWxQwP7911OXVaY2mDwVyDN5m+cPKa4YmCPDZOYkbel0wDzj9M7nTxSXLNXx7HFbl1K8Fdfd4HjaAfSiCVANxXqSH98RcRMItIgfnBKSMk/UVV/qOqwTCBMQR2KRgmQpjP1YmNgFNHMDlicPAKw8MZjwjkw1jBvNkjb5JdbNT98A3fv6Mh7+U2nkNEOU0Icf0d9T+vE4ODAYcAH6TetIAOqVEKLINkBiMZIhVIhAAe86JneaAwlEKqM4F3BkgEpupfamPg3bWCWE6jMxVheG2HI/eS/5L/zhYUhO2qU0eCPF9wSqeILIsM7NIx2KkKgragPxzkDzGvHPFM+e+KqVHP5P/6n12jVCuen8NjdwZc7ouCwQUjVVIzgg4IAS9SVEcNTl2BVxwyhDDUEfktjajXIApCSBWi1gT5UKpwCMF9gEEQ4hiEeS2r0QZwBkNJjdFQjTPpWvMmbQ8HQoOsLmGUzaGMjFZcPX++9KTB2XvxRS/DkxYYoyzK4KBR/x0CzH+nqf+Mlj5MhrmXwTYiMzIHdzUnK4gFD49hh6re3xKGqQImBawwiIVZmgPPzxCCCAIHSGAiAIHLLBsjQwYlvUBuqAo0QHk4RzChI9jl/UFAN1SEXi7PZihCwFZQDHMe+Dwopp6rjQD7EQgBt0LgWkkVlFq/xDJIZGFHAumgBWECOVIV6MEcA5JYGYmMhAj3TDdPEKPFfOj315I7Dth3y101/68UdZfuKYkrUMKDM4pkFRiWIFkB+whCzyZfFuxmfjsngE5GBEM6iPoEQgGvq07sCkptLUKvGRJgqUDZNEFwgGoYOwixiioYWGIkQx4JBKJSeatjhzQa5JsAABAASURBVIG+ZrAIJQSkOhgFYyRhDTh6A0c07vkE3rPLzrtsx623o/0h01v9qE69gKf/ISw6Zsoux3FOcYksEqB1ItQrIjANQoRgWID9z7iq568rCPoJpT4FgL4BJfB5phvi1WiFYEgGgVrlDEQByxSAlpEQiCcKRihVoDBAxwpBz0RBKnf1f+BDBSi1UkJ+CyFv6gf/Fk0QvIlDUBQEziGbDBcK6FWFEIYhmNMjrPHCvC4P1hnLIoOpLja4fYnczSMsCjc5RiATD95MGRb12N7jzBNru7OP7Y2v3LeEqu/h6RftUmH/J34w/4lW/oMa6VsM7lnM2BWERfANSBWM4AqEwOyjwIxGEIZpBBw6hCwIA3EMgxFmGYQZiGEYlNWBMUYovaDfH2qSquFFgVJIld9mmd8mH/JCqOpSc2HIC6I6ZhCkCsSg1AkSQSMRgpIgGAqkCUIyxkiESzVNzYEhBwOCibor1yAZOCgspKrGQcAihE1avafCaRrsjrGUQX/D8VmOXMf1SNzE8cgOm0rEcSxioB8kUUEM5hDGDFLAG6jzNQL7SZpgtTXqlRqLssZKXj1dVYgV8psDATAEQc3phaBnCyYIwRYMokAOuIKfLLDmkcjoNbzBVAr4djl0K6n6ugdv///++/piS16T3z4kCLMk0XWOIaKMwSJ4IoMQUaAhCGGMEYGxJMGCF2Oceo3xmw1BKVTEpBMM5y+KOgIzFwx9/0wwjAWMWJQmKPWaSbMZ4nGapMYxCGOIAUHoWYgxhlMGRA1B1HikXrMQpwqHCIw3gjVYYTQ6wmkDGK2+BG8wVtcYjS01JsPXDsT3s8n8EKvMjIS9w6g4hzwokY9rd/pBQs2ou7Flzt6PDXrwRvsY0eMfRABGyz/Imv+AKYcQAp/NuRQGEwnoy6rAvStB22WswCQrg6ihKnBORCQTKVUURQJHKSECkx7GUAjKpH0IIRD/TJ5P4hAHkShNiKIgVRSYhGQonyZgkGoUglQEGhBiMEKQjiB4JgSunwkBpyAjeG8AgmAXilVhZCgiIxZe3KrXDKQxYDejCLBLeSasIiIW7NeAA9LCnK2VCUoVsEkD3pCHUH0czKo2wxMEVrUEnlxIYAMB76lnuAIuBddCf8Oh/o53HWfcI1sctziXU9ZKArTFhTDMsSoPBewk0HeIIYiBfxwIC8IRBqnnDEBkMEYMtOXPiarj9YRTdEgVBmnAGhaxYC/LiGCTgOBREM8ZtYUkjfark9ftrdbBu1n0lo+AAGzz9vTbS5zCRMUpnOJkSeGAIwOjn0EKAsMQGInUkMCY+C8K3AIIQdvTJDMGkK4u8fUwtrzhYX0wzzH5eZ4thnhtTVava8cajQNcCj8OnP1Uh8RPf0TcJl146hhy/lFKh1/uJVU9/zC2oPrInu7k3/Kg/wvUM39BHbSKdAQiMFZg0hLVDZ2C4X6FNJi+EIHzVIcKoXqO1J6BczUE/4sYuGUZFkOI4CBIUSA33OzqTQ4RCGOMMNzRaj71Ok0YjFGasAyDOHjcLUFZ9bG8Kql1o2d2YIwRKErVrcCiQVH1I9WRI4QgCYFNah0InEGqILBBFZiIEUzERA1/EwzxaQJrFyhKEAu6GHXH9Zs8O5cRVq/BqWNw9ixmQDV5Vh3YixkWNCGs4Vg3wen6oPvOs3/Lu/SGnxW4ZWCYE2aNRmBhwUFkCXE8iwRRgMUWtIFVAamYMMIEqb4dQgyixqlCoE1/RlQdf0IIPEkAQbDIAKvAFgVEBoUySl2UMIxWq+VLEpZrc/Lm04qRkWpm9FaPwrBTz+fmt1tyuCZLgnicSBJBMO5UaAqEBGrHMAbUMf3fFIwYDEvENHlFFgphdApmvRiey8vptCV5nfZTTq+tx/B8W5cs93ARZbCNKKMdIp6QJIoRCZqnPY/efBJ++m5MzeN3Hxe5dCnGBPMLi/7Gg1b9agSYV8tOc78JAj3KeEiMQkR4h576C0t4BSENzK2pO1dwalpwcBpRQRpJfibg/nlBRLwLBEKNLCIt7IDThFdciJddkNeJOMkOaS6khTgdiFZyQLwDpYaiHfGCDRmhEQZEkCp6cKQ6cE6qqDtPVYzwRCBNDFCXXnShVBFcyCA6kRvsts2ygIyg2yA6kBEkNYRrNxB3yYncoS430aGYBIeod9pcWqfVrnFYLDohPsngiknQumJiOUf0U9YW85CxPn3AWB7fZ1Pu39NbHj3SpTx8rEt+8MRgiYkxpMQ9ZeOfPmGT4++TpMSkU9cfBIL5f/knFGPBqMjf2y2Ws3pFlIzwNAK5LIiFpxMaLYMIEZD6dEGVtD+cwkNf8uCYOFjksBAysOj5u4RFIiyoBASrp1RhkBOchR1xjAVCKxKQCyGDQYuNprKSztD9tPlOJfQXHOpOvaB34F7ZZhsuORw7GUm0ccBJFQTjECEJsQqVrBgwwAdnIOpiACOY4mERBws5TGC0EsxqCcObEaf1w7wuL9YbyjJ6Qw3OYGijcTP3YgymYS5WO1rAuvExejLy8O3YficfxH198m58x9P3Y5ufvBVb58SNpx+euvkoJCoq9VfYQgWIHv8QArQz/oaOCPXSK7AZFhnYisBT2tTdHAOT/3MhcOulCkJsakhQqpMAW2GfBfkVlP4GZsApM+A0VOGQAmVkmLxBIE695iEuNcSEcJjIqflhl8nIMmzOYLsuwTNkWRKIpLiIJLtkUXTIouiAXZNdFESr6BSTBZeY5HKJiQ6nGGezu6KtducTi9310OJwPQC5b3EIdy0O8bZdJDedCr7uIuxVEfNXZFbzi8Jqzyuc7gzhdKcQozmKWP4gw2j2Y16zh9dod2q1uu1GvWabUcdvM2nZrW4avMHMcxsMHFlpZNECs0E330PLzfHQMcuDOOMjwPC3fMp8nPscFp07nE5nNCOLME3KCMM0iYCvDIsiBIskVQAzeiYIYcD77Bz6DJw6/lNCEIbx8HoCtkLdqn0I7MXgKDE4eVUQhAqMFZHAsOQ1Rk6rLRZvkz5d/cvtt/4lOQSHnx+2uhfNcRQ5hZEOq2254HJeI4psRQgaC/wQ2PYnBHb9sirwOAkGPAFRUkWCOtIEmg4dqsA9oEiwcU0VJ1Ekh6JI9t/EBqFVkSWLIospcJ6cKrKUJMtSYppAelKqKFKyouZT8yuSFfLaQOygU9Wr1iEQRVZFrVsiYBe0UbVRtRWaLoMof0qAHtAFFQREPYMQphmkfuFOhkQFqc/OMEcwoycYe4AEYZbJy2q4EpxG8xmv0TVEGk0LJ+FaOxHTxi6xrR2IaWWXmRYpdtI0jhFq7r9wv/z+s/eKHjj/IMfuYw+8jh69al536ZJGre6fL++ehcy716R/fovMgnKfS7w/zhl3c6jl6dVx9kdXp9gfXplhf3R5uv3xr9MccG19eHWi/cmN8Y7YO2NdcXfHiHH3RwkJDyPE+AfDSdzj/ij2aW+UEN0LJcT2YJPjvmaTE75iLQlfMskJXzApSV14S2Jn3prcQWtPCdfZ7a01TlsLrcvRXO+0h+mcSY309qSGeltiQ4MjsYHOZW2os1saGFwpDQzOlPpGp6WeKmZXcl13Mbmuh2Kr66VY6/gQR+2a/qjOygo+ddZU8Ku7tqJ/gzVV/Rusr+rfcF1Vv0Y/VPVtsraCZ9O1n/qGrf7Up/n3FT1brf7cIxyk47rPPLqu+dzz6zWVfXuvrBTQf3WVwKE/VA6MWFE5YOyaSoETVlXIMXFVzeCJiz4NnLCyRs5J82rmmji7et4p02rkmz2/ZsG586oXXja1Wck9m7pVTvi7ergWxi43Iiy1JceOs8RHL+edKftIUsxRlBQdpXUknkOW2LNKSswJISX2iJQcc0hIiYkULDE/SclPI6Xk6EjREnNQTIk5JCZHH3xRBIhLlaTog0LS08hUSY75yZUUfQBkvys5Zq8rMXq3KkJS9C5VXIkxu11Jz8SZFLPHlRy715UUvU9IijkgJMf8BOFBMUmtL/awkBx7VLYknMTW5KvYmnKdWGy/Eqv1LElKOoSSLQd5m+2ImBh3jNisF2CX/KstxWa59jRB+1exVp+AlC8Wci7IVzPeEZ/UwxIXP9CZnDRVTLEsEe229ZLNukWyW7dLVusOkO2i1bpFsFg2iRbLeleKZa3TkrLKkZSywpmcvNSenLzIlpQ835aYNNuelDjLFp84w56QON2WkDDNDmJLTJgKMg2up6piTYDr+PjJtviEyZaEhInW+PgJlrjE8SlxCeOs8QljbHHxo1Li40ZYYmOHWWLjhyTHxA9KiYkdkBIb39cSG9fbEhPX2xYT+40qyTGxvVJi4r5Jhjgo3yclNrafNTpugAXK2GIThljjE4dZEhKH2+KTIuzx8aPAjjGOhIRx9oTECY6ExIn2xKTJzqSkqa6k5JlCUspsIdk6T7SkLID2LgRZJKVYF0P7QWxLRJttmWRzrJStttXE5lin2J0bFbt90zNxrsdO5yrkdKwmDts6ZLdvVBzOjcjl3IIF5w5GcO4mLtc+4nLuJy7XfkVw7VUE4UfZJWyTnSCCuF+WpNOyKJ9TROmcJMnnJUm6JInSFYi/I0lynCTKTqdNkK1WC4qPTWbtlgQuVnSwtthYhhCC/6qxQ+v5PwHq0P/P4i87q5bPFNe/Uq51Q2sXnDe0SfFpQ5uXmDi6Zenxo1uUmfBts5ITxsP11JYlJo9pVnTqtMaFpk9oUHD6uAb5Z06vk3fW9Dr5Zk+vmXvhd81DFn/XNHjplMYByyY28F8xt36RlXPqFv1+Tr2iq6bWLrx6Uq0iaxbWKPTDtMoF14/+PO/GsZ/m27Lwwzxbp7bJu2N723w7trXL9+O29vl3bmuXf/eadnn3rumQb9/qdvn2z2iff//09gUiVencvtDBTu0KH77fruDRe+GFjt0LL3B8Y/WQk01yGqMa5jKeUaVxDuPZeiD1cxjPqVI72HS+RrDmgio1/bUXqwdoL9UI1F6uGqT9tXqQ9mo1P92NWv66W1UCdLcrBurvVg0y3P88h+GBKhW9DQ+rw7ka1oLzGr7GJ9UDzDEfg3wQ7BZf0c/Pip9tif+yvnqxoiFVikfX+iT/KjMhIxmbfbDG4eyvc9j7qaFGcPbjQPSi0F/rcvXnRAeE9v5aAUIQ1uHoyzodfViXs+9zsVr6siAcCGOz9mPsqtj6c3DOWVP68zbLAK3LMYCz2wZyTttgreQapBFdg1UxyK7BBsk12OQShhgFcYjBJQyGuCEmRR5sUCBNFgcbFXGwXgCRJTUcqJOFPjri6KNThL4aF9gpksEaSOcdTghdg5HV0g+lpAwN1vFriwWWfYr+wgP6Virg7f3Qv2Seg3kCQ1bmDjBNDAgyDgvwNPQN9DZ9E+hl6hHoY+oO0iPIx/RNkK+5d4CPqS9c9w/0Ng/Mm8s8OE9Ot2F5Q8wj8uQ0j8yd0zwmZ4jbuJBc5vHBOc3fhuR0m/Q7yeU2OQQkZy63KSG53aflyOU2DdJnBuVyn5Ujt3ltI+n+AAAQAElEQVR24Vw55hQIyTEvf87gBQWCzYvzhuRcmjskeHnenCHf58yRc3VQYPBav4CQdf6BIet9A3NuUCUAQvU6ICDkBzVdzRccDG0JCV6eOyTH0rzBQYvy5QhakC8kEPTmmFsgOOi7/DnNM4NCTNMDQkxTcoSYJgUFm76FcFyOEOOY4BDDqBw5TBEhQQZVRgQHG4YHB6miH5YzyDA0Z5BusG8Ow0AfL10/X09tbz8vQ6rAed+QANPAnP6mgf4Bbv38A819AkEC/M29Avz47gF+5m4+bvyXSrLzC1VIkusrxeHqYZRRryA/jz4mXtfPjTD9cwbohwUFGsbkCNRPzBvkOz13DsP84JyBK4vkzbk9f968h8vmyX+hZEix23mrFHviWa1EQoMKpSztKlRw4b/5PkX/gOPvMIH5Oyr9r9cJg12JCPWzDsrlkRgR5BYXAQ5riL8pWpV+cK5Kj0BzbF9I6wryNTgzVTqFuCeo0j6PZ1Jrb+8UVTr6+lpUCfPD1jRpG4BtqtQNwvawEOxonwc7ValVALvC4F1wOYzF9FIRYylNIF1OkwiMFVXAXqLKf73f0tpf1csreUSNUvfGVA09P6lG8dOtq4Qea125yOHWlYoebVql2MkxVYpEjale7Oy3VUPPja9W/LwaqjIZzv8gtcucn/ybTK1V+lyapMWp4cTqJS5MqVnyoioTqxb7JU3GVyl6UZWx1YtcGF+t8HmQC+MrFzs3plLBs+MqFj3TuHLhqIaVCp9uXLXQqcaVC55oXiX/sfDP8h4I/6TAvvDPCh5oWbHokYaVQk83rlw8SrW7VdXix9pXK3KoWc1ipwZUKfYgLBTDS/e0Vv91obpbL+CNU/KbzTGFjcbH+bwM9/N46u++KHk99ffUtPzehgcFYAEYYjA8UiUnlMltND7JYzI9zWsyRat60ksBszk2Iynk5hanShE3t3hVQtxxQi4PnKhKHk/PpHxeOFm1SxX1C32hcM+VhHstM1HT1XyqqGXU8nk8cZIqqk5VvyrBUJ9aryqqXaqtqt2q/Wo7VFHb9KKobQ02GB7mNxgeqBxUUZmoop4HG/DDHAb8IJ8B38+rx/fSJI9efzeXDt8u4KG79XFx/+fyacGA26XzeN5V85UJ0t8rk9fznqq7kMHwSO2HPCb8VLWtiBuOV+1X2xUUhO15YH5R5xN1DoF5QgYhiB5/CwHq0P8W7LTSfzsBddICkVRRHVCaqBObGvebqJPbXymp9vxWt6Takl5UGwvAa4M0Ua/Tp6vnaXGgg07K//ZBSu1/hwlk3DTq0DPmQmMpAUqAEqAEKIF/FQHq0P9V3UWNpQQoAUqAEqAEMibwphx6xtppLCVACVAClAAlQAn8JQSoQ/9LMNNKKAFKgBKgBCiBt0vg3+HQ3y4Dqp0SoAQoAUqAEvjXE6AO/V/fhbQBlAAlQAlQApQAQtShI0THASVACVAClAAl8K8nQB36v74LaQMoAUqAEqAEKAG6Q3/7Y4DWQAlQApQAJUAJ/AUE6A79L4BMq6AEKAFKgBKgBN42AerQ3zbht6ufaqcEKAFKgBKgBFIJUIeeioH+oAQoAUqAEqAE/t0EqEP/d/ff27WeaqcEKAFKgBL41xCgDv1f01XUUEqAEqAEKAFKIHMC1KFnzoamvF0C/0rt6whhL126pLlxI97t3Lk7HlFRjw03btzQqvH/ygZRo58TWLeOsMeOPdBfvRprVvs1KiqKJ4TQOfI5IXryTydAB+tf3EPqhLH72M38P528XXL3kdsld514UEKVHT/fDD3w84185+7c8Xgdk86evRq0B3TsPvmw5N5TD0vtBv17f75TStW7O/Jy/sjIO7rM9MKkxR74+XaurSeulth5+m7pvafulNp57G7pfeo56IiMvBL609Ebxfb8fCU0VeBaDX86ermYKur5j8d+Lb4Lyv/486VSe09dL7X7yLWS+0/cLgETow/oz3ScqZPm3kPnCvwE+SPBbrVO1fZUOXqv2IGo+/kiL8WYMrP9ZfH7T/7qve/YxeL7Tt8ofejErQIxr6hLddQnf33oHXnxTuH3qw8rOfTDbh81Dp/5ce2wPvWbtBsW1qzLwOo120yrNqrqoE8PXXxQPOpKbOCdO3cyZZ2ZvbduJbhHHrtWOBLYH4i6W2b38btl1D74CcbIIWB7+PCVQODIZ1Y+s3gow+w/caPovmM3Su8+cqnkoUO/FDlx4hd/iMeZlVHjIZ05fPaGr2pP5Mn75faC7Dlxr+yB44/KpMb9fCV0/+FLRQ+fADl8o6g6Pp7J5WJ7Tqvj5ELovigYE2eulvjpwu1Ce8/eD4qEBZCqV9X/KgJluAM/X8ml3i8/nbhX9qcz98r+CONyP9w7+w/fK3rg2LUc6qLqFXXiE7AoA5tybzl8tmiFZmNDe4/tVLZZ58GfVW3YvVbD9gOrNmg/u3yuEp1LrwVmx84/yKEu5F6ljsePHxt2AvtDUbeKq/fmDtCz59jFwvuOXy+SJjvgXM2z5/TN0P1nHpTYF/Wg+KFjD4pHwvVhKHv09NVCqkRG3Sl8CPIeOn63iNqfP526UUwN90TBmDlzM/+JEzfcgBPOjn2Qjzl8+IbvLrg/1T7dc+Zx2R/h3lP17T16NgjSs6UnO3WpzHbD/HMAxvOB0w/e3w3yI4ylnTAe1Tb/+PPZXNnRQ/Nkj0CmE232itNcr0JAvVG+GbcsuEajfoOqdJi+pkbXaT/U7DhxTc2O41bXCY9YVDmsX+/xU3YXexWdaXm7DF3dsFa7qXNqhE9ZXbvNlB/qt/9ube32k9bXaTV6YY0mvfuMmL/KNy3vi+Hl2Fh9zVYDWtRvN35ZrfBxG6t1mLyhVqeJG6u2m7iuWoexi6t2GDeiRruRg+qGjx5Yt+3ofg3bj+pTv/mQ3jWb9utVJ2xonwbNRw2q32TEyJoNh8yo3Xz04totxn3foMPE72s2GbawTqvBFQ8evKtBmRwb9j01NQwfO6hW+7Grarf/dn3t8Bkb67Wfta5e+LS1DTqMX1qvZcT48Haja+y58NSYiYqMoyE2AnZXvXrO+bxhk3HzmzWduLJDlylf7zl3PQckvfSjOvJdx3/NPaNuxEet28/o2KLF2Gk3b9+ZmZJs7+bmrasVlMe3YHA+31xmH91HiVZL+MOYuJkNwicsrd9pct/a7edU2Hritv9LK0mXYdPRi0WrtRg1vGLb8Wsrd5y2pcaXszfV+nLOxkpdpq75vMW4OR0GzK0JHF+ZQa73uxSp0qzfrKrh/VfXaD9sXo02/b/sM3ZZmbCw9Vne+8ePP9R2HbK6UsU241dW/2Lu9trdluyo++WSbbW7zttare20FZXaTRpXq/34flVbjOlVrf3ob+q0H9e7TsfxfWp1nDS4RosJY6u3njSraouJy2q1nrqqbstJc1q0m9y/RfspLWdtPlV83aVLmY6HdEien565nWis23ps60btJ65s0GX2tlrtvtvaqOvc9XXCp62q2Wb0rMbtRjVf+uO9bC2E4R5kdsJCpXaXOSXrthzZuG6bb4eEdZrx7YnT53sZ3LjagTm9yoYUyJHLP8SvVIrT2fCpxT6iY+fJUxu2G9f3syaTKmyHxQPKxgH14JlrDhWq1WLEzM9bRCyu3mLY3DotIiZWbzhsaK2GA/rWbTqoV92wgT0bhw3o2ah5xJB6rSbMrtZi1PfV245d9Xnn0d/DOFj6eevRkyuHjelXOWx0jxpNBveq0mJ4/0otBo+o0nzEpErNIubWaTNqfpOWE+bUajhwbJN2I8uAWSzISz8XL0bra3YYV71ei28XN+o0a0ujjt9tb9x+2rqqYWPm1Ws2rsNXg1Zni+VLK4IMS3ffcWvaeeSA+h1Hr2/y5cxdYd3m7gnrNmdHwy4zNlRvPHx2k7AxTSAb/bwhAlne1G+oDqrmNwIYYzJuUCtBZzTo/bxNhQL83Qv5+piL+nmbC3u5G3LkyBGQ+PlnZW/9lv2VgsAc+W77eHmZA7zM+f383Av4ersXDvT3yudu4P307p7XF48eEpOpwhhfwdsr53UPL58Qfx/vXAFeHvkCfDxze7ubgkxGc1zhQiVXt2zUdHmrJk2XtGrSbH6rxk3ntAlrNrd507B5LRo3mNekQb0F9erW+KFKpY/35Mud86xJz1mMWmTWarA2b4HcyRUq5HZlVveEQXUSc+Twt3qZdT4B/t5+/r7mEF9vUwF/P6/CPt5upd3Nxs+TUxK7f919avXMdGQWH4GxEt6pnovXIsRpiNCqbaNTbdp8ci2z/GnxUVdiA+eFTf88vNPEiKs3b8yxpDzpqEhO4aNypfc2b1Tr21N7Zgw8uGnSsEObpg05v3dB/1ZNmkV88ukne3kOmURXUvt7Tx6P7NBlYo9SVQeXgicUhjS9WYU6vf6pl4dnvK+7McjPyyPE192cy9/TLY+7UZtDq9VYBvRsd6VixTxJWel4MW1X5K+5rRZbH7NO4+em0z+sUP7DQ23btp718/Ypu9avD5NfzJ/+unz5EEePHo2ijXqtxsukC/A0aPy9TJqgVHE3kdIlSx1t1aLZylbNw5aGhzVdEt6q5cKObVrNa92i2arGdWvtqVDhwzO5cvha/D103h5uug81GtLGJTn6DBsyb1z3ZrOqr9t92Ut1eunrzOzcm0l2+Xj5XzcZ9f4eboYgH1+vHN7e5nweHqa8RoPe7aOPPng6rleV6MzKp8Wfu5PoEdZjdbnmbb794sDBExOTkuK7KrLLq0Ro4TPN6tVeumz10Amnds0Yd2zblIlROyeP7N215ZjPK32yR2/Ucjwvh8nEOblNhwn9l2y5/EFkZCSXpjej8ODBg+yiJbs8dAatydNs0np7uiXlDAm8VK3yx4cb1a25uVXjuquaN2m4qnlYk9VlPvhwv6+vv+Tv7VnQ38eruL+fb6inr0/RoIBgR4VPKhxq3qDu1qb1625s0rDWplrVKv5U+bP3zgT7eT00Glh3oxEV0utxIb3ZXcnIjoziRF+FaHQ6ZHYz+JhM2mB3D1Ogh6c5r4+PuajGZCjnlOTSGZV7nbgpfevG+QX4yCajwddkMnoa9LyHyajx9/fzyMOziq5O/RoHXkcvLZMxASbjaBr71ggYeUmrM1oYHjMKERGrYRADU4OkiJaPPy53o1vY+09fp24vL8/7jJaREEs4iYgYYQUxLMJOhzO5SpWKu/LnR0JmeosVQwrD8Ukc5+WBkInhiAFpFB1mXLLsxjA3mnzYd+uyifX3LZ3Q6Kel3zY4tnBK49MLpzQ/vXxmuzOLZ4WfWjmr5aFN8zv8sH9V7wkjvmk9pGxonllGTjkpCZa4Tm1rudSFTGZ1q/FanrkuC4lPTXr2B1FOvsEgO1EkCXFaM8fqPPw8fHKXT7A6vw6tOTSfmv9VhDPgBMQ47imMNcrdU7r0srJ7Tj0KGTh+a8ebdx7PcXP3b+nplasoIdrHFSpXnL5jde+J08eFnccYy+n1zIyoc2nad+FDWEVZBS434wAAEABJREFUb9TpdZ5eBd7XGfz6PHyaMumbscveS583s3MTr0nkGTaBJ5jlCYtYWUI6RUF6SXCYWPag0UhuZlY2o/itxy7nb999bDdZUvIyhD9cv1r1kQdXDBg4f0yrGxnlzyjOU8OmmDHzwEBEpFUEpJHtSKNYJV52XW9c94PdS8fU+2nJ+EbH54+ve3xuRNUTs4ZWPrUooubODdOazzm49Ou+3dvVHm4k0nqk8Fbe4Odl8gjJ6+kTUktBaGaPPjPq3r2LtBnV+2Jc7ty5RcwyDxnCsBKRkMzKSMYSjHHC6njNE/8gnzMvlnnxeh08Yh43a1+DA0cuTNLq/fp6eQZ+aNToreXff2/JvMHhE5dPb/dz+ZAQR/pyEeCM9i35arZep13ikEXZOyhfqNnTr0ff0UsWN++5pmz6vH84z52bUxSFU5yWq25avKFBnUqT+o7oGbF3df8FPyzssmPxjHaHl01rc2T5lFaH69cucQJLrhgNp2Ex1iEZaRnEGbVavflUzxafb10+rfne72e03LdmWtvt2xd0XnBgxTfDv2jXoF+5MqEzZWfiCcGeFP1emeJPEIKi8ONlH2+XS8GYdWFWMmIdh2QMwxkGn85s9tabjR9cunX//chIArPSyzRlL91s9IznNWaFEAUTJCKoFzGSnbAwjtbN7nwxe1poruwQYLKTieZ5cwQkzIu8Vp+MFA5hpEFEZpBLUASZaG65eftde92aFNlpVxTWhTBPOI0RyQpCoixLMmLuTx/Q9AnGcC9lrlwGj/7E5RJcmGEQQVAYKQR0CAXz57JGRGA1IvPS6VLaNioZs3/tkNU9u4fPC/T1OajTsonpkjM8lV32BLvNduf9kvmGffTxp9OcTvsTliNQvwi2sGAJw2p0+lCL1dlnP7wTz1BJJpG8jF1IllJyBPhfC8kbnOXTjx8PXMk1ZMziry/9eq29zHJ5FfCtLsH5pGq1qkunD615EhiKmVSD8iMkVKv0+bJkm/166lTIIY3GwJd7khjTdM/PF/wyK5cWrzB6WSTIJRIMfYaQghkkKgoRJNmWL3/I9RZ1ysan5c0qVHeOa3aeL92185gvXXaLvlbtKmuatKw/bOWU8KNZlcsoTYPBc2JGgOkeKQxGMowNBWMFIxTHSVJcRmXSx/Vt9/nhr7u2mWY0m09JssspEwXJBCOTu1uwKCntD545k5sQiEhfKONzBdY3NoJ5gcDqV1YkpMDIwAwWzW7mh7VKc1kuUiLm7vHr9NXElgd+OtRNo2PK8lrGTVKkpLJly+wcMrjByXLlguwZV/sstmP1alslhTspSpJMeC0ymsyFbaKz9+qtv/g/y/HHn742mxJa0PdpzUqfrOn3RficxWObHe5Rq4DrjzmR2ukeTsHliVTICoOQBG7PKdpzBHs+qlkzvzWjMkO/rPxoz9K+i5o3az6fZTT7erRvGg3jk2SU98U4m83IyYrgDgzsSFGcJLWDMZwyCHM6w80bj4psP74z74vlXveasFoHzEPQ+yxiGA1i4J/kkqz+viGHQGe2bIZ89JMNAkw28tAsb5AAlgWOSMRMJD04dBNCigFhxSDznDFGz3MPX7cqwcUzBDYsBOsYWWaQAvOk3SFYNXrTOb3eM1NHpNanTgQY5kgZlhcKBt/NwBkSCc/zVr2bV6ya51WlT8cKkf17tVxaMIfHvZeVlZwuzmm1PWn3TVX75m+bLJQF11qXlGSVYDeGeROSWD3GnMkHZoUmvYaurPkyfb9LJ7ChFEW5QtXy0WEVQzOcHNX8kXfu6EbN3dL0fkx8S0ljziPpfHkXLI6csvhzr66V9wUFZT3pqwwn9asULcnCeUXHIJEHkgatOdEiVBo7edsnah1ZCXHB5pwhvMSwxIVZpPAaJLMwyTLMk+Ytq8Wr+rMqr6ZtPXrV3GXY1gpf9Bj3ldPpsoe1bDNj9fSOCxZHhCWo6a8qAgMvKhjeTWIw9AEGexgkYVaRFCXBIemSsqPvm/Yf3c2Vx3+dorieEMwSCfNIwEbO6O7/3vBRSyvevAmr2pcrYhCLPRkenlOwWrCBIIUhiBDFUap06NOwsMxfH1y6dEkTdflpRdbo1o5zdy+DdKwR6znikl3nWzf7ZF/FQkEvXZhERNS1G7X6MxLLyjbEIUFjYBgd+2H/UXMqZ2Z6aGiocHjrtF+2Luy15+vwD7JcjEkyG0AUTTBCehYrGsQTLWJFYm9S/z0b9DvckJnVgtCsiOZ7u3XpsOqDD7xsmef6fcqPJ6+7JVvjyzhslmuCzXWeVTQydDAstrSI03m6ad28PhMUttLvS73+FeY5vYw0GDF6RBQeEQkhh9WZaOC1Z6F91KGjN3dQh/7mWGZLE2EVLUGKH4ZJEiEC/xQky6Lg5WWK/jRPzphsKckgk8TIPgzH6uCOSU0FX4AEyZVo1OuuJAQiuIVSozP8QQhheI4zahge5klwIpBLUiSCMWM36k2v5dBBBfoyvPKjcuXyJavnWQk8oAhkMYk3y7Ki5uvasckS4lBO8pIoiYoTEQwLDLjzEcO7J6ZY2y7ddilbj7FVXTB5GBFDsFnHZOmAflh4Lv/tmw/LI0bjxWk1GHwPsjlTbBpWF6lTiEXV9TKRJHg5qaAnoj3ZxiNZJILDAU8fLLKsboGyLk1YowHzOg+GY3ikyLCHwYjBLNZpdNGeen2WtiM4th26mmdQ/+/qRz++37BK1c/O92rTadL8MU1uALbXnjAxIiaGYWCscjBUFVgkKrDME0lw3hyOEV3KOqDabH089NpDouC8B7rgOTlCmGUQo2UNdtERajdHg3KU5XH5MmIxy7ljDdYqKhtYnnISlhARn7p5GK9nVXhnVHLIkZ9PVcQMyoc5Xq0YiQ5Z1iLdKSPLPsqqbPo0eEMWh1yOJB0WHcSRZMWyFCeKztdmm163KBMfwiEfgkSE4fUGCyGDiB1e3wjp82V2Pm5IY/UJ3MsHGSi4dIlovp22JJQVRW8fs8faD8qELnE5kqNZBsuYsDAXISQjxeeXq9eKR0Xdcocif/ojS4KvgiROvY9l2Ykwg5EkuCy1mnx2/08rpwp+R4D53RW9eOsEBJFoBUkIRAgmSLh1ZLh5JdnhDC2cKy4sLDRbN3BGRoqCw58oiha8AGYUGVb5ChJtySnwOOB2MZS1Q4cXkKyiKL5aBWlZxCDCMAjubjBQTNa+wqSXkV3ZiZOJlIvjNdFuDn9ZzT++R60rjWvWnM46xUcKYwd7IJrTIMQaNbLO4+OxMzf0XBd5KUDN+zJxEsUdMxqi47ksF0sul6swq/MqTFizAexBkmJHNjklUSD2y1eK+WbLedlsSOYY5S4WXBeR036Z2OzHPDSatc3qfn7qZXYiTvJCWi4Hy2Geg+7C0IdYYZCG4WM1spSSWfnIyEhu7ppDpXv3m9728dMnpZs2qrVzQu9uiyIiamVWJjNVf4hnXcREWC4A6MMkDCODY9VpWalQtayEMSZ/KJBJRLGGOR/rtNxTQmTY3EuIwIQuwNN8hygEGXiezaTY8+gU94esghUPSStrCCFIJ+sQ5ySi5LI8IsiR5RMgQti8Gr1HSUavN4uIRQrSISRqBSIpt6+cKPrShVKaETwmD4jDdoS1Jkax9qQDjENc2Lpto31p6a8bRkYS7trNaF9s0HgojIg4RgArBaTj2Cc6hsm2fdmpH9jhfacPBmGFbRgcGHJ9YLtaqzu2LnfQbok/JwuCEykswowOMTyrv3LzVpG1kdeLZkdvVnnWrSOsJSnFDzHg0Bm172E0YRlJSEj4ok8dS1ZladqrE2BevQgt8WcIHIq8zsEyWI9kCbEYIwy7NxYTl9Gk/1MTsMPlCIIbVo8QRgroJZgFp0SU2rWqqI9bs5x8ve8iVlCEHJgjGtWZMTDhghoii2IKGKh+2Qa9rWPdunWsALsFjdYUY8uNlLR65o6r/6OIyBKnNSmawSKCCQcx0CYiK1qrNeXD4eNWt9q+PcqQlj+jUJ0sz567Y+R5jVOjMSZmlCctjhARM7ILa6DhjEQQB5ObWetm4jhjUd/LscA1LWfmYf78SPqybePjXdo0nNWuRb2JXdo2ntanX9sfenSu+NJXKUTmPYnCBmDEwqFFCBEky3aFKHIcOPoUiPjDZ3vkNZ++Y3bUGTR4esekxASlTatWKxZN6rinQAHs+kPmV4yAscQeO3vPgBl1bQhjSiYIEwZpGa1Vp+EytAdlchRFvjqt0aSRZBkzLJ+aCxafgiQ7k12y/LzPUxMy+OFl03FOwRmICDw0gnQY3ghjsFCRbCyLXvI4W1BkwSZhWVCQIsDDD0DDM9AUNqRAuV+yvQPt1K7Or21bN1nQsmW9ye1aN5rapW/HNVP61n3p43owN8vPrdjjbsdPnPHhAAzBaq8jpCiyxDPsEw3mX4lzlhVB4pMnT/SjJy+uote43Pv1ar+5R49aLg8vXQrH8mcFwWZh1fphIQmjHxb4TM6TUeeLqQ4Zir72Z82O2R5Wm8OdwSyDFIJYhkMEM0irM0ZDT5DXVkwLZkiAOvQMsbydSJiCmA1rdxpZhugZQpDqzHnoAYNWY3EzGKL/TK0Wa4qPIAgaBeZH0IwUDLsRgqxtwytaMcYkK90uUxzvkFLyEo6wCqydJcmFGJaTYQuUzDOCuiDIqvifSlu856G7U5TNBq0+Gp4kyOmVdepSax5x2vYhxSZAg1IdCsvC9KPV5oqNT2k1ZeXh8unzv3j+yHHTcOzYGQ+G5a0M5hNfTE9/rdfqn8L2LxmeUig6okG8xCAjZ/LELNNq176L9WevORWybl3W/38aOMvj+tW/OLZH5TWqjOldY3f3Fp88Tl9PpucK44MUNggRniEKhmwEycQplSxXICbIGP+HncyGPRcLR4xf1vJJdHwTs5vvnS7tmi2aOarhRbBBgsJ/+rP3YrRuy4/7vWFxp87AwJ4DYRCPuAQ94TN/2oH+eKzacLZYitUVrA4qBTNIVmCUOZwOs1Z/EPn6Cn8s8fsY2ajhRZczJ0awg4Ty4PIQLDwVSXQmiwhnaQsrkxhGcjxmFJcIZBEDu0PCyVqZFattP3K55uRVP+daujRSB/emCh1ldgzsWvX+xL7V903sU23b+N5VjnzbtepLXyVlpit9vCjr3aBiT8RCwzCGNYuCZEl2wSB4xHPCH/o9fdlXOYf2sbN3/FJKEG2V/HzMW75uU+6sWl7QyUmYuE65BEsix2CEwekSBp5isHqv67/eKnbl7k5fNd/rih1rAjSc1gNjmPVgUYgQtBEhpNUYb8H9rsAp/bxBAuBO3qA2qipLAjdv3uQ5rAvgeJ0Jwf2LwM1ighWW4a08o/lTjtMlyRrEyAxm4B6B14syhLDVe+QBz2uzNAoSBTsPOyBHLowVMItBGHZRMiGyu6cpudZ7H2Vr4oIJg1m06JjXzMW7S0ZduQuvFEBxNj48a8yNNVodo9Woux0lfZERX1SL/bJzmyX2pJRfdNAenLp74JGCdSyrNeaNTu17DOgAABAASURBVEjpsvbHXwumL5P+nBVlk8zovQnm7RqXI8vJUYM1111O2yWnkGLBrETUh4Iy5pCMcLnvv981eOK0DcP7TVo2YNTio1/MWfdL7e/Wni84Z9URz/T1/Zlz0Sl6IUX2wwwLw0LFoAp2Va1W3FKxYsXnTjoqKoqfPv/EJxNm7uz34HH81waPgHxmv9xWs5eXFupnQN7Ih9hELUHYi2FYFjMw0YNmGBIyg0gsxzGv9L0KO9LUYjhDToRgeQALTg0DW+uUhFst2tY8AZO6+DKDbZJL4xQdeQiG2jFCBINTAL/n6e2Vkhs1SMmqPIeUJ4okX5GcjngMJwjKKkhhWK2mzM5dR74ZN3njsH6zdw3sPmVf1ymrTteatvZMkZnfn8j2b13Lqu7spAmiZBJlyQ0RhDAD7YMT2KG7EHE9hUGY5ZhFr3BMW7zHfdbURY293cwPu3ZpvyetaK0CBVxN61W9wRByXUZqVxDEqH3EaYyMRlfGojAl0/K+TsgonD9mORNDWGgcq3YekmUiajntddAHrYaf9PPGCMBt+sZ0UUUvIRCPvLQyIiG81uimQF6MMFIUQiSXIFy/dpv9bvlJ7zRZtO6YV2Yyf12UuzrpzJ8fZVB3F4MXnvCPS0zyYliFRVhABN5VCUR08RrtFUYvqHcp1Jb5J94JJUWXL2bAfWGMCGaRyyXKRJEdT4VH/OLFR81qnap8D5Pd7HWRJlXmr470mbH0UIGJC/d/WqPl6CZDJ03pOGLMrNbHz17P9n95IQxbEGFe07xlvWSM8e9ucPW6Zo/KR3QKP0dx2pMY2NnJCswL2IARY3C3ONHnYyauavz9zhtuKIODZ7CbwvA+RYoWFtXHixlkeR41Y1z96IBA3zWC5DgvEaeA4FmFwmoQ4j0MnM5cFPHaTrKGGzFn8bZxY6etGjFu2oq+Iyet/OqbqVvDhy/YWXPC4p9Kzl53yYRe41Afax48fsPMsbyBAYcOwwJB2xFm2BSGYexpKjftuenXcdDmOuO/WzEwNtkZpvUIKiBrTB84Eeo1ZcHqtoOm/ZiLEMKk5f8zoYjsekmUvRGMBgTLGkJEJEsuGZ5fP2VkKdtPk77bEPXB/fvRNRlGD+OLYxjoYclhi+MVsrZzh0oP8At9npHNthSWE2TZnyAWKQiDQC6MXblz54yPiMAKXGX66dOlQnzpYoV3whv3Q0iSrAqREWIw4g3uHnp333Ias0dHXm8atm5z5NgJs1YPGz1hSa+IcQs6dxm5odnQ2btrjF0QWWrC/KhsP5rP1JBMEhzE6SGJkhcCLgAXQfOQLAuiItgfx3rYslysoGweUVGEHzdxaTUeEf8ePTuv6hpW7neL9BZhZZ/yGB2SFBfhOBYcOkEM4jSSwuU/dvJc8Z07b6iLRfQ6h0sS/BHm9RiGJQatioKQKEoODcfeROhZV0JIP2+IAPOG9FA12SHglPSizBRgtVqdyGHkYmCvwLPIKbq8Tpw4+dmoCbPCxkyY2WzM+Fkthg6f03J4xIJWERELWw8ZvqDV0OHzWg6CuKFDZ7eIGDKtxYTRc5qMnPhdtcFjvq8wd9LSxskJ1rIa1gA3DgeP7VjkEhWHgdNf1yb7ii8zbcuGUyzDcGYENx1ReKRIWoSJFsdFxwaNGTmv5bBvF4SPHzGn/bjh33UcNGZu53GjlnUdPWrxF8PHLeo6ZtqiryfMXDEg6tLloUTLdcMcV5RhYHp4WaW/pTtsjnwy4oXmbavBK7XfItMFFTGWevZrv1UShE2Sy5bAgMNTZAkxnAbMZT1iUmxNtv54rn66Is9PnTBZioJo/vijkpn+d7XnmeFkUOewI/mCA6bJ1uSNtsSHv9hTHsYgkiwJyIVklkWI1bMMo/WUWamUzEjNBJb0XLhs06hZCzaNnr5o2+hpc9cN/nbh2Y7zN1wsDOqy/bForxlOnj4LixIJK4qKQUYE5joNZ4zTcuYkVdGG/bdLTJq9seP96Lj+RM9VkDjFKANmB5GxVbAXcBEmfOHyXb0Gjz/wZp4aiJxBlMUAhBhEMEIEnLqsiKIiO6OR4FKfpqhmZSrr9t1yH7ngWPW5i3b2TYp9XIhnRFZLnOCnYh+INusP3buHbyrh7+/IVMFvCYQQ/MPm4x46TmvC4O1UIQSe3RLZpuV0L/2Wurpg6DOm/sWPypWaqzhdP7gs8ReJLSGOFawwyhXEg04GDpZHXiClWS1uirRc73Wb9o+cs2D7qFlLdo+atmR9xMAZkZ3GLTlcVLXnN9PeSOB0SR6SLHoRhSBCwNthIC2L9vc/LJXUtVy5l967LzOCAL89J34qLstCw5xBgT/2afPphRfLVCmb19IirNolh+J4omAX9LiIMEaI0eo8bt95WPLUr/dyvVgmu9eCrOQkhNFiWIgzBF7bIIwkQbISrH2EMTQ2u4povmwRoA49W5jeTCbCKXpRUvIRluFFDiGJZ5CIYUFOlGgOydewwjzmEPuE47jHWhAeM49ZjB+VKF7sYYECeR/C/P3wvfdLP2ob3uxJ7pDg6AoVPkwICgmwMpzWTadzC2QRp8GEBWNBm4IkDa+PfpgfZmKIyeyj3vCbN+8wajU6E1IIIhILt5wB8aybXcca7nAEZmLCSRiEJbyMGUbkEIcZpMGIaDBm9CyrMWkYrbsR8yajRu+RgjXZ/4KfNSUhhGV1Cb4SPE/PxMhBbd9P/KprgznwHvU8VvMQ+EEIUjCv0Zi8Qq/dvN92/rqLxSH2dx/RKZhFQWB5Xv9SB6QWDAsLFfq0GbKjZ8+wcUHexhGs6NjssiXfEYic4FKgnxBPCNYjxHM8YzC6aTw8fQ0+fjm1bj5lWZ2xjk2Qes1dsW3YuKmLu3eftLW2+vRE1fsyYTjJyLAI3jOqrlNBGMtQmSiziHt45MhdW/9vfyz37bcrO9y987AVbzKXYMzuRpFVsIBFWGoQJHFaVu8VkJfR8s0XrlpV7cYNokV/8nAhp0kUXPDqBCMwCMFDGxgesiQKYooVmZ8/NUDpjnXrCBsx84Tb4O8OlBk7dWWbeUu2DUq2CNW1PGNmZIci2i1PsOBY3a1rw3mju1e6izFW0hXP8PQMQtymzbtzGAxmNzAAMQiGHULg/5QUrYZ/mGGhFyLVx8rh1bof69ur6eSSBXJH6BS0RnE4r8kOZxKC7TEBR6qSR7xWqzG6e+rd/IKMHv4FDR7+7yG9uTbSm7ovW7Nr+MSJK77qMmxzbfQGj717o0zQGhOGOwpD62DFiDBRksp/VsSG3sDRb/Jew6z5K9t5e/s/GvRF+M6MVKr9UK9qsQdYw56RsJMwjIIwJghrDQaR1YQm2m2vtEBNX4dTdITATaPFBEMLWWghRrKiODt8WTkxfT56/mYIMG9GDdWSHQIOwaEViZyDITzSSBjxIlEUuyMhX+4cx8f37r3l6dXl2x5cWb7lwS9LN99V5dLiTfcuL9m4b32/zUe2R2x5/Ov3W7Z/33fLt0PrbT22f9yPqxd/cbhu5JhjBYsVuM+xWJAlB2JgcmIwQUhhBJYhiRXg7CW2sQrS5WQ53g1mEsRgARHZKhHZcbtovvwbRoz7asOoid3Xjhn29aoJo3utnDG2x4ppo7osHT+q65Lp47ovGTu86+xRA9uPq/Dh+8M4kczWYOagUVGy/OZxenvs1hR3Lae/7zRIUvr49OfqhNPoy09+adO61mxb/MMrPKvI6qM7gmByZ3g+NjGp9PwVe7p8v/NCcPpyFrtDQ2QhhWHQS3dyaeXCwrA8sN0nl5tVnLr12xFdZ434ptXgAJOpn9blGotsKatdiU+PJkc/uiokJD6GPhRZASNWRAjLPFDX6xFnCEkRUNjy1TuHf7tyfyf11Qh6yQGPsk2yQjyhORipP9SpDwQrksfBfXtrLvhuYas7N6+YkOQ8xgquDbIl6aiUkvQYw3sRLVIQLysIybAU1Hq4SxpNt9lbdpchJHVlh173sNkZnaBInpIiI6zw0D494hkj+EL2k+27d/foNHRNt84Dl37RZdCSL7tFrO/Va+yuQZPWTBkzb+2GCYtWbRvzMD66J+blDxTFydqtCVdctpQNLZpXHzliRJdFI76upv7/eDk7tvk/fMhhrMmp0WrNMA4QSf0H/Y+UBL2GyfILcen1q/3av9XnN3o06rtt7MBw6NfmQ4oWyNWPV5SxrMu+TEpOiXTEJfzqSIy9LztTkhlZVgjcSwqB52jYxXI6UzCnd2+6btOBiK9H7u05acUeY3r9r3OuLviuXbvlw3JaI2Z4aBmG1TchHMc/0Wk0ia+jM30ZQgj+funyWrJoDR4+/MslYS88ak+f12DSRWtc0gHJ5ohnEIckAkMKBDF80Mkz197b8/PNl/62Q5TB8ehptBdBmEMYIww8WQhhfkrpWPUTuGsyKECj/hQB6tD/FL5XK7xm9SUOpl43jFjEKfBTQgoryon58+V52LVrORFjTF4U9JIjAmPF7MF7S4pdy2AFNCtw48C0R4jcuV0dC+hTslJx8yZiJQXlZjneiDHkxHCfKQ5ZEhzRufIF/qq+b2vfsHRSq1YlEsPCiiU0hPNGjT6IDwdpUb/M486Nyv76ZdP3Dq6b1XHtuIiOc74d2HF7+dK+2doRQ21IcNo1Wo67Y3S5JPU6MwnFWKhfu+RenijLnbaUJAQOT1EIUjADKyOt19P4+Orrd5yrlFYeJjNm47ZIeC4vprB6TXRafHbDiAisdGny/uVvWn+84cre4Usm9OwwZcyAFqOHD2o2xBNr+utkzRxO0O8lTu4BzPnJjAKvOrABOUQGXhf7+Bg8fcskWx1dV2/YW+NldRIXchcFpw9BCkzqCKntwgizRHLlF12O0EqVKv46cviX88eNbj915ODWY4d902xwnuDg1ayEHrCiKPGYQQzSIsSaNLzJUG7JyvWtB3673vSyejNLV9mt23bcyHDYjDBCWG2bxCGW1Wsxy5f+5fKN+itX/1j6h02RAas2HCi4eM22ekvWb/36bmx0L6TTdkFabQ1eb8xvcHNHJjfPpW07NBg8dFCbUV82qL6iW1jZmxjjbDlzBIfNpoN1IglGLKeBcoiAUyCIKBBaFcy9stNTHXv7JqVvfNHqw617V3dbPGp4namjBzYZM6xf+NDe3cL7BXn4TuEVzRYi4isOWGxLRFAQ7FgVxoD1Jn8/d68cZTduOtBv4rcbm4B5r/L5Q16bu8HIMlpfjuN0CEATgpCkyDLDsI90svLKbUMvHAOm/1jYZk+q4+vlvbxtnZKXXkj+3eUnxXMmt2hcOUpxyTcVGQyBMYUYBml0Rs+HD2PeP33pQcHfFcjGRd0u8w1Wm8uMGZaFPoMSBMELHKRjdQ9g+a5ABP28YQLUob9hoJmpI4QwPx0+687wnFGG+UxhRERYAeZuKVGj5V/rD7Kk1SU4lBxY0ZpYxgBTHYeQjAmRFWutT4uCd07LlXFoNsPaguDiBq2bGcsaKMuCR2GIt6+vVMC7VbbePauaYbJV2jbMfpwyAAAQAElEQVT6OKZFi08eFyiQ8e+sVvOll9mzI03JyUkMS/DdhITALB26Wq5CMV/74P5ttihOx25OdroYLMDjOwdCHMKE5YOvXrvfYtoPpz9W8y47eFBz8fI1A0GyUydxf/rxZevWBVI6hpW81qvVx4cfX168fdr4LovHDW0eMWZE6x6sJA5xWmKPYSHexrAOhHmCFKw2C+d+eD+hjtpO1abMxIVEL5GQQIKAv8JCY2BxIEmi05H8qHbdytv79m+3oVv7CqfbNyl/tUujD673aPHJkdFDWs4L8nXbKNmtT4jklGG9gxCsDFjWoOG0xoZL1h1uFEUIn1mdWcWrfzr17OnLgVqtyZ1Vd46MBG2SkCgkp0j2pCNVPy0/b9qEb+bOndp3xdxv+y+uXuGz+TySo5DigvGGWUS0MBzMSHYxDKPwzsoflD31NSyOCrzG/483GjU8PDPKB53MwkpHdXtIHdyi7LJpCJuUVTtelgZGkta1Pkxp17DcrZ4t3j82ouvHP04Y1WXlt8NaTxrdv0lfX5O2h5xoXSsnWx8j0a4Q7EKwAkesmQlItiVX6zI/6rX4ptnFKHp3zPGBLKfVEfChCNApChY5lonlGI01Ld/rhisWrf/K3z9XQsTk8L0v04FhY1CxfN47GlZ7VEasqJoDAxEhluEExBXdv/9CqaioV2uvgJA7r9F6YJZhCIZ7giFIFJ1OA8tdCgxE2V7Uvcx2mv5/Asz/T+nZ2yRw8+ZNmPMUf06jMSrqWAanTjA82GIVm0mvy/Yj6oxsvH3nqQfDaDUMUjcxHEzsjMJxXJzJrJMyyp8+zuFI4u1OZyjLaHREYeH+5RGDWeTn66tEROC3uore9fPpQNHl0rCs8KhYMfRSW9VJ5/O279/p2qn1UklwPWZhgsCMgtTJguE0eovN9f6CRTtbLNlwMa/R5WdADIEJE8EmT0hJ3+aXnUdEEOZlecJqhz5tUbtwVKe6RbfMGdls6ZDBbYaKonCBZUUkqL+ulmEwYVje6ZC8D0b9os9Kn82FjZKkuGOsgY2oDskwLESny4Zlaff77+X5sXwx94QXy9cql/NW/2+az/f28TmFiOKC+ZiIog0hhcVms1egzW5pvmDkjnywkMQvln3ZdYKs6DCr8Wd5nYEQjBQigjiRINhiSpYuvLNHj/obvgore6ZVnRK32zYqeWnA0BrbPv7ovcWyJF9BBCkcY0QY65HgEDT2lOTas+ZvaaT+6dKX1ZtRuiiyvCyJuRHCDChFiEAFsqwEBvvbC8zM/oITZfOo82muxJb1Sl3u0KTUnquRI1dPHN51tJ41HFBcdlEhAhIkJ1J4jHktz5/ZseNPOXRZ/XY7ZoIwy3MI7jnMMEiRFREYJug8WXs2TX6W7YWfuUJb1yZErBIQXNB2+uiTIgPHb849ZMzGXIMiNhUcMGp1scFj14YOHL8uP0jZ/mM2lOs3dnOpNWuOhfAYqc6cUW98GDsIMSxmNXq/G3cefrD3bFKeF6rJ+pJgL55n3aFdLGEQkhUJybLk0HDMHSioVgEB/bxJAoD5TaqjujIjIMseGhjOORgtpydwxyKkwAKYELhzrBxveG2H3qH/VrPNajUhRl1WOxEsEmDSsaXA1HAVczIskjOz6Fm8k2fhkbsQqMgSmAQ2MYggRnZxPPqDE3lW4s39hJkrWEYi6tW9ZTwGj5QdzeUwFuvVyneaQ/JUZ1L0TcyA3bBAwmA2YTXuCRZSb9aKgy3nr9vjoRDGvWSJIo6I9hWdWeleuPUX/zb9N7Zp3O37iU2++n7GvO3dh9RpN6MATGjZuj/q1i1nrxdW9jjHu5+URFbikQERkUU8a1AYjrfbXZxqJMroUH916+aNRz1YXmtSCLyDkUXEcSwiGMfVrl/tQpdGZaMzKqfGNa9c4FZIjqCVNmvSJUWyiDrY6GHZiGTBjPQGv/c3bNo9EF6pwLZfzZ19YXjWqHB8TobleZlgGKcMwhIrabD+SrkSxY5VLxkAK4f/6ysfEuIY3rHJoeIFCu2UnfYYLEHPChL4Xw4pDMp7LurSF+Mn7fr0zp07uv+Xyt5ZkuBgMcMEIERAnwJCEGKIFBgUYInAWMlKy549F4xhPVdUb9Bj+ai63RZOq9nxu5Gtei59T/3yXlbl0tJgTJKuTUpcNet1PxEsJGK4N4AJUhQWIQVJZgv8TMv8GqGsEG8BnswgxKqaYa2C4ZG7IstESUKxVxyvoTK1yOCxm0KtloTmomRNvv7r8VwL5i5tPm/+koazFyytP2/x4qaLln3fdt6CZeHz5y1ot2jB4pYLFi9st3Dhwvbbd2xqnJD0KI9AYP5gZcTCrhpoIwKLFydmP9i689QHEdlY7KYaAT9cohLCYGyE/kJE3cAgEbnsSfGYKOpfx1MgC/28YQLMG9ZH1WVCIJm4NC7JkQvuEhYmKAR3L6zGZUUh2GpkxNd+X6ZlWF9Ow8HuTmYIgiUD3DSyLLg4Dt3hHKKYiTnPo21PrZjnWANCGGxS7zECp0TQGfXZ/sIRes3DYbMUxDKRP/y44EsXHumr+CC/l6V/97BthLAHJGgseBwEK3+EWZ7FvCH4aVx8/UtnrzVDjOJdMLTASxcm+w6e/3zbzn0dTpy60PHk6V86JqdYmxyLuuS7fr0KJX3NmZ/HP0GM3WEJwYzOBRsRMAmcmUSIp4eXY2Tv9zN9fPrIoTfcvfvUh+M08NpUQUT9EhpUwzFcTLHQwFj8Eqc1bECDnwKDAve6nPYEuJnB/WqBBY+MRl8PmUVVekyYU40QiEbZPxQimQVFyIsZWFmAF4MQ2sMQluGfmjhdhn9Qo1w5r+RSoQV2EYFEKZLkZAEdz8FaguU1Wp2uSOTBo+27jfnRP/tWwHAEu4dO2OTNazTPvg9AFNCKCJgl6A1uL+3XyF/uFd2+60D4wVMXupw8e7nTz8fPN9+z92i+xMQzgCr7llgsCV4ES9AVUAYz4NAx7DaJNTJyhAtiXvsjyJKbpEieCNqJ1NsONEEgNGpS0xEWlvlfkINsmX4ipm32WLZiVVs3k8e1STOG9x07uffsSXMGrZ80d8iBaTMHR86cPXTXtFnDNk2ZG7FxxpwR22d9F7Fu6qwRq2fMHbJ2zneDN3z5dfj3LEK7JdHlAMeLFGAuYYyRhg+6ez/6U6/cx/JkWvkLCYLdEkII0SuKjAmsvTDHIJfLbuck6S5kVUDo5w0TYN6wPqouEwLWRJ3O4bLnJqkrVQbuYU2qMJiVXVo5yx1kJipToy2SIyeDFC/EwEzD8Ui9S2S4C2HVEJdsdEmpmTL5ATcbM3jypnycgdETDTh0hoAzcMGTQCFOp+NvZ1IsW9Hqr0l92U7I6bSHKoQokqKoZmdLr5oJZlbSs23ZJ4O+brnWaXVcVkTY0zAcIizsbBFMHgxbFBtMXRCjyWfAfJYLk5k7d2qPn75QgtXoimJO58FoDUa9u7sXwxt8TKVvcmp92ZEx438IZFltCcll12pYJ+EYi91pS7js7aWLLFeuXKYLK8KY/TitoQCnh9WIBjCkPnEQwYEqNlbhXvrY9ZPCvpZxw5ouNum0kQ6nxSKxEpF5eD0J3enp5udz7NQvw1r02VgqO21IywOPFMyKJOYhJC0GTogsYlmyGiVBTIt9MWzWOvRS2ZIFtxHReo9gh2LFAnLqeCQZTLBkNFWKOnMtbN+tBPcXy2V2fRkh/tTZS6E6vc4DweNoQtR2wYBRFJtGo8nyqRYhBK9ftz0YYaWoTm/w4XRmk97o5afRm3yCglC2H5V3H7fT1yaiihLn5i6xRhhjHLLbUqLdTaYD6jjMzPaXxavvo9ds+MmbYVk3hMGFqksVBCGjcRXK558p45fpXbFicweXy1UoYvrgFR1rljvaudaHJ7rU/Ph055ofXmzfoPwvbep9dLZt7Y9Otof4trU/PdkCzjvUKX8svObHx8Prlj/1calCP8uWlCOixRrLqs/JYRyJ8KoB8Rod5twqbdwWVV5l+zI71HSb3ZETEVlLYKOBYN7DHIZNjOjq27tNCsYYBpWai8qbJMC8SWVUV+YEJs1cyYuCIwBWq88yYQ4cOoMwvGrVO8TXHtxxsY8LyrLTH/SCD1cnBBBEYPqDSFnOUi9MmNzZi+dKYR6ZESYIs3DDKSLcr4qFZ5hs/R/fZ435/c/FK48WGj9nTj3FdAbeff4+Le1q0qQ9xscPHgUqCiGwACFp8dkNMcZilXIB5428caUgkSQFwbKGEIRZFiGWM8iMxp/hdIJJo8/0kbVaV/eaNcWC+QsmcBqDyGoMjIxYhDHSQ7/kAgY6Nc/LJJIQLvLAkWY6rSYnx3Icy8jE6UxJdjMbjvUd3GJbVuVdshwkK2IoPE3gEVaQ2gcIlmUMp86A2VvoNPyoyN1g/xyrJQndl5AkExYhQXQhWClpTe4exTfv2dkpKxvSp60jhJ04a7M3TMLecvrqFZkgAp7URwNeNX2J/5+rj94L5s+1RRSdxxVFdEowppyw9VRHt8Zgdrc5LXVGD1maFwYYWPj/cpmexcZqREkqA32hAa4IYfgwGMyQFZZjs1wEY3AYZcp+lMDx+iSCGVZWEOJ5rY7FfC6rwnigbB6wo6+v0WhLIUYHyy0WO0VBlGR0dt70gXuyqSLDbGeuI/dHD6KDeK1Gh/BvOKB9DGZkhoW+z7BU1pFNO0yvl5QUV8nb3Xdf2yrFXuv+bVAhtyWsab27sstpg+05whh4q8yBIcdpc96+c6/C3NVHX7pLj5i62+vh/Xu5ECJaBOVlWIzBmICNPm+vWCXw92Mo62alpkZGXfOpHxZRZfL87T6pEfRHhgSYDGNp5BslcOPGDe3la3dL6ExGf5ZwCCsEsfC+lCOSJLmsTgtMT69T4cyZO7W/XLiVU2JYd8LCDIZgYY9FxBBRYGWJPHiJUucTxKXYLeUNWr1BkVww9QqIKIIkWOLui1bntZcU/0Py0qWRujqtxjecumBVRFxC4oeiCI39Q65nEcd+uVECYW0gw+rNPMu81jgsVSp3yrcDWmzjZfsK2REfwyD1CagEbRARzBgEczqLRstk+U1ojLEyoEeNtXmCTUtTrPevSCTeqdVyblC+9YiBG2GXSmCafWZzRj/BOeExnb8fhDUoXKtxaLCS4nTFPznnxfNz5k5pN73RB8GZ7iTVsovm7g5EBOflsJFFRIOQwiJGXVQQomAmvUfNqPb/x00a0/ZQoEm7lE+yPtaILoWwCnIxBMkMpzGb3MIqtp7Z7/+5Mz9zO/7Q/c7dB6W0Oo2n+gU/BtsQltXXuYRFGDFeFgdBWRyzBteK/eS9UutlR3KUm5zs8pCgPJKQpGWR0SugzK/Xngw9eDk2W3/wwxav07oY9Ali9FgiDCLqfSOLCLy8QlJsWdqhmji6Z92zDap+staZEH+BhXcSPJI0ouD4fPqcLYXU9JdJhyFbW1ls4pd6ncbPrFhc9keXL+LkuJnLxn7Vr2HFPFmOq5fpljmcixCmNM+wOgKMm4KB9gAAEABJREFUFSYRsdiKsDMFK6KY5ZjLSHe7fj/UOnz8TGeF6N2mTO25GWOsZJTvZXFQTi5W0C1BdCQ9lRWXC+YRpBNlpEUEYU7iBCyXX7J8T8WtR6+as9J15nbSB4rOXBTWKloOCUgn25HWmejQ87qbDgmWRFkVTpd24elTY5NOC+v36j930omzv9Z/dDVaSZdMT18gwLxwTS/fAoFYwc9od1k+0up1Bth8wYSNEJFlhOFCxzGMh5HhXqfa84/tOZyCFMhp9CzciEhRJFBDkJbnMEToNCyb5cQwou8ELcuiYKTILIPVrAQxLEM0LOPUM+h3v+8ZFGf6Uf9ISdVWM6uNmr1m6Imo890SEpPLsgxvNWhxpt8uT7E7qiCW92dYzWu1XTUGY6yENwi9N7hnq/WKpFzHABWnTjzgezC0iEEOu0N8aTuqlcn5uEBwyBzFJa9yOYWrMHXJHI9Dr16+3b5y80nqjjLD+6TpF8tzlGs4YeilC+e+cDeb8wiC0yYK0nVfr6DvFk3tN68m7JxVOzOTbduumR4/ifNXFEUnCCImCoI+JAjagmTBpVEkWZNZ2Rfj1UfvsyZ8sSLAP2S96HTFshqtwvAaxHAahtdqvS9dudSieuvpjV8s9+K1gzg9nYK1FHDkZUVEGMuws2URy3KEY3k+AcHc/mKhF65Hda97qHDhgttsSYn3FadNUkCPS3QihteYtLy20jc9p7W9dCnm2XvxF8qmXRLwdt9ELMnLsjiYIFjrwF0D/Y04+MEzWKO4HPq0vJmFheF1RMnCIRt4gcwVHdJ5SZRSQE2O23fv1/u4xtBCEVBHRmV7Tdvs8VHjsR327trTjeOYogzDooSY2BsGzjhv7Xf9pzSvU/DXjMq9Slx8jBSoyFJ+YMpiliCEJYSRhDhFZh12hUGvcHQY+EOV3bt2dRBkqYBGa7ySp1LJ2Fco/oesOq1iU2QRHLokMGAVC4JkCSlEwpjDQXcfPKh882p0/j8UTBdhddrKIYYNZDmegT5DGBZisIkRDDptglPyVdJlTXdKcEREBKNK0y/m5qjcdHST5q0mjd1/9HDPJ7Epn+TMl1f70UcdX3o/p1Oonv6nhPlPtfZvauyAiNmsYEnkrDH3f7HH3T5ljbl11hp/+6Il7t6vkjUxUUyWspzcMjP70a2bRVwp8ZLtyYOTlsd3D1kf3z7piLl72pHw5Kot6aHOlMKzmZUlhLDHj5+tzEjSo7iH906kxMVcsCfEX7ElJd50Ou1eew7uHpnv454TC3/Wc0bRCj3nFq/cZ3GJqgOXFavcZ2nhKn2WFP70qwUFP+48q0D5ztNHjV8w8tSJY+3ikpM/FmTR3WG3PVUk8aYJ8xk69CFj1oRcuXA8WLDHxzuscfa+vWbwmdn5sniMsdTg43JnenWqO1KIebhJib4TJT29fVGIeXy+bNH817/tX8/6Mh1q+vfT2zw5sCxiwcdFi3aXY+OXKwkJl2SX/f0zUVdHf1B/Ut9a7ZfUqd1mUWjtzkvKq+flG06acPDwkel3L1+ub4t/FJsU++gAp+CB/h7ubZYt7LOpYrmgOFVvVnLPkhSSEP8wn9Py+Kot5vYlZ+ztq2Lc3WuOuFtXXMlPrdbkFG1W5V9MqwJPA+ZPbTXJ04T7JD26tDn+7rljtsfXr7GW+PuyNVF38ucjLWo0m9j0xXJp11FRjw3Dh8wrjp12Xyk5/oGUEv/QmRT3IDn27s3EmF/PW5Puq21yS8ufWViyZIBt0vAOy/LnyzsxJT52qyvmQZQUc+eqnHT/qiPl9sMbv/7yaevOY8KrtZlkzEzH3bt3Ned+/rkCscVHO+Ju3HTEX79pjb35a9zTm78kxty/Fff0Ec6sbPr4vl0rxu1eMWZtu4ZVugnJ8d9aE6JPOly2POev3Ry0qerwHvXaLaxav9384g07zCtZt9PcSh+HjRu8cvGmGRfPn2vndFiNluQnx7AgjHM3mdv8sLLvmhqf5X6SXv/rnG+OPOexcuUaT8kZf88Sc/OCGHf/siv24UVb9IOzTkv8L6sXrcv27v/Lgas8N6xd9YnVGuPmsMTd0vLcpmIIVgavY9hvZbREG88o5FRK9MMoS/Sdy0Li41uuhCe3XQlPbxNb8gPJZjUvnb+mwLrISxnOWxER6zSXz5xGzsSnd62x92/a4p/cFKy2a/Zk+x1rYkL+1k16DCn88dfjzbkbTzXlajTLlKfhd6Y8jee752+2aPr315fOXHltyYHte0efPRHVNvbeo89Y0RkgWJKtjCwnqb8Y6DczaZABAerQM4DypqMqF/soMcjTe46/u/8Qf5NnP1+De18fo1ffIA/fof6ePitlo/O1frGMl6fnST9/97H+bh49A908OvnpPXr46ty/9Hd36x1g9Nl4sZBPpl+qAkcoe3kEHBo+tP9gb5NbuLtW39FNa+rqqTf39TV5feut9dyKXJqtkmjYJEueW0XFc6dAPPYqyGuPIrntUZD7TkU07JAduh0Gxm2rp8lnubfBd4KXya+fp8l3SLeu7X+qVq1EhvVfv/AkTseYZrsZPHoG+ASO/rpVbdVRvDb2PHmws8mXlQ8P6d+5v7vR3M1D597D2+g1snThwgehnSS7isuBE+6z4qsT2zZMGFG2eMkOPmb/+Ua9e+L123fLHTx6tPPeI7uG7v1pd/dDRw63vnb9dn5e4xZr1Aes8Db7td+2fkKXNSsHrvxl/4hfPizgneFC5kU7HC7pgbe79zIvg9cIH4N3Hw+Nd18PzqOfp8ZzoLvR9zuNhr3+YpmXXX9Y3BS9anXnzdu3je+5fNXkVoGegV3ddN6Dc3jmGhTsm2dpTHTyg6ZN17EZ6dHpEqTkZOt1H7fAGR46715unFtPN9bc203j9g2M1/7eGvfVyKzPVl99UMQtfvL8Lj8sWT6xZ/HCRTqbGbfBRsU0yMi59fbxDJrqsIhH4q/cEDKyQ407fTq36OXmsQ/G1Cg33n2gG+M2wEPj0ded8+jlafIe4efpe0DNlx1Rv4E/cUSti7t/GDZv3frpPWpWrzfbzeR988HDp4UPHDncct/hyJ4//rSvx6HDR9veunGvhNHo4woMyL/L0z2oz+z5QzsvXzBw5oMz03+pWPrPPWZPs1WMLWWpUPnT/UHevoN93fy6emq9OvsafLr6Gj2+8Hf3Hlyzds2zaXlfFlYsw6fkzpFjDTwV6uvvHdB3zvgxR2HMZ7IDfpm2Z+mtG5WN6dm5zQY/d48RXnr3Pu56t55wT3X30Lh946Zx7wXzwzAFMYd9UazzWYnf/xwxoqlYNH/e7fmC80UEBeTs5WMO6OVl8PzGw+g1yMfdZxoS2H2KwOwP9stzIF+eIkeLFCoXVTz0w7OFi3x4pkjh904VLFjuZMGCJXcXylNyVd6gIjNyBRQZm8uvwKjC+Ytu/X1N/4Crf5gJ1KH/BR0SEVFRunph4d0bUd9dWDN3+vEf5s84un3Z9EObl808fPnEzGvTeoc5XseM1fO7xN8+s+D+rfPf3bh5ZtatJdNnnr15Zsb5m6fnXrkSNe1pGIbnpVkovnluVuyAzh8+fHh+wY17UG755Mkndi6bfGjzwunHt6+cfOKHeRNPrZ017tiOpSMi960YsvOnFW237F02eOuBlR227VnRZfeuVV8f3Lay6+Ftq788sm9Dz4M7Vs2K3LBo7s/rlyw80f/rqk9hYiEZVb9+fW/H7avLLy+csTRq2cXlp8PCyr9W+9PrVn81bK92H967dnrqmbULpx7b98Pkg00HVLmaPk92zitiLH1Y3D9694Zvrvy8u8/6Q5v7R5zcNeibU7uHdP9pw8j+P++Z1v/Yril9f941qseh9f1G7F03YOnd8zMvfF48x4NP4BFvZm3OqO4BHT+xLDs/85ftqzoeVGX/+q779278au/ejT32HtjY62j/DlVfa6FXLijIXrlQyKMWH+W5+/2sicf2rPt686bvu+7evKrr/h8Wjjm3fn3G/yUqNDRUWD513vXIzd/s2f9Dt10H1n2986f1X+848MP0fZsWzz558/Ls2y/7P/3p2/mJr68lrHzIo583D754ZHvfXZHb+uzeubb7oY3L5x+dN2vOlTNnFojp86c/V3di389ecOnw1j57j2zq/uORLT13Rq7vfmDr8tlHNy+Zc2LTiq9upM//snPoF6V06TxJtT4Mfjiub+vDe3aMnX1kw9iIszvHDT22adKIU1umDz+9bsKQg2vG9P5p1aihe1b0n7Pmu/GHWn1a8PanJTwSoXyGY/ll9WaUrrZt/thGT68cn3bh+vFJUQu/nXb6xomZp6+dmn322tmZl8YNqpyQUbmM4sLCwuQLR+fcuH1m4aW7v6y4VrdukD2jfK8SB22Vvx3R6PHmBXNO7FgxPXLPqkn7ty2buH/Xuil7Nq+cdvj65QW/XD41O7pixYpSRnqhPJk8oN/ltTNGHlo3c+yBbcsn7N+6cvJPO1ZPjVy9YOaJDcumHftxdfejO3/odmDX6m7bf1zRecPSJe1WL1/SZuXSpa1XLFvR9vv1KzpuX7+q87Ytazuv37H+i81bfui6q9/X4aczqo/G/Z8Adej/Z/FWz9RBDiKXK4dFVUJDsaCKGve6FUPZ1EkmLaxYEUtwLoMoIKlp2dGt5gWR1fKqTWn2qaEq6q/sDAnBjiBwFGoYEhLiyJMnj7NAgQIu1Qmo8uwcC2p+VUBflrsESE+tryI40OzYmJ08oJOApDJWbVZ/CU12ymWUB/SQgIAAW4ECgbGFQkIehebLd//TcqH338+f40HJAt4PC+f0faymqe9pIW+Wbc1If1qc2n6Vnyoqw/TyZ/Sm6Vf7Qu2v/+vFrrS0jMKKMIbUvk0vqSxh3L6uPWq5NH1qO1Wb1Hoyqj99nJonrZwaqm1Qy6oCOjN0JunLZ3autqdELo/E0FC/p4Vg4VOqVMgjVQpBCP35OH9+c0y+fF7Jaj2Z6fiz8WB/6liFMPU+UMN0ku17V7UDyqn3e6qo129CVJ1q+9X5QOWVPoQ0dY7J0sa0smnlVB2qpMWrfan2qTqn+Pn5WQvD4i+9qGNWFTVdFTWvqutNtO1fpOOVTaUO/ZWR0QKUACVACVAClMA/jwB16P+8PqEWUQKUACVACVACr0zglRz6K2unBSgBSoASoAQoAUrgLyFAHfpfgplWQglQApQAJUAJvF0C/yCH/nYbSrVTApQAJUAJUALvMgHq0N/l3qVtowQoAUqAEvjPEPjPOPT/TI/Shr4SAUJItn7jWHbzvUrlqk5VXrXMq+TPbl7VDlUyy59VWkZlMsufWfyr6MgoL42jBCgBhKhDp6PgnSKg/tnWVt1nukVFRfEZNUx1KDduEG1sbKy5+7h1vrlLds89feF+/1u3EtzVeDU9fTlVT4VG3wYXfO+bPPk/6B5cpGyvQFXyvdc7RJWCH32TI33+rM5BN/P06VPj0s3nPIJLfpEjV5keRQqU65F30Lj93pCW4a95hXj28ePHhueJ12sAABAASURBVBvx8W6dun8fnKvU10XVsuv2RbnfIUQH6TijOsvWjTDkLdslp2p/Runp43buvKEt+N5XeVp0/y4wfXza+eKtR835SnUpMGnSHr/ncYuPmot+3CNn4Q975VYZBL33VUie0t1y5S3bI2eR0n1yqbwKv9/NO/2f0K3Tdm6OvO/1Krjzxo3M2opT23oj3i0HlM1dtlvh0rXG5LqVkOD++DExpNX9Yrh0aaSubbfvvB88eJDh73ePmL/dkCe0k/+KFXsy/VWzL+qk15TAv5EA8280+p9nM7Xon0BAdR69J0yutGXbrvkLN0RVftEmcH7csFm78pav36FryHvdFs9buHlVjC15cf+xizaVrPjFtgp124Wv33PFM325YTMO17h64+rWZHvS9qdxj76/G3tn5YOEpxtjk2JXx8U/WhL79HHP9PkzO4e62SWbLxcv/MmAb7/sOWZ9dPzDFU/jrk57mnR/+ZKVK9YOmrS90YtlLxGi6Tthd5myVccNK/Ne97VrtmxdlGhNnJdkt2/9svvCTZVK9+u+46frQaD7D07dlWLrmJgQs7xJh3llM0pPqwvS2M4Dvy2dYrFvTExhOqbFp4WQjgcNnFsl3mmdn4DFmmnxd6IfV3z06M7cJymxa59aY7dZnSk/JYnW/RanfWucLXZtfPyjpfFx0V3cAn59vgiItVrGJNis2wd1XFwU9P5u7lGvg4p3KxxaeWREaKUvl0c/erzkXuydaeeuHF9WvHyPH2t0GhVxKSbmD787fN2xB/p+E79vsHrHT9uKVRowuM/k7T5pNqaFN27ENroTfWf6Iyv5KC2OhpTAu0jgdzfVu9hA2qb/DgFf38t6DjEBWp5nDQaDI33LwWFoRk798f25s5f1ILK9fK4gv6iSBQpMKlEgX6/SRYqN9PQwX7LZEz79duTcHOnLORy2IEQko6dX0ObSxcoNKVfi4/4flP6wZ7niZUDe61m69Huz0+fP7Hzj9tOFv+oRMZTnZFORfAVnFc8f2rVkkfc7lSxYZmyBPIUPBfh4/O7XfT54QPRLxmyqsXLFqkGSZCkTHBD0c2jhYlOLFwjtX7Rg4Vk6LU602Z6Ef91nUrsDB+48d5pp9TtkMVgiipe32fAYY0zS4l8Mjz98qGGQnIfTsIrRoI15MX3XrpNmhmOCeA0vMxxnTUvPlz/3qXJlykd8WObjfgULlVvg7uF/12DwfJQvb7ElH5Qt3/vDMp/1KV641Gofg/357363pCSZZULEmQt6JYFNz3+7HvQNl6PUlyXt9pQpRLAWL5Q3/7FyoSVnvVf4g4El8pVY5u/n8/jB3atV2zQfOeAqPFlJs0ENTZLTpDO7hXh6moK0Rr5ObLIl7M6dOzo1TZX586P4PTv2GXU6ntHpXu9Piqp6qFAC/wYC1KH/C3qJmpg9AiLPuQkMkwvrdFYtr3uUvtS3Sw+Xm73kh+5Gk3vQyMFffHft2HcTo/aO2ndyd8TFk7uG7e3XqXHvqh9U7BJ1bNbl9OUSHTF5FEVgiGjdfXjroBNHdvQ/e2BTr9M/bfrm7IGtfa/8tKnvvfT5MzpXH3v3GbE0p8ndLednVaqdOBc5ftvZgxNunt479sHPu0bsOrpz8JheHT7bk77skl1Haq3afKC3l4cHKpY3z4hfj00ee3JfxN5juwcfP/Xj4OVtmzXpyurQ0SRnUpuuA2Z+dOOFx9gOjf5jl2eA7/z1A5871PT60841glEjEzGngCwprJa9nRafFiazRnfEcjm1Wp1dr+Nj0+Lbh73/9MCmQaf3fP/VkQbVy10QHIJVEeznen5VYfuONV8c37mxe9RPO4feK1eunKiWqdAuQvfwYSzPcIaUYF4jo3RHvg8GlIpLjvsOK07xow8/7H/x0LhJJ/ZE7D+9L+L8xQPjl29cPKAbFoULTxIc9foM3twuXVGEec7IGdzz6dw8fHUeHvn37zvWZsaaSzUIISyCw+qmBGjNPoX1BjPRsZwVouiHEnhnCTDvbMtow/5zBCSF9RAlKQ9DsE3H65PSAKjvxh89ii4pOKx+HTuFre0WXvFoWlpa2KNHLdf69b0d6XeOaprVluTvdAmKxSHifT9f84+6cjfw0p2YgFOXYgLS7wTVvJmJDraG/kEBDItl05Onj4vujLqfL1XPpUsmcDzci+VUvXcf3S/lFGxePXu0WXlwV8SJF/OMH1wlvvR7H+7jeW0S5tk8DOP9fFeq5pVEgZEkkhiEMnwtrmZJFZ4IWpcg5mJZ3sEhJjo1Mt0PzAgmUXb5MoRYOUYbny7p+angFNxdgsDJEnmkdegszxPSnZhsbt4cg/QGnfExy8qpTj4t2SY4WvEcNpYt+eGgnau6/5IWnxaWzh+QEJK38BynILM2uzVXWrwayopsctjt/rzW7Y6Pf56fFcURsPqHjfX7TtlRQE1nFN4bc7w/r9En6ThjohpHhRJ4VwlQh/6u9my22/XuZJQVyV0mbD6zu5eArKbnDv3QmWMFNq//saxOy97y1OJfs9vidbDLEyQt7AA99AqnHRrWZfKoyvWHRnxatfeQGvW6D6xQa1TR7OhS/yDJuIh2v3p7Gg7/evlC8WZtRqyt2Hj0xApNprYI+2L+e5cfJHul17Nq/91SO7bsKwjO7xRvZjL9q2IajJNYIskykr0Fwf58YVC0QoTJkRDN6xG6FxiIFJTVQSSNJEq5GYXFjCL87rG/WswuEDdRcPpIoi1Gcab84ZG8mkcUmSBZZL293D1jpYJyhg7dgeScCLm0Wpa/ZWM1z/9ATP6a3bV2a/wnHOceu39Lnwz/Oh7GWO7Vo5kDaUz+mNHkVutME9mF3CVZCTFw2tO5/H2H5AgK3iRrtRVWrdvRYdrSSA/EagI5jVsendYjWcAcdehp4Gj4ThKgDv2d7Nb/ZqPsTlnndDk933uvDImISPenHTHjjjDx5FhNrCKjDHeZGRFb2GKif0pcohvHscmFCuV7UrZ0oYRSJfJby5QpZClZolB86dIllIzKZRRX5b1ct+dMGzasUoWqiz/55KOoXLm9vWTW0WbfgT2Tho9c2vHcnUSPtHJEFr0YovjyLG9BmLWkxb8YghMPQbLIYVl+hLFWSEs364S8Og3nZja7P4W4TN+fQxqySywnu6x+guh0JSBHrBqXJvD0gL37MM5HkVw+Rr02wVvr93yRlJZHDQXJ5eFyOnWdOjW0hIWGPrdDTUsTWXLkwkQy8Br8mLUJz3foGofOk2EZDa8z/q7utHJqqL5O6DtwRj4thx1Go+muGpcmTkHRy6LD54P3Qp1bZrY680WXeut5hrkkuayVbt6La/LoaXJOweWE/meSPDnBllaOhpTAu0iAOvR3sVf/QW36K025efcRx2ENY9Kbnenr1XLIwXGci2FQPpZhQ9KnZXXuYXQLNrOcByM4D02aFN7nwKp+/Q9tjOhzYN2wwZFbRo3esvqb81mVfzGtYrmguI1zO3y/a2GXLycP697m/XKfL/L2D1HOXLjZ8Medlz5Ny2/W6OPNnJtFo/AhWifrnRafPvz5wlO/2If3PpNcTlv+4IAjhQr5WNPSiaLJyxIi6zjdNYhTQDL9MIILy0hEEpFEncKY0mfcduCSz8LvNpZmOS2qXPnzu127Pnsfnj4POH3m3p1HRoxYbDByEsrkcEmOYIeQJMliym2WTXru9D8pmD+FwayoiM7ggwdjDS8WB/34yD1HDkWy1ZItT34xKcr69Hlu33rEsRgzWhal6nS3ljkdaDTOEB2OxM3bDjTftGF/fUYhvgG+vs4rTT9ypS9LzymBd40AdejvWo/+R9sTGRnJLVq0Rc9zKMVg0Ks70+ckfH3dnwQHBl0TZCXP5dtPKu07citnVFSUQS1z7Ngx/Z5DV/P8GHm91K1bCe7PC6knnCEPZjgTg7iHxCGKUIZX/2ucKqqjQdk8jh17oN8deTl/ZOQdD7WsWqz6R8FJH5cvck6SpeuS5HRgLD93hkEhnk98fHzuOgRLvgvX75ffe/RqkFq3au/Wo1fN2w/dKtBn4PTO9x/cDQ4tWmLX6FnhT3C6b7KLsiWfJIg68HOPd+26yUQQwkQCH1XHpUuXNGr9aYIZXmJ5XTzCnE9sgv293ccue6n51PDitejSDtn5vrenz9ViRfNGpZVJH05c8rMx8tBRI8/jFA3PZroDFkVboCS7NHnzhTzJnz//8x36ggVd7Xqd5pYsJAa27jGi5uGzN3xVXirfo9DWzfsvFR70zaQwRrKUNOn1pwsHmc6l1b8O2jJr/g8eOp536bRuD9X4sDAsDxr45ekC+YtuUSQxSBLjPiWygw1rXtkWgem33FVGVN5dAtShv7t9+x9oWfom+urg0as7clkfwW7tTvqUGp8VffJ1t8Y7A/LkvvbDj3sbNftqVK+abWfVafblwsoNOs+v1fqLiX3Cu47tc+LivTzpyxGkKcAw7lpJIj6RRy6Va9BlbuUvBzau+PWQZhVzFG5W3r9w0+Lp82d0rjqmxuGDczdq3f+bFl+NbPn1kFYVP2405MO+U7Z9MmPG7LpJiY9CPL0MPxbJ53kmrXxY1dD7det9ugOz6O6SlRvCWrQf9WXVxhOqNOg4q0KHTmMbt/1y5LDrN69/WqhYib3TpvVc9kFwcHxaWTW0OqwhrM6kkSWpSPjQCRW/K9Lis8Zfzq1cp/2kKo07TPlk587//2IXxqBPMbn57GcMbv5Xbt7p2abbjNZVW06r0qL9+GYTpy9q6HRY7OU/LLutR+vPrqi6XxSHLcVdEh0MIfY7DKskvpiedp1sTfKUFVHqMzAsARYfv3tqULVuhYkEiT9b7UmDmrSeGF6v7aCqXvmblW/Y5dsmX/Se0UsQHNXdTF7runRoOTsiIix1J67qDUw0GxlF8mNkZzTP4sdqnCphVfMlD+vXbk9A3pA9gpASKziTb/FEjlbTqFAC7zIB6tDf5d79j7WNw2yC0Wg8bdLyv3PoKobmNUuf7tu91bycOXPsI4qklYlQy6UI4S7BViskp7dUv26VjYVK5PpdOaI47QzDXCHElWtcxNTw5MS4Fi4ktXMpjg520d7B5bBXU3W/RHCtOp+LZUoXTVCwVB4ebnc8f/biF3NnLmil17F+FT/9aEvfnm03NKpe8ndfOBvydZV9tatUnpMz0O84waKfyDhbSYrYUpIcH+cvEGypWbvmoqlT+y0sUyDwD++eiSwkgIe94rIlFIf3x21ciqujjMUOgiS0SbYl1T516tzz35hWNq9nSp0a1daUK/feRqS4EkXJXlFUnOGy6CjmadY9qVnps2X56xfckVkbOUkmBOFHWk57jkUoLqN8ERERjMNqv6/nDFFuPPrd7wdQ8y8f3epMeKsWcz75uNwBUbDldhJHTReSwyXF+RHPKZa61WssC6/fYcbgHrV+19ZklIwURbZoNJozehZfV3WlSf2K+W/27NLwhwA//60s4g5gRvndu/e0fDSkBN4lAtShv0u9+R9uS8Vok4BwAAAH7klEQVSKodZGrcIOdmhZbwmy1szw29Jtqhc/dmHb2GFdW7cY2jk8fFrndi1ndW3bZuKCsd8MXjylzZZy+byS0yP8rGze7Q3qVx3dpmXTqR3C237XoXXb7zq3bT2zXXj4jPBWree1bRe+Pn3+jM7V3ejiaZ1uLp3WZUx4i2Yj24c3m9GyRYulrVq3nBsxsPuoLYsGzg2vV+5+RmXnTwzb1+DTiYNaNG4xuG2z1jNat2o5p03rxiPmDmvUa9X0zhtetBf9djRvXG9VkzrVBzRtVGNwi6aNx7du3XZ6h1Ztp3RqB2G7dssjIsKef5sd7COLJrZ+OGhe15GtmzYY2LRZ/UlNm9af27xl02kju4dP2Ly49+6IihWl31T/Ifi4tCm6ScO6Gxo1rLw2p9nyfJecPiM4dKVHh1YrO7RsNt1DCsnQ6c+KCDv144o+/bp2bDmsddMmC9q2bDK3Q/OWgx+dW9J3xcxOYPMfbbA/KZ5Sr1bdH+vWrDrj/cLBf/jvbu1rlTzR/+umYxs1rT69eLB0Ob1N9JwSeBcJUIf+Lvbqf7RNk/tVt0X0axQTEYGVrBBMGFQn8duBNS5MHtzo+MRh9a+VKxdkzyh/zy8+vzEton7U1IjGJ2aMbnxalWngeGYMb3Jyxujmp2eODMvQEWekq0CBAq5Jw+remDq08YmF41seWjiu1flOYeUTMsqbPk5ty+zxjeLnTGh+ev7YFlHfRbR4XK7cH7+clr7MmP4Nb80a3eTcd8Mb/DIvotGlucMbnpk2tOGpiQManhrXr/7F9HnTzitiLM0cWO/+wqFNjy0d3/bQgjHNb7VvX/F3Xy5My5s+rAjOfsnEFo8XjWsZrZ6nT0t/3r9Hw1szx7S/ERqKnz8yT5+edq72zbwJLc7NG93i3JSIuhk6/7S86vvyeZMbxUyPqHO7Vq0CGX7hrWXdcnFzIsKeZmVbmj4aUgL/dgLUof/be5Da/y8lQM2mBCgBSuDNEqAO/c3ypNooAUqAEqAEKIG/hQB16H8LdlopJfB2CVDtlAAl8N8jQB36f6/PaYspAUqAEqAE3kEC1KG/g51Km0QJvF0CVDslQAn8EwlQh/5P7BVqEyVACVAClAAl8IoEqEN/RWA0OyVACbxdAlQ7JUAJvB4B6tBfjxstRQlQApQAJUAJ/KMIUIf+j+oOagwlQAm8XQJUOyXw7hKgDv3d7VvaMkqAEqAEKIH/EAHq0P9DnU2bSglQAm+XANVOCfydBKhD/zvp07opAUqAEqAEKIE3RIA69DcEkqqhBCgBSuDtEqDaKYGsCVCHnjUfmkoJUAKUACVACfwrCFCH/q/oJmokJUAJUAJvlwDV/u8nQB36v78PaQsoAUqAEqAEKAFEHTodBJQAJUAJUAJvmQBV/1cQoA79r6BM66AEKAFKgBKgBN4yAerQ3zJgqp4SoAQoAUrg7RKg2p8RoA79GQf6kxKgBCgBSoAS+FcToA79X9191HhKgBKgBCiBt0vg36OdOvR/T19RSykBSoASoAQogUwJUIeeKRqaQAlQApQAJUAJvF0Cb1I7dehvkibVRQlQApQAJUAJ/E0EqEP/m8DTaikBSoASoAQogTdJ4I8O/U1qp7ooAUqAEqAEKAFK4C8hQB36X4KZVkIJUAKUACVACbxdAn+1Q3+7raHaKQFKgBKgBCiB/ygB6tD/ox1Pm00JUAKUACXwbhF4txz6u9U3tDWUACVACVAClEC2CVCHnm1UNCMlQAlQApQAJfDPJUAdevb7huakBCgBSoASoAT+sQSoQ//Hdg01jBKgBCgBSoASyD4B6tCzz+rt5qTaKQFKgBKgBCiBP0GAOvQ/AY8WpQQoAUqAEqAE/ikEqEP/p/TE27WDaqcEKAFKgBJ4xwlQh/6OdzBtHiVACVAClMB/gwB16P+Nfn67raTaKQFKgBKgBP52AtSh/+1dQA2gBCgBSoASoAT+PAHq0P88Q6rh7RKg2ikBSoASoASyQYA69GxAolkoAUqAEqAEKIF/OgHq0P/pPUTte7sEqHZKgBKgBN4RAtShvyMdSZtBCVAClAAl8N8mQB36f7v/aevfLgGqnRKgBCiBv4wAdeh/GWpaESVACVAClAAl8PYIUIf+9thSzZTA2yVAtVMClAAlkI4AdejpYNBTSoASoAQoAUrg30qAOvR/a89RuymBt0uAaqcEKIF/GQHq0P9lHUbNpQQoAUqAEqAEMiJAHXpGVGgcJUAJvF0CVDslQAm8cQLUob9xpFQhJUAJUAKUACXw1xOgDv2vZ05rpAQogbdLgGqnBP6TBKhD/092O200JUAJUAKUwLtGgDr0d61HaXsoAUrg7RKg2imBfygB6tD/oR1DzaIEKAFKgBKgBF6FAHXor0KL5qUEKAFK4O0SoNopgdcmQB36a6OjBSkBSoASoAQogX8OAerQ/zl9QS2hBCgBSuDtEqDa32kC1KG/091LG0cJUAKUACXwXyFAHfp/padpOykBSoASeLsEqPa/mQB16H9zB9DqKQFKgBKgBCiBN0GAOvQ3QZHqoAQoAUqAEni7BKj2lxKgDv2liGgGSoASoAQoAUrgn0+AOvR/fh9RCykBSoASoATeLoF3Qjt16O9EN9JGUAKUACVACfzXCVCH/l8fAbT9lAAlQAlQAm+XwF+knTr0vwg0rYYSoAQoAUqAEnibBKhDf5t0qW5KgBKgBCgBSuDtEniu/X8AAAD//99vBYEAAAAGSURBVAMALLSUHnHzX1kAAAAASUVORK5CYII="
                        />
                    </defs>
                </svg>
                <div class="flex flex-col">
                    <span class="text-2xl font-semibold text-[#1B84FF]">{{
                        currentTime
                    }}</span>
                    <span class="font-semibold text-gray-500 text-md">{{
                        currentDate
                    }}</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 py-4">
                <h1 class="text-2xl text-[#1B84FF] font-semibold">
                    Hallo & Selamat Datang
                </h1>
                <h5 class="text-sm text-gray-500">
                    Data anda hanya digunakan untuk proses absensi dan tidak
                    disimpan
                </h5>
            </div>
            <div class="p-4 mt-4 bg-white rounded-lg border border-gray-300">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="py-2 border-b" colspan="2">
                                Informasi Personal Karyawan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-300">
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                Nama
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{ notifyData?.data?.employee?.name || "-" }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                NIK
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{ notifyData?.data?.employee?.nik || "-" }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                Departemen
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{
                                    notifyData?.data?.employee?.department
                                        ?.name || "-"
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                Jabatan
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{
                                    notifyData?.data?.employee?.role?.name ||
                                    "-"
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                Waktu Absen
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{ notifyData?.data?.dateTime || "-" }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 w-1/3 font-normal text-gray-500">
                                Status Absen
                            </td>
                            <td class="py-4 font-semibold text-gray-700">
                                {{ notifyData?.data?.status || "-" }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4" v-if="showAbsenceStatuses">
                <AbsenceStatuses
                    :type="notifyData?.data?.type"
                    :title="notifyData?.data?.status"
                    :message="notifyData?.message"
                />
            </div>
        </div>
    </div>
</template>
