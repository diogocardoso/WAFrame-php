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
        $file_name = $this->sanitize_file_name($file_name);

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
            $directory = str_replace('\\', '/', $this->diretorio);

            $is_absolute = (substr($directory, 0, 1) === '/')
                || (preg_match('/^[A-Za-z]:\//', $directory) === 1);

            $directory = trim($directory, '/');
            $parts = ($directory === '') ? [] : explode('/', $directory);

            // Prefixo inicial correto para path absoluto (Linux ou Windows)
            if ($is_absolute) {
                if (!empty($parts) && preg_match('/^[A-Za-z]:$/', $parts[0]) === 1) {
                    // Ex.: C:/...
                    $current = array_shift($parts) . '/';
                } else {
                    // Ex.: /application/...
                    $current = '/';
                }
            } else {
                $current = '';
            }

            foreach ($parts as $part) {
                if ($part === '' || $part === '.') {
                    continue;
                }

                $current .= $part . '/';

                if (file_exists($current)) {
                    if (!is_dir($current)) {
                        throw new \Exception("Erro: Caminho existe mas não é diretório: {$current}");
                    }

                    if (!is_writable($current)) {
                        throw new \Exception("Erro: Diretório sem permissão de escrita: {$current}");
                    }

                    continue;
                }

                // Verifica permissão no diretório pai
                $parent_dir = dirname(rtrim($current, '/'));
                if ($parent_dir === '') {
                    $parent_dir = '.';
                }

                if (!is_writable($parent_dir) && $parent_dir !== '.') {
                    throw new \Exception("Erro: Sem permissão de escrita no diretório pai: {$parent_dir}");
                }

                $FILE = $this->WA_file();
                $result = $FILE->create_directory($current);

                if (!$result) {
                    throw new \Exception("Erro: Falha ao criar o diretório: {$current}");
                }

                if (!file_exists($current)) {
                    throw new \Exception("Erro: Diretório não foi criado com sucesso: {$current}");
                }

                if (!is_writable($current)) {
                    throw new \Exception("Erro: Diretório criado não tem permissão de escrita: {$current}");
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

    private function sanitize_file_name($file_name){
        $value = trim((string) $file_name);

        if ($value === '') {
            return 'cache';
        }

        // Evita separadores de caminho e caracteres inválidos para nome de arquivo.
        $value = str_replace(['/', '\\', "\0"], '_', $value);
        $value = preg_replace('/[^a-zA-Z0-9._@-]/', '_', $value);

        return $value;
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