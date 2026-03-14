<?php

namespace WAFrame\Exceptions;

/**
 * Exception thrown when image processing fails
 *
 * @author DiogoCardoso
 * @version 2.0
 * @copyright (c) 2025, webavance.com.br
 */
class ImageProcessingException extends ImageException
{
    public function __construct(string $operation, string $message = '', ?\Throwable $previous = null)
    {
        $fullMessage = "Image processing failed during {$operation}";
        if ($message) {
            $fullMessage .= ": {$message}";
        }
        parent::__construct($fullMessage, 0, $previous);
    }
}

