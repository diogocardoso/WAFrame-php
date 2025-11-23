<?php

namespace WAFrame\Factory;

use WAFrame\File;

class WACreate{
    private static $path='app/';

    public static function show($dir, $extends=NULL){
        $arr = explode('/', $dir);

        $plugin = $arr[0];
        $type = $arr[1];

        $name = (array_key_exists(2, $arr)) ? $arr[2] : false;
        
        if(method_exists('WACreate',$type)){
            static::$type($plugin, $name, $extends);
        }else{
            static::factory($type, $plugin, $name, $extends);
        }
    }

    public static function all($dir){
        $arr = explode('/', $dir);

        $plugin = $arr[0];
        $object = $arr[1];

        $types = ['struct','repository','validate','service','entity','controller'];

        foreach ($types as $type) {
            static::factory($type, $plugin, $object);
        }
    }

    private static function verify_directory($plugin, $type, File $File){             
        $tmp = static::$path;

        if($plugin=='dro' || $plugin=='drone'){
            $tmp.= "Drone/";
        }else{
            $tmp.= "Domains/".$plugin."/";
        }

        $dir = static::folder_type($type);

        if(!file_exists("{$tmp}{$dir}")){
            $arr = explode('/',$dir);

            foreach ($arr as $folder) {
                $tmp.= $folder."/";
                if(!file_exists($tmp)){
                    $File->create_directory($tmp);
                }
            }

            return $tmp;
        }

        return "{$tmp}{$dir}/";
    }

    private static function factory($type, $plugin, $object, $extends=NULL){        
        $File = self::WA_file();
        $dir = static::verify_directory($plugin, $type, $File);

        $templateName = (isset($extends)) ? static::extends($extends, $type) : $type;

        $template = $File->read_file("C:/Applications/WAFrame-php/factory/templates/php/{$templateName}");

        $className = static::className($object, $type);

        static::create($plugin, $object, $className, $template, $dir);
    }

    private static function create($plugin, $object, $className, $template, $dir){
        $de = ['@CLASS_NAME','@PLUGIN','@OBJECT','@OBJ_FIRST_UPPER','@DATE'];
        $para = [$className, $plugin, $object, ucfirst($object), date('d/m/Y')];

        $template = str_replace($de, $para, $template);

        if(self::WA_file()->write_file("{$dir}{$className}.php","<?php\n{$template}")){
            echo "-- Success: {$className} created in {$dir}\n";
        }else{
            echo "-- Error: When creating {$className} in {$dir}\n";
        }
    }

    private static function className($name, $type){
        $class = "";

        if(isset($name) && $name){
            $class.= ucfirst($name);
        }
        if($type && !in_array($type, ['system','entity'])){
            $class.= ucfirst($type);
        }

        return $class;
    }

    private static function folder_type($type){
        $directories = [
            'config'=>'config',
            'controller'=>'Controllers',
            'entity'=>'Entities',
            'interface'=>'Interfaces',
            'repository'=>'Repositories',
            'service'=>'Services',
            'struct'=>'Structs',
            'validate'=>'Validates'
        ];

        return (isset($directories[$type])) ? $directories[$type] : ucfirst($type).'s';
    }

    private static function extends($name, $type){
        switch ($type) {
            case 'entit':
                return $name;
                break;
            case 'controller':                
                return "extend/{$name}";
                break;
            case 'model':
                return "extend/{$name}";
                break;
            default:
                return 'basic';
                break;
        }
    }

    /* Objects */

    private static function WA_file(){
        return new File();
    }
}