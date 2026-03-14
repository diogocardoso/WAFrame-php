<?php

namespace WAFrame\Exceptions;

/**
 * Exception thrown when an image file is not found
 *
 * @author DiogoCardoso
 * @version 2.0
 * @copyright (c) 2025, webavance.com.br
 */
class ImageNotFoundException extends ImageException
{
    public function __construct(string $path, ?\Throwable $previous = null)
    {
        parent::__construct("Image file not found: {$path}", 0, $previous);
    }
}

