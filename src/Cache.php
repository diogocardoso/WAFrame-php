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

    public function save(array $cache){
        try{
            $this->verify_directory();

            $Util = $this->WA_file();
                    
            $json = json_encode($cache);
            
            if($json === false){
                throw new \Exception("Erro: Falha ao codificar dados para JSON");
            }

            $result = $Util->write_file($this->dir_file_name(), $json);
            
            if(!$result){
                throw new \Exception("Erro: Falha ao escrever arquivo de cache: " . $this->dir_file_name());
            }
            
            return $result;
            
        }catch(\Exception $e){
            // Log detalhado do erro
            error_log("Cache::save() - " . $e->getMessage());
            error_log("Cache::save() - Diretório: " . $this->get_diretorio());
            error_log("Cache::save() - Arquivo: " . $this->get_file_name());
            
            // Retorna false em vez de fazer exit, permitindo tratamento pelo código que chama
            return false;
        }
    }

    public function get_json(){
       $FILE = $this->WA_file();

       $file_name = $this->dir_file_name();
       
       return $FILE->read_file($file_name); 
    }
    
    public function get($item=NULL)
    {
       $FILE = $this->WA_file();

       $file_name = $this->dir_file_name();
       
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

    public function delete(){
        $file = $this->dir_file_name();

        if(file_exists($file)){
            unlink($file);

            return TRUE;
        }

        return FALSE;
    }
    
    public function get_info(){
        $FILE = $this->WA_file();
        
        return $FILE->get_file_info($this->dir_file_name());
    }
    
    /**
     * Verifica se o diretório de cache está acessível e com permissões corretas
     * @return array Array com status da verificação
     */
    public function check_directory_permissions(){
        $result = [
            'accessible' => false,
            'writable' => false,
            'errors' => []
        ];
        
        try {
            $diretorio = $this->get_diretorio();
            
            // Verifica se o diretório existe
            if(!file_exists($diretorio)){
                $result['errors'][] = "Diretório não existe: {$diretorio}";
                return $result;
            }
            
            $result['accessible'] = true;
            
            // Verifica se é um diretório
            if(!is_dir($diretorio)){
                $result['errors'][] = "Caminho não é um diretório: {$diretorio}";
                return $result;
            }
            
            // Verifica permissão de escrita
            if(!is_writable($diretorio)){
                $result['errors'][] = "Sem permissão de escrita no diretório: {$diretorio}";
                return $result;
            }
            
            $result['writable'] = true;
            
        } catch (\Exception $e) {
            $result['errors'][] = "Erro ao verificar diretório: " . $e->getMessage();
        }
        
        return $result;
    }

    protected function dir_file_name(){
        return "{$this->get_diretorio()}{$this->get_file_name()}";
    }    

    private function verify_directory(){
        try {
            $array = explode("/", $this->diretorio);
            $diretorio = "";
            
            foreach ($array as $dir){
                if(empty($dir)) continue; // Pula diretórios vazios
                
                $diretorio.= "{$dir}/";

                if(!file_exists($diretorio)){
                    // Verifica se o diretório pai tem permissão de escrita
                    $parent_dir = dirname($diretorio);
                    if(!is_writable($parent_dir) && $parent_dir !== '.'){
                        throw new \Exception("Erro: Sem permissão de escrita no diretório pai: {$parent_dir}");
                    }
                    
                    $FILE = $this->WA_file();
                    $result = $FILE->create_directory($diretorio);
                    
                    if(!$result){
                        throw new \Exception("Erro: Falha ao criar o diretório: {$diretorio}");
                    }
                    
                    // Verifica se o diretório foi realmente criado
                    if(!file_exists($diretorio)){
                        throw new \Exception("Erro: Diretório não foi criado com sucesso: {$diretorio}");
                    }
                    
                    // Verifica se o diretório criado tem permissão de escrita
                    if(!is_writable($diretorio)){
                        throw new \Exception("Erro: Diretório criado não tem permissão de escrita: {$diretorio}");
                    }
                }
            }

            return TRUE;
            
        } catch (\Exception $e) {
            // Log do erro para debug
            error_log("Cache::verify_directory() - " . $e->getMessage());
            
            // Re-lança a exceção para ser capturada pelo método save()
            throw new \Exception("Erro na verificação/criação do diretório: " . $e->getMessage());
        }
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