<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ImageProcessingService
{
    /**
     * Process and save profile photo with cropping.
     * Returns the relative path to the saved image.
     */
    public static function processProfilePhoto(UploadedFile $file, ?string $cropData = null, int $userId): string
    {
        // Create user directory if it doesn't exist
        $userFolder = public_path("uploads/photographers/{$userId}/profile");
        if (!File::exists($userFolder)) {
            File::makeDirectory($userFolder, 0755, true);
        }
        
        // Load source image using GD
        $sourceImage = self::loadImageResource($file);
        if (!$sourceImage) {
            throw new \Exception('Failed to load image');
        }
        
        // Get original dimensions
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // Parse crop data
        $cropX = 0;
        $cropY = 0;
        $cropWidth = $sourceWidth;
        $cropHeight = $sourceHeight;
        $sourceCropX = 0;
        $sourceCropY = 0;
        $sourceCropWidth = $sourceWidth;
        $sourceCropHeight = $sourceHeight;
        
        if ($cropData) {
            $crop = json_decode($cropData, true);
            if ($crop && isset($crop['x'], $crop['y'], $crop['width'], $crop['height'], $crop['imageWidth'], $crop['imageHeight'])) {
                // Crop coordinates are relative to displayed image, need to scale to source
                $scaleX = $sourceWidth / $crop['imageWidth'];
                $scaleY = $sourceHeight / $crop['imageHeight'];
                
                $sourceCropX = (int) ($crop['x'] * $scaleX);
                $sourceCropY = (int) ($crop['y'] * $scaleY);
                $sourceCropWidth = (int) ($crop['width'] * $scaleX);
                $sourceCropHeight = (int) ($crop['height'] * $scaleY);
            }
        }
        
        // Target size for square crop (800x800)
        $targetSize = 800;
        
        // Create destination image (800x800 square)
        $destImage = imagecreatetruecolor($targetSize, $targetSize);
        
        // Preserve transparency for PNG, but we'll convert to JPEG
        imagealphablending($destImage, false);
        imagesavealpha($destImage, false);
        
        // Fill with white background
        $white = imagecolorallocate($destImage, 255, 255, 255);
        imagefill($destImage, 0, 0, $white);
        
        // Calculate scaling to fit source crop into 800x800 square
        $scale = min($targetSize / $sourceCropWidth, $targetSize / $sourceCropHeight);
        $newWidth = (int) ($sourceCropWidth * $scale);
        $newHeight = (int) ($sourceCropHeight * $scale);
        
        // Center the image
        $destX = (int) (($targetSize - $newWidth) / 2);
        $destY = (int) (($targetSize - $newHeight) / 2);
        
        // Resample and copy
        imagecopyresampled(
            $destImage, $sourceImage,
            $destX, $destY,
            $sourceCropX, $sourceCropY,
            $newWidth, $newHeight,
            $sourceCropWidth, $sourceCropHeight
        );
        
        // Save as JPEG
        $filename = 'profile_' . uniqid() . '.jpg';
        $path = "uploads/photographers/{$userId}/profile/{$filename}";
        $fullPath = public_path($path);
        
        imagejpeg($destImage, $fullPath, 90);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $path;
    }
    
    /**
     * Process and save logo.
     * Returns the relative path to the saved image.
     */
    public static function processLogo(UploadedFile $file, int $userId): string
    {
        // Create user directory if it doesn't exist
        $userFolder = public_path("uploads/photographers/{$userId}/logo");
        if (!File::exists($userFolder)) {
            File::makeDirectory($userFolder, 0755, true);
        }
        
        // Load source image
        $sourceImage = self::loadImageResource($file);
        if (!$sourceImage) {
            throw new \Exception('Failed to load image');
        }
        
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $maxSize = 800;
        
        // Calculate new dimensions maintaining aspect ratio
        if ($sourceWidth <= $maxSize && $sourceHeight <= $maxSize) {
            // No resize needed
            $newWidth = $sourceWidth;
            $newHeight = $sourceHeight;
            $destImage = $sourceImage;
            $needsCleanup = false;
        } else {
            if ($sourceWidth > $sourceHeight) {
                $newWidth = $maxSize;
                $newHeight = (int) (($sourceHeight / $sourceWidth) * $maxSize);
            } else {
                $newHeight = $maxSize;
                $newWidth = (int) (($sourceWidth / $sourceHeight) * $maxSize);
            }
            
            // Create resized image
            $destImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            $hasAlpha = self::imageHasAlpha($sourceImage);
            if ($hasAlpha) {
                imagealphablending($destImage, false);
                imagesavealpha($destImage, true);
                $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
                imagefill($destImage, 0, 0, $transparent);
            }
            
            imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
            $needsCleanup = true;
        }
        
        // Determine file extension based on transparency
        $hasTransparency = self::imageHasAlpha($destImage);
        $ext = $hasTransparency ? 'png' : 'jpg';
        $filename = 'logo_' . uniqid() . '.' . $ext;
        $path = "uploads/photographers/{$userId}/logo/{$filename}";
        $fullPath = public_path($path);
        
        // Save with appropriate format
        if ($hasTransparency) {
            imagepng($destImage, $fullPath, 9);
        } else {
            imagejpeg($destImage, $fullPath, 90);
        }
        
        // Clean up
        if ($needsCleanup) {
            imagedestroy($destImage);
        }
        imagedestroy($sourceImage);
        
        return $path;
    }
    
    /**
     * Load image resource from uploaded file using GD.
     */
    private static function loadImageResource(UploadedFile $file)
    {
        $tempPath = $file->getRealPath();
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Handle different image types
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($tempPath);
            
            case 'image/png':
                return imagecreatefrompng($tempPath);
            
            case 'image/gif':
                return imagecreatefromgif($tempPath);
            
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($tempPath);
                }
                break;
            
            case 'image/heic':
            case 'image/heif':
                // HEIC is not directly supported by GD
                // Try to use Imagick if available, otherwise show error
                if (extension_loaded('imagick')) {
                    try {
                        $imagick = new \Imagick($tempPath);
                        $imagick->setImageFormat('jpeg');
                        $tempJpeg = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
                        $imagick->writeImage($tempJpeg);
                        $image = imagecreatefromjpeg($tempJpeg);
                        unlink($tempJpeg);
                        return $image;
                    } catch (\Exception $e) {
                        // If Imagick fails, return null
                        return null;
                    }
                }
                // GD cannot handle HEIC - user should convert to JPEG/PNG first
                return null;
            
            default:
                // Try to detect by extension
                switch ($extension) {
                    case 'jpg':
                    case 'jpeg':
                        return imagecreatefromjpeg($tempPath);
                    case 'png':
                        return imagecreatefrompng($tempPath);
                    case 'gif':
                        return imagecreatefromgif($tempPath);
                    case 'webp':
                        if (function_exists('imagecreatefromwebp')) {
                            return imagecreatefromwebp($tempPath);
                        }
                        break;
                }
        }
        
        return null;
    }
    
    /**
     * Check if image has alpha channel/transparency.
     */
    private static function imageHasAlpha($image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Check first pixel
        $rgba = imagecolorat($image, 0, 0);
        $alpha = ($rgba >> 24) & 0x7F;
        
        if ($alpha < 127) {
            return true;
        }
        
        // Sample a few pixels
        for ($x = 0; $x < min(10, $width); $x += 2) {
            for ($y = 0; $y < min(10, $height); $y += 2) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 127) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Delete old image file if it exists.
     */
    public static function deleteImage(?string $imagePath): void
    {
        if ($imagePath && File::exists(public_path($imagePath))) {
            File::delete(public_path($imagePath));
        }
    }
}


