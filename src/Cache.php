<?php

namespace WAFrame;

use WAFrame\File;

class Cache 
{    
    private $diretorio;
    private $file_name;
    private $formato;

    public function __construct($diretorio, $file_name)
    {        
        $diretorio = ($diretorio!="") ? rtrim($diretorio,"/") . "/" : $diretorio;

        $this->set_formato(".json");
        $this->set_diretorio($diretorio);

        $file_name = str_replace($this->get_formato(), "", $file_name);

        $this->set_file_name("{$file_name}{$this->get_formato()}");
    }

    public function create(array $cache, $create_directory=TRUE)
    {
        $UTIL = $this->WA_file();
                
        if(!file_exists($this->get_diretorio()) && $create_directory)
        {
            if($UTIL->create_directory($this->get_diretorio()))
            {
                if(file_exists("temp/index.html")){
                    $UTIL->copy_file("temp/index.html", "{$this->get_diretorio()}index.html");
                }
            }
        }

        $json = json_encode($cache);

        return $UTIL->write_file($this->get_dir_file_name(), $json);
    }
    
    public function get_json(){
       $FILE = $this->WA_file();

       $file_name = $this->get_dir_file_name();
       
       return $FILE->read_file($file_name); 
    }
    
    public function get_dir_file_name()
    {        
        return "{$this->get_diretorio()}{$this->get_file_name()}";
    }
    
    public function get($item=NULL)
    {
       $FILE = $this->WA_file();

       $file_name = $this->get_dir_file_name();
       
       $temp  = $FILE->read_file($file_name);

       if($temp)
       {
            $cache = json_decode($temp, TRUE);

            if(isset($item))
            {
                return (isset($cache[$item])) ? $cache[$item] : FALSE;
            }
            else
            {
                return $cache;
            }
        }
        else
        {
            return FALSE;
        }
    }

    public function update(array $cache) 
    {
        $tmp  = FALSE;
        $json = $this->get();

        if(is_array($json)){
            $tmp = $this->create(array_replace_recursive($json, $cache));
        }

        return $tmp;
    }

    public function delete(){
        $tmp = FALSE;
        $file = $this->get_dir_file_name();

        if(file_exists($file))
        {
            unlink($file);

            $tmp = TRUE;
        }

        return $tmp;
    }
    
    public function get_info(){
        $FILE = $this->WA_file();
        
        return $FILE->get_file_info($this->get_dir_file_name());
    }

    private function verifica_diretorio(){
        $diretorio = "";

        $array = explode("/", $this->diretorio);

        foreach ($array as $dir){
            $diretorio.= "{$dir}/";

            if(!file_exists($diretorio)){
                $FILE = $this->WA_file();

                if( $FILE->create_directory($diretorio) ){
                    $FILE->copy_file("system/index.html", "{$diretorio}index.html");
                }
            }
        }

        return TRUE;
    }

    /*  Get & Set   */

    public function get_file_name() {
        return $this->file_name;
    }

    public function get_diretorio() {
        return $this->diretorio;
    }

    public function get_formato() {
        return $this->formato;
    }

    public function set_file_name($file_name) {
        $this->file_name = $file_name;
    }

    public function set_diretorio($diretorio) {
        $this->diretorio = $diretorio;
    }

    public function set_formato($formato) {
        $this->formato = $formato;
    }
    
    /*  Objects */
    
    private function WA_file() {
        return new File();
    }
}