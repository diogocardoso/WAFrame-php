<?php
/**
 * Image processing class with support for resize, crop, and format conversion
 *
 * @author DiogoCardoso
 * @version 2.0
 * @copyright (c) 2025, webavance.com.br
 */
namespace WAFrame;

use WAFrame\File;
use WAFrame\Exceptions\ImageException;
use WAFrame\Exceptions\ImageNotFoundException;
use WAFrame\Exceptions\ImageInvalidTypeException;
use WAFrame\Exceptions\ImageProcessingException;

class Image
{
    // Supported image types
    private const IMAGE_TYPE_GIF = 'gif';
    private const IMAGE_TYPE_JPEG = 'jpeg';
    private const IMAGE_TYPE_PNG = 'png';
    private const IMAGE_TYPE_WEBP = 'webp';
    
    // Image type constants for getimagesize
    private const IMG_TYPE_GIF = 1;
    private const IMG_TYPE_JPEG = 2;
    private const IMG_TYPE_PNG = 3;
    private const IMG_TYPE_WEBP = 18;
    
    // Allowed MIME types
    private const ALLOWED_MIME_TYPES = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];
    
    // Magic bytes for image type detection
    private const MAGIC_BYTES = [
        'gif' => ["GIF87a", "GIF89a"],
        'jpeg' => ["\xFF\xD8\xFF"],
        'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'webp' => ["RIFF", "WEBP"],
    ];
    
    // Limits
    private const MIN_DIMENSION = 1;
    private const MAX_DIMENSION = 10000;
    private const MIN_QUALITY = 0;
    private const MAX_QUALITY = 100;
    private const DEFAULT_QUALITY = 80;
    
    // Library
    private string $imageLibrary = 'gd2';
    
    // Paths
    private string $imagePath = '';
    private string $directory = '';
    private string $directoryOld = '';
    private ?string $directoryThumb = null;
    
    // File names
    private string $imageNameOld = '';
    private ?string $imageNameNew = null;
    
    // Dimensions
    private ?int $width = null;
    private ?int $height = null;
    private int $widthOriginal = 0;
    private int $heightOriginal = 0;
    private ?int $widthThumb = null;
    private ?int $heightThumb = null;
    
    // Image properties
    private ?string $imageTypeOrigin = null;
    private string $imageType = '';
    private string $mimeType = '';
    
    // Quality
    private int $quality = self::DEFAULT_QUALITY;
    
    // File info
    private string $urlImage;
    private string $fileName;
    
    // Crop position
    private int $x = 0;
    private int $y = 0;
    
    // Cache for image properties
    private ?array $cachedProperties = null;
    private ?string $cachedRealPath = null;
    
    // Error handling
    private ?string $error = null;
    
    /**
     * Constructor
     *
     * @param string $urlImage Path to the image file
     * @throws ImageNotFoundException If the image file does not exist
     */
    public function __construct(string $urlImage)
    {
        if (!file_exists($urlImage)) {
            throw new ImageNotFoundException($urlImage);
        }
        
        $this->urlImage = $urlImage;
        $this->fileName = $urlImage;
    }
    
    /**
     * Crop an image according to the specified dimensions
     *
     * @param bool $save If true, saves to directory; if false, displays the image
     * @return bool True on success
     * @throws ImageException If width or height are not set
     * @throws ImageProcessingException If image processing fails
     * @example
     * $image = new Image('path/to/image.jpg');
     * $image->set_width(200);
     * $image->set_height(200);
     * $image->crop(true);
     */
    public function crop(bool $save = true): bool
    {
        if ($this->width === null || $this->height === null) {
            $error = "Width and height must be set before cropping";
            $this->set_error($error);
            throw new ImageException($error);
        }
        
        $this->initialize(false);
        
        $this->widthOriginal = $this->width;
        $this->heightOriginal = $this->height;
        
        return $this->processImage($save);
    }
    
    /**
     * Resize an image according to the specified dimensions
     *
     * @param bool $save If true, saves to directory; if false, displays the image
     * @return bool True on success
     * @throws ImageException If width or height are not set
     * @throws ImageProcessingException If image processing fails
     * @example
     * $image = new Image('path/to/image.jpg');
     * $image->set_width(800);
     * $image->set_height(600);
     * $image->resize(true);
     */
    public function resize(bool $save = true): bool
    {
        if ($this->width === null || $this->height === null) {
            $error = "Width and height must be set before resizing";
            $this->set_error($error);
            throw new ImageException($error);
        }
        
        $this->initialize(true);
        
        return $this->processImage($save);
    }
    
    /**
     * Move image to specified directory
     *
     * @return bool True on success
     * @throws ImageNotFoundException If source image does not exist
     * @throws ImageProcessingException If copy operation fails
     */
    public function mover(): bool
    {
        if (!file_exists($this->urlImage)) {
            throw new ImageNotFoundException($this->urlImage);
        }
        
        $this->resolveDirectory();
        
        $this->fileName = "{$this->directory}/{$this->imageNameNew}";
        
        if (!copy($this->urlImage, $this->fileName)) {
            throw new ImageProcessingException('move', "Failed to copy image from {$this->urlImage} to {$this->fileName}");
        }
        
        if ($this->widthThumb !== null && $this->heightThumb !== null) {
            $this->createThumbnail();
        }
        
        return true;
    }
    
    /**
     * Delete an image file
     *
     * @param string $fileImage Path to the image file
     * @return bool True on success
     * @throws ImageNotFoundException If file does not exist
     */
    public function drop(string $fileImage): bool
    {
        if (!file_exists($fileImage)) {
            throw new ImageNotFoundException($fileImage);
        }
        
        return unlink($fileImage);
    }
    
    /**
     * Get image information
     *
     * @param bool $json If true, returns JSON string; if false, returns array
     * @return array|string|false Image information or false on failure
     */
    public function get_info(bool $json = false): array|string|false
    {
        if (!file_exists($this->fileName)) {
            return false;
        }
        
        $info = getimagesize($this->fileName);
        
        if (!$info || !is_array($info)) {
            return false;
        }
        
        $expType = explode("/", $info['mime']);
        $expName = explode("/", $this->fileName);
        
        $result = [
            'width' => $info[0],
            'height' => $info[1],
            'bits' => $info['bits'] ?? null,
            'mime' => $info['mime'],
            'type' => end($expType),
            'date' => filemtime($this->fileName),
            'name' => end($expName)
        ];
        
        return $json ? json_encode($result) : $result;
    }

    /**
     * Get the new image name
     *
     * @return string Image name
     */
    public function get_name(): string
    {
        return $this->imageNameNew ?? $this->imageNameOld;
    }
    
    /**
     * Get error message
     *
     * @return string|null Error message or null if no error
     */
    public function get_error(): ?string
    {
        return $this->error;
    }
    
    /**
     * Set image library
     *
     * @param string $imageLibrary Library name (gd2, gd, etc)
     * @return void
     **/
    public function set_image_library(string $imageLibrary): void
    {
        $this->imageLibrary = $imageLibrary;
    }
    
    /**
     * Set directory where image will be saved
     *
     * @param string $directory Directory path
     * @return void
     **/
    public function set_directory(string $directory): void
    {
        $this->directory = $this->sanitizePath(trim($directory, "/"));
    }
    
    /**
     * Set thumbnail directory
     *
     * @param string $directory Directory path for thumbnails
     * @return void
     **/
    public function set_directory_thumb(string $directory): void
    {
        $this->directoryThumb = $this->sanitizePath(trim($directory, "/"));
    }
    
    /**
     * Set image width
     *
     * @param int $width Width in pixels
     * @return void
     * @throws ImageException If width is out of valid range
     **/
    public function set_width(int $width): void
    {
        if ($width < self::MIN_DIMENSION || $width > self::MAX_DIMENSION) {
            $error = "Width must be between " . self::MIN_DIMENSION . " and " . self::MAX_DIMENSION;
            $this->set_error($error);
            throw new ImageException($error);
        }
        $this->width = $width;
    }
    
    /**
     * Set image height
     *
     * @param int $height Height in pixels
     * @return void
     * @throws ImageException If height is out of valid range
     **/
    public function set_height(int $height): void
    {
        if ($height < self::MIN_DIMENSION || $height > self::MAX_DIMENSION) {
            $error = "Height must be between " . self::MIN_DIMENSION . " and " . self::MAX_DIMENSION;
            $this->set_error($error);
            throw new ImageException($error);
        }
        $this->height = $height;
    }
    
    /**
     * Set thumbnail width
     *
     * @param int $width Width in pixels
     * @return void
     * @throws ImageException If width is out of valid range
     **/
    public function set_width_thumb(int $width): void
    {
        if ($width < self::MIN_DIMENSION || $width > self::MAX_DIMENSION) {
            $error = "Thumbnail width must be between " . self::MIN_DIMENSION . " and " . self::MAX_DIMENSION;
            $this->set_error($error);
            throw new ImageException($error);
        }
        $this->widthThumb = $width;
    }
    
    /**
     * Set thumbnail height
     *
     * @param int $height Height in pixels
     * @return void
     * @throws ImageException If height is out of valid range
     **/
    public function set_height_thumb(int $height): void
    {
        if ($height < self::MIN_DIMENSION || $height > self::MAX_DIMENSION) {
            $error = "Thumbnail height must be between " . self::MIN_DIMENSION . " and " . self::MAX_DIMENSION;
            $this->set_error($error);
            throw new ImageException($error);
        }
        $this->heightThumb = $height;
    }
    
    /**
     * Set image quality (for JPEG and WebP)
     *
     * @param int $quality Quality from 0 to 100
     * @return void
     * @throws ImageException If quality is out of valid range
     **/
    public function set_quality(int $quality): void
    {
        if ($quality < self::MIN_QUALITY || $quality > self::MAX_QUALITY) {
            $error = "Quality must be between " . self::MIN_QUALITY . " and " . self::MAX_QUALITY;
            $this->set_error($error);
            throw new ImageException($error);
        }
        $this->quality = $quality;
    }
    
    /**
     * Set X position for crop
     *
     * @param int $x X coordinate
     * @return void
     */
    public function set_x(int $x): void
    {
        $this->x = $x;
    }
    
    /**
     * Set Y position for crop
     *
     * @param int $y Y coordinate
     * @return void
     */
    public function set_y(int $y): void
    {
        $this->y = $y;
    }
    
    /**
     * Set new image name
     *
     * @param string $imageName New image name
     * @return void
     */
    public function set_name(string $imageName): void
    {
        $this->imageNameNew = $imageName;
    }    
    
    /**
     * Initialize image processing
     *
     * @param bool $resize If true, calculates dimensions for resize
     * @return void
     * @throws ImageException If image properties cannot be loaded
     */
    private function initialize(bool $resize = true): void
    {
        $this->loadImageProperties();
        $this->resolveDirectory();
        
        if ($resize) {
            $this->calculateDimensions();
        }
    }
    
    /**
     * Process the image (resize/crop and save or display)
     *
     * @param bool $save If true, saves image; if false, displays it
     * @return bool True on success
     * @throws ImageProcessingException If processing fails
     */
    private function processImage(bool $save = true): bool
    {
        try {
            $src = $this->createImageResource();
            
            if (!$src) {
                $error = "Failed to create image resource";
                $this->set_error($error);
                throw new ImageProcessingException('createResource', $error);
            }
            $createFunction = ($this->imageLibrary === 'gd2' && function_exists('imagecreatetruecolor'))
                ? 'imagecreatetruecolor'
                : 'imagecreate';
            
            $copyFunction = ($this->imageLibrary === 'gd2' && function_exists('imagecopyresampled'))
                ? 'imagecopyresampled'
                : 'imagecopyresized';
            
            $newWidth = $this->width;
            $newHeight = $this->height;
            
            $img = $createFunction($newWidth, $newHeight);
            
            if (!$img) {
                $error = "Failed to create image canvas";
                $this->set_error($error);
                throw new ImageProcessingException('createCanvas', $error);
            }
            
            // Handle transparency for PNG
            if ($this->imageType === self::IMAGE_TYPE_PNG) {
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }
            
            // Handle transparency for WebP
            if ($this->imageType === self::IMAGE_TYPE_WEBP) {
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }
            
            $copyFunction($img, $src, 0, 0, $this->x, $this->y, $newWidth, $newHeight, $this->widthOriginal, $this->heightOriginal);
            
            if ($save) {
                $this->saveImageResource($img);
                
                if ($this->widthThumb !== null && $this->heightThumb !== null) {
                    $this->createThumbnail();
                }
            } else {
                $this->displayImage($img);
            }
            
            $this->destroyImageResource($img);
            $this->destroyImageResource($src);
            
            return true;
        } catch (\Throwable $e) {
            if (isset($src)) {
                $this->destroyImageResource($src);
            }
            if (isset($img)) {
                $this->destroyImageResource($img);
            }
            $this->set_error($e->getMessage());
            throw new ImageProcessingException('processImage', $e->getMessage(), $e);
        }
    }
    
    /**
     * Create thumbnail using current instance (optimized)
     *
     * @return bool True on success
     * @throws ImageProcessingException If thumbnail creation fails
     */
    private function createThumbnail(): bool
    {
        $originalWidth = $this->width;
        $originalHeight = $this->height;
        $originalDirectory = $this->directory;
        $originalName = $this->imageNameNew;
        
        try {
            $thumbDirectory = $this->directoryThumb ?? "{$this->directory}/thumbs";
            $this->set_directory($thumbDirectory);
            $this->set_width($this->widthThumb);
            $this->set_height($this->heightThumb);
            
            $result = $this->resize(true);
            
            // Restore original values
            $this->width = $originalWidth;
            $this->height = $originalHeight;
            $this->directory = $originalDirectory;
            $this->imageNameNew = $originalName;
            
            return $result;
        } catch (\Throwable $e) {
            // Restore original values on error
            $this->width = $originalWidth;
            $this->height = $originalHeight;
            $this->directory = $originalDirectory;
            $this->imageNameNew = $originalName;
            
            throw new ImageProcessingException('createThumbnail', $e->getMessage(), $e);
        }
    }
    
    /**
     * Create image resource from file using GD library
     *
     * @return \GdImage|resource|false GD image resource or false on failure
     * @throws ImageInvalidTypeException If image type is not supported
     * @throws ImageProcessingException If resource creation fails
     */
    private function createImageResource()
    {
        $imageFunctions = [
            self::IMAGE_TYPE_JPEG => 'imagecreatefromjpeg',
            self::IMAGE_TYPE_GIF => 'imagecreatefromgif',
            self::IMAGE_TYPE_PNG => 'imagecreatefrompng',
            self::IMAGE_TYPE_WEBP => 'imagecreatefromwebp',
        ];
        
        if (!isset($imageFunctions[$this->imageType])) {
            $error = "Invalid image type: {$this->imageType}";
            $this->set_error($error);
            throw new ImageInvalidTypeException($this->imageType, array_keys($imageFunctions));
        }
        
        $function = $imageFunctions[$this->imageType];
        
        if (!function_exists($function)) {
            $error = "GD function {$function} is not available. Check PHP configuration.";
            $this->set_error($error);
            throw new ImageProcessingException('createResource', $error);
        }
        
        $resource = @$function($this->imagePath);
        
        if (!$resource) {
            $error = "Failed to create image resource from {$this->imagePath}";
            $this->set_error($error);
            throw new ImageProcessingException('createResource', $error);
        }
        
        return $resource;
    }
    
    /**
     * Display image in browser
     *
     * @param \GdImage|resource $srcImg GD image resource
     * @return void
     * @throws ImageProcessingException If display fails
     */
    private function displayImage($srcImg): void
    {
        header("Content-Disposition: filename={$this->urlImage};");
        header("Content-Type: {$this->mimeType}");
        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        
        $displayFunctions = [
            self::IMAGE_TYPE_GIF => 'imagegif',
            self::IMAGE_TYPE_JPEG => 'imagejpeg',
            self::IMAGE_TYPE_PNG => 'imagepng',
            self::IMAGE_TYPE_WEBP => 'imagewebp',
        ];
        
        if (!isset($displayFunctions[$this->imageType])) {
            $error = "Unsupported image type for display: {$this->imageType}";
            $this->set_error($error);
            throw new ImageProcessingException('display', $error);
        }
        
        $function = $displayFunctions[$this->imageType];
        
        if ($this->imageType === self::IMAGE_TYPE_JPEG || $this->imageType === self::IMAGE_TYPE_WEBP) {
            $function($srcImg, null, $this->quality);
        } else {
            $function($srcImg);
        }
    }
    
    /**
     * Save image resource to file
     *
     * @param \GdImage|resource $srcImg GD image resource
     * @return void
     * @throws ImageProcessingException If save operation fails
     */
    private function saveImageResource($srcImg): void
    {
        $directory = rtrim($this->directory, '/');
        $fileImg = "{$directory}/{$this->imageNameNew}";
        
        // Sanitize and validate path
        $fileImg = $this->sanitizePath($fileImg);
        
        $saveFunctions = [
            self::IMAGE_TYPE_GIF => 'imagegif',
            self::IMAGE_TYPE_JPEG => 'imagejpeg',
            self::IMAGE_TYPE_PNG => 'imagepng',
            self::IMAGE_TYPE_WEBP => 'imagewebp',
        ];
        
        if (!isset($saveFunctions[$this->imageType])) {
            $error = "Invalid image type for save: {$this->imageType}";
            $this->set_error($error);
            throw new ImageInvalidTypeException($this->imageType, array_keys($saveFunctions));
        }
        
        $function = $saveFunctions[$this->imageType];
        
        if (!function_exists($function)) {
            $error = "GD function {$function} is not available. Check PHP configuration.";
            $this->set_error($error);
            throw new ImageProcessingException('save', $error);
        }
        
        $success = false;
        
        if ($this->imageType === self::IMAGE_TYPE_JPEG || $this->imageType === self::IMAGE_TYPE_WEBP) {
            $success = $function($srcImg, $fileImg, $this->quality);
        } else {
            $success = $function($srcImg, $fileImg);
        }
        
        if (!$success) {
            $error = "Failed to save image to {$fileImg}";
            $this->set_error($error);
            throw new ImageProcessingException('save', $error);
        }
        
        $this->fileName = $fileImg;
    }
    
    /**
     * Load and validate image properties with caching
     *
     * @return void
     * @throws ImageNotFoundException If image file does not exist
     * @throws ImageInvalidTypeException If image type is invalid or not supported
     */
    private function loadImageProperties(): void
    {
        if ($this->cachedProperties !== null) {
            $this->widthOriginal = $this->cachedProperties['width'];
            $this->heightOriginal = $this->cachedProperties['height'];
            $this->mimeType = $this->cachedProperties['mime'];
            $this->imageType = $this->cachedProperties['type'];
            $this->imageTypeOrigin = $this->cachedProperties['typeOrigin'];
            $this->imagePath = $this->cachedRealPath;
            return;
        }
        
        if (!file_exists($this->urlImage)) {
            throw new ImageNotFoundException($this->urlImage);
        }
        
        $info = getimagesize($this->urlImage);
        
        if ($info === false || !is_array($info)) {
            throw new ImageInvalidTypeException('unknown', [], new \Exception("getimagesize() failed"));
        }
        
        // Validate MIME type
        if (!in_array($info['mime'], self::ALLOWED_MIME_TYPES, true)) {
            throw new ImageInvalidTypeException($info['mime'], self::ALLOWED_MIME_TYPES);
        }
        
        // Validate magic bytes
        $this->validateMagicBytes($this->urlImage, $info['mime']);
        
        $typeMap = [
            self::IMG_TYPE_GIF => self::IMAGE_TYPE_GIF,
            self::IMG_TYPE_JPEG => self::IMAGE_TYPE_JPEG,
            self::IMG_TYPE_PNG => self::IMAGE_TYPE_PNG,
            self::IMG_TYPE_WEBP => self::IMAGE_TYPE_WEBP,
        ];
        
        $this->widthOriginal = $info[0];
        $this->heightOriginal = $info[1];
        $this->mimeType = $info['mime'];
        $this->imageType = $typeMap[$info[2]] ?? self::IMAGE_TYPE_JPEG;
        
        $arr = explode('.', $this->urlImage);
        $this->imageTypeOrigin = end($arr);
        
        // Get real path and cache it
        $realPath = realpath($this->urlImage);
        if ($realPath === false) {
            throw new ImageNotFoundException($this->urlImage);
        }
        
        $this->imagePath = str_replace("\\", "/", $realPath);
        
        if (strpos($this->imagePath, "home") === 0) {
            $this->imagePath = "/{$this->imagePath}";
        }
        
        // Cache properties
        $this->cachedProperties = [
            'width' => $this->widthOriginal,
            'height' => $this->heightOriginal,
            'mime' => $this->mimeType,
            'type' => $this->imageType,
            'typeOrigin' => $this->imageTypeOrigin,
        ];
        $this->cachedRealPath = $this->imagePath;
    }
    
    /**
     * Validate magic bytes to ensure file type matches extension
     *
     * @param string $filePath Path to the file
     * @param string $mimeType Detected MIME type
     * @return void
     * @throws ImageInvalidTypeException If magic bytes don't match expected type
     */
    private function validateMagicBytes(string $filePath, string $mimeType): void
    {
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return; // Skip validation if file cannot be opened
        }
        
        $bytes = fread($handle, 12);
        fclose($handle);
        
        if ($bytes === false) {
            return; // Skip validation if read fails
        }
        
        $expectedType = null;
        foreach (self::MAGIC_BYTES as $type => $signatures) {
            foreach ($signatures as $signature) {
                if (strpos($bytes, $signature) === 0) {
                    $expectedType = $type;
                    break 2;
                }
            }
        }
        
        // Map MIME type to expected type
        $mimeToType = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        
        $expectedFromMime = $mimeToType[$mimeType] ?? null;
        
        if ($expectedType !== null && $expectedFromMime !== null && $expectedType !== $expectedFromMime) {
            throw new ImageInvalidTypeException($mimeType, [], new \Exception("File signature does not match MIME type. Expected {$expectedFromMime}, found {$expectedType}"));
        }
    }
    
    /**
     * Resolve and sanitize directory path
     *
     * @return void
     * @throws ImageException If directory path is invalid
     */
    private function resolveDirectory(): void
    {
        if (!is_string($this->urlImage) || empty($this->urlImage)) {
            throw new ImageException("Invalid image URL");
        }
        
        $pathInfo = pathinfo($this->urlImage);
        
        $this->imageNameOld = $pathInfo['basename'];
        $this->imageNameNew = $this->imageNameNew ?? $this->imageNameOld;
        $this->directoryOld = $pathInfo['dirname'];
        
        if (empty($this->directory)) {
            $this->directory = $this->directoryOld;
        }
        
        $this->directory = $this->sanitizePath($this->directory);
        $this->verifyDirectory();
    }
    
    /**
     * Calculate dimensions maintaining aspect ratio
     *
     * @return void
     */
    private function calculateDimensions(): void
    {
        if ($this->width === null || $this->height === null) {
            return;
        }
        
        $newWidth = ceil(($this->widthOriginal * $this->height) / $this->heightOriginal);
        $newHeight = ceil(($this->width * $this->heightOriginal) / $this->widthOriginal);
        
        $aspectRatioDifference = ($this->heightOriginal / $this->widthOriginal) - ($this->height / $this->width);
        
        $resizeDimension = ($aspectRatioDifference < 0) ? 'height' : 'width';
        
        if ($this->width !== $newWidth && $this->height !== $newHeight) {
            if ($resizeDimension === 'height') {
                $this->width = $newWidth;
            } else {
                $this->height = $newHeight;
            }
        }
    }
    
    /**
     * Verify and create directory structure if needed
     *
     * @return void
     */
    private function verifyDirectory(): void
    {
        $directory = "";
        $array = explode("/", $this->directory);
        
        foreach ($array as $dir) {
            if (empty($dir)) {
                continue;
            }
            
            $directory .= "{$dir}/";
            
            if (!file_exists($directory)) {
                $file = $this->getFileInstance();
                
                if ($file->create_directory($directory)) {
                    $file->copy_file("system/index.html", "{$directory}index.html");
                }
            }
        }
    }
    
    /**
     * Sanitize file path to prevent path traversal attacks
     *
     * @param string $path Path to sanitize
     * @return string Sanitized path
     */
    private function sanitizePath(string $path): string
    {
        // Remove null bytes
        $path = str_replace("\0", '', $path);
        
        // Normalize directory separators
        $path = str_replace('\\', '/', $path);
        
        // Remove path traversal sequences
        $path = preg_replace('#\.\./#', '', $path);
        $path = preg_replace('#\.\.\\\#', '', $path);
        
        // Remove multiple slashes
        $path = preg_replace('#/+#', '/', $path);
        
        return $path;
    }
    
    /**
     * Destroy image resource safely (compatible with PHP 7.4 and 8+)
     *
     * @param \GdImage|resource|false|null $resource GD image resource or GdImage object
     * @return void
     */
    private function destroyImageResource($resource): void
    {
        if (!$resource) {
            return;
        }
        
        // PHP 8.0+ returns GdImage objects, which are automatically destroyed when out of scope
        // PHP 7.4 and below return resources, which need explicit destruction
        if (is_resource($resource)) {
            // For PHP 7.4 and below: resources need explicit destruction
            // Suppress deprecated warning for PHP 8+ compatibility
            $destroyFunction = 'imagedestroy';
            if (function_exists($destroyFunction)) {
                @$destroyFunction($resource);
            }
        }
        // For GdImage objects (PHP 8+), they are automatically garbage collected
        // when they go out of scope, so no explicit destruction is needed
        // This avoids the deprecated warning in PHP 8+
    }
    
    /**
     * Get File instance
     *
     * @return File File instance
     */
    private function getFileInstance(): File
    {
        return new File();
    }
    
    /**
     * Set error message
     *
     * @param string $error Error message
     * @return void
     */
    private function set_error(string $error): void
    {
        $this->error = $error;
    }
    
}
