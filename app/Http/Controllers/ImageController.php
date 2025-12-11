<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    /**
     * Menampilkan gambar dari storage
     */
    public function show($folder, $filename)
    {
        try {
            // Validasi folder yang diizinkan
            $allowedFolders = ['calon_fotos', 'photos', 'uploads'];

            if (!in_array($folder, $allowedFolders)) {
                Log::warning('Unauthorized folder access:', ['folder' => $folder]);
                return $this->defaultImage();
            }

            // Cegah path traversal
            $filename = basename($filename);
            $path = $folder . '/' . $filename;

            // Log untuk debugging
            Log::info('Image request:', [
                'path' => $path,
                'full_path' => storage_path('app/public/' . $path)
            ]);

            // Cek apakah file ada
            if (!Storage::disk('public')->exists($path)) {
                Log::warning('Image not found:', ['path' => $path]);
                return $this->defaultImage();
            }

            // Dapatkan path lengkap
            $fullPath = storage_path('app/public/' . $path);

            // Validasi bahwa ini adalah file gambar
            if (!$this->isImage($fullPath)) {
                Log::warning('File is not an image:', ['path' => $fullPath]);
                return $this->defaultImage();
            }

            // Dapatkan file content
            $fileContent = file_get_contents($fullPath);

            if ($fileContent === false) {
                Log::error('Cannot read file:', ['path' => $fullPath]);
                return $this->defaultImage();
            }

            // Dapatkan mime type menggunakan finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fullPath);
            finfo_close($finfo);

            // Validasi mime type yang diizinkan
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

            if (!in_array($mimeType, $allowedMimes)) {
                Log::warning('Invalid mime type:', ['mime' => $mimeType, 'path' => $path]);
                return $this->defaultImage();
            }

            // Return response
            return response($fileContent, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=31536000')
                ->header('Content-Length', filesize($fullPath))
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Error serving image:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->defaultImage();
        }
    }

    /**
     * Validasi apakah file adalah gambar
     */
    private function isImage($filePath)
    {
        // Cek ekstensi file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        // Cek mime type dengan finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            return strpos($mimeType, 'image/') === 0;
        }

        // Fallback: cek dengan getimagesize
        if (function_exists('getimagesize')) {
            $imageInfo = @getimagesize($filePath);
            return $imageInfo !== false;
        }

        // Jika tidak ada fungsi validasi, return true berdasarkan ekstensi
        return true;
    }

    /**
     * Menampilkan gambar default
     */
    private function defaultImage()
    {
        $defaultPath = public_path('default-avatar.png');

        if (file_exists($defaultPath)) {
            $content = file_get_contents($defaultPath);

            return response($content, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        }

        // Fallback: buat gambar default secara dinamis
        return $this->generateDefaultImage();
    }

    /**
     * Generate gambar default secara dinamis
     */
    private function generateDefaultImage()
    {
        // Buat gambar default dengan GD
        if (function_exists('imagecreatetruecolor')) {
            $width = 100;
            $height = 100;

            $image = imagecreatetruecolor($width, $height);
            $bgColor = imagecolorallocate($image, 240, 240, 240);
            $textColor = imagecolorallocate($image, 150, 150, 150);

            imagefill($image, 0, 0, $bgColor);

            // Tambahkan teks "No Image"
            $text = "No Image";
            $font = 5; // Font internal GD
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            $x = ($width - $textWidth) / 2;
            $y = ($height - $textHeight) / 2;

            imagestring($image, $font, $x, $y, $text, $textColor);

            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);

            return response($imageData, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        }

        // Jika GD tidak tersedia, return 404
        return response('Image not found', 404)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Menampilkan foto calon
     */
    public function calonFoto($filename)
    {
        return $this->show('calon_fotos', $filename);
    }

    /**
     * Menampilkan foto peserta
     */
    public function pesertaFoto($filename)
    {
        return $this->show('photos', $filename);
    }

    /**
     * Versi sederhana tanpa validasi kompleks (alternatif)
     */
    public function simpleShow($folder, $filename)
    {
        try {
            // Cegah path traversal
            $filename = basename($filename);
            $path = $folder . '/' . $filename;
            $fullPath = storage_path('app/public/' . $path);

            // Cek apakah file ada
            if (!file_exists($fullPath)) {
                Log::warning('Image not found (simple):', ['path' => $fullPath]);
                return $this->defaultImage();
            }

            // Dapatkan ekstensi untuk menentukan content type
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
            ];

            $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

            // Baca file
            $fileContent = file_get_contents($fullPath);

            return response($fileContent, 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=31536000');
        } catch (\Exception $e) {
            Log::error('Error in simpleShow:', [
                'error' => $e->getMessage()
            ]);

            return $this->defaultImage();
        }
    }

    /**
     * Menampilkan foto peserta berdasarkan kode unik
     */
    public function getFotoByKode($kode_unik)
    {
        try {
            // Cari peserta
            $peserta = Peserta::where('kode_unik', $kode_unik)->first();

            if (!$peserta || !$peserta->foto) {
                return $this->defaultImage();
            }

            // Dari debug, foto_field: "photos/KGfP3W4NepQ4joI0uSPmkoYtWEuU0GlNNZIoSTc5.webp"
            $fotoPath = $peserta->foto;

            // Ekstrak filename
            $filename = basename($fotoPath);

            // Langsung panggil metode show dengan parameter yang benar
            // show('photos', 'KGfP3W4NepQ4joI0uSPmkoYtWEuU0GlNNZIoSTc5.webp')
            return $this->show('photos', $filename);
        } catch (\Exception $e) {
            Log::error('Error in pesertaByKode: ' . $e->getMessage());
            return $this->defaultImage();
        }
    }
}
