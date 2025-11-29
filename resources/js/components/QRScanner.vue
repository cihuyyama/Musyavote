<script setup lang="ts">
import jsQR from 'jsqr';
import { onUnmounted, ref } from 'vue';

interface ScanResult {
    success: boolean;
    message: string;
    data?: any;
}

const props = defineProps<{
    onScan: (data: string) => Promise<ScanResult>;
}>();

const videoRef = ref<HTMLVideoElement>();
const canvasRef = ref<HTMLCanvasElement>();
const isCameraActive = ref(false);
const isProcessing = ref(false);
const isScanning = ref(false);
const lastResult = ref<ScanResult | null>(null);
let stream: MediaStream | null = null;
let animationFrame: number | null = null;

const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });

        if (videoRef.value) {
            videoRef.value.srcObject = stream;
            await videoRef.value.play();
            isCameraActive.value = true;
            startScanning();
        }
    } catch (error) {
        lastResult.value = {
            success: false,
            message: 'Tidak bisa mengakses kamera',
        };
    }
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
    }
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
        animationFrame = null;
    }
    isCameraActive.value = false;
    isScanning.value = false;
};

const toggleCamera = () => {
    if (isCameraActive.value) {
        stopCamera();
    } else {
        startCamera();
    }
};

const startScanning = () => {
    isScanning.value = true;
    scanFrame();
};

const scanFrame = () => {
    if (!isCameraActive.value || !videoRef.value || !canvasRef.value) return;

    const video = videoRef.value;
    const canvas = canvasRef.value;
    const context = canvas.getContext('2d');

    if (!context || video.readyState !== video.HAVE_ENOUGH_DATA) {
        animationFrame = requestAnimationFrame(scanFrame);
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height);

    if (code && !isProcessing.value) {
        processQRCode(code.data);
    }

    if (isCameraActive.value) {
        animationFrame = requestAnimationFrame(scanFrame);
    }
};

const processQRCode = async (data: string) => {
    isProcessing.value = true;
    lastResult.value = null;

    try {
        const result = await props.onScan(data);
        lastResult.value = result;

        setTimeout(() => {
            lastResult.value = null;
        }, 3000);
    } catch (error) {
        lastResult.value = {
            success: false,
            message: 'Error memproses QR Code',
        };
    } finally {
        isProcessing.value = false;
    }
};

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <div class="simple-qr-scanner">
        <!-- Scanner Area -->
        <div class="scanner-area relative overflow-hidden rounded-lg bg-black">
            <video
                ref="videoRef"
                class="h-64 w-full object-cover"
                :class="{ hidden: !isCameraActive }"
            ></video>

            <!-- Scanner Overlay -->
            <div
                v-if="isCameraActive"
                class="scanner-overlay absolute inset-0 flex items-center justify-center"
            >
                <div
                    class="scanner-frame relative h-48 w-48 rounded-lg border-2 border-white"
                >
                    <!-- Corner borders -->
                    <div
                        class="absolute -top-1 -left-1 h-6 w-6 border-t-2 border-l-2 border-white"
                    ></div>
                    <div
                        class="absolute -top-1 -right-1 h-6 w-6 border-t-2 border-r-2 border-white"
                    ></div>
                    <div
                        class="absolute -bottom-1 -left-1 h-6 w-6 border-b-2 border-l-2 border-white"
                    ></div>
                    <div
                        class="absolute -right-1 -bottom-1 h-6 w-6 border-r-2 border-b-2 border-white"
                    ></div>

                    <!-- Scanning line -->
                    <div
                        class="absolute right-0 left-0 h-1 rounded-full bg-green-400"
                        :class="{ 'animate-scan': isScanning }"
                    ></div>
                </div>
            </div>

            <!-- Camera Placeholder -->
            <div
                v-if="!isCameraActive"
                class="flex h-64 flex-col items-center justify-center text-white"
            >
                <svg
                    class="mb-2 h-12 w-12 opacity-50"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                <p class="text-sm opacity-70">Kamera siap untuk scan</p>
            </div>

            <canvas ref="canvasRef" class="hidden"></canvas>
        </div>

        <!-- Controls -->
        <div class="controls mt-4 flex gap-2">
            <button
                @click="toggleCamera"
                :disabled="isProcessing"
                class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        v-if="!isCameraActive"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                    <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                    <path
                        v-if="isCameraActive"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
                    />
                </svg>
                {{ isCameraActive ? 'Stop' : 'Mulai Scan' }}
            </button>
        </div>

        <!-- Result -->
        <div
            v-if="lastResult"
            class="mt-4 rounded-lg border p-3"
            :class="
                lastResult.success
                    ? 'border-green-200 bg-green-50'
                    : 'border-red-200 bg-red-50'
            "
        >
            <div class="flex items-center gap-2">
                <svg
                    v-if="lastResult.success"
                    class="h-5 w-5 text-green-600"
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
                    v-else
                    class="h-5 w-5 text-red-600"
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
                <span
                    class="text-sm font-medium"
                    :class="
                        lastResult.success ? 'text-green-800' : 'text-red-800'
                    "
                >
                    {{ lastResult.message }}
                </span>
            </div>
        </div>

        <!-- Loading -->
        <div
            v-if="isProcessing"
            class="mt-4 flex items-center justify-center gap-2 text-blue-600"
        >
            <div
                class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"
            ></div>
            <span class="text-sm">Memproses QR Code...</span>
        </div>
    </div>
</template>

<style scoped>
.animate-scan {
    animation: scan 2s ease-in-out infinite;
}

@keyframes scan {
    0% {
        top: 0%;
        opacity: 1;
    }
    50% {
        top: 100%;
        opacity: 1;
    }
    51% {
        top: 100%;
        opacity: 0;
    }
    52% {
        top: 0%;
        opacity: 0;
    }
    100% {
        top: 0%;
        opacity: 1;
    }
}
</style>
