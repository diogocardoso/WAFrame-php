<?php

namespace WAFrame;

use WAFrame\Cache;
use WAFrame\File;
use WAFrame\Helper;
use WAFrame\Validate;
use WAFrame\ValidateBR;
use WAFrame\Image;
use WAFrame\Upload;

class WA
{
    public static function version()
    {
        return "1.0.0";
    }

    public static function cache($diretorio, $file_name)
    {
        return new Cache($diretorio, $file_name);
    }

    public static function file()
    {
        return new File();
    }
    
    public static function helper()
    {
        return new Helper();
    }

    public static function validate()
    {
        return new Validate();
    }
    
    public static function validateBR()
    {
        return new ValidateBR();
    }

    public static function image($urlImage)
    {
        return new Image($urlImage);
    }

    public static function upload($encript_name = TRUE)
    {
        return new Upload($encript_name);
    }
}
