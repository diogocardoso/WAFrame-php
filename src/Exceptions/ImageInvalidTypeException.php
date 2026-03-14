<?php

namespace WAFrame\Exceptions;

/**
 * Exception thrown when an invalid image type is provided
 *
 * @author DiogoCardoso
 * @version 2.0
 * @copyright (c) 2025, webavance.com.br
 */
class ImageInvalidTypeException extends ImageException
{
    public function __construct(string $type, array $allowedTypes = [], ?\Throwable $previous = null)
    {
        $message = "Invalid image type: {$type}";
        if (!empty($allowedTypes)) {
            $message .= ". Allowed types: " . implode(', ', $allowedTypes);
        }
        parent::__construct($message, 0, $previous);
    }
}

