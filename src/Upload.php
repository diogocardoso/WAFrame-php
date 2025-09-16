<?php

namespace WAFrame;

use Exception;
use WAFrame\File;
use WAFrame\Helper;
/**
 * Classe para gerenciamento de upload de arquivos
 *
 * @version 1.1
 * @author Di0g0 CaRd0s0
 * @link http://www.webadvance.com.br
 */
class Upload 
{
    private $directory;
    private $file_name;
    private $file_types;
    private $file_size;

    private $max_file_size;
    private $encript_name;
    private $type;
    private $name = FALSE;    
    
    private $error;
    private $infoFile;

    /**
     * Construtor da classe
     * 
     * @param bool $encript_name Define se o nome do arquivo será criptografado
     */
    public function __construct($encript_name = TRUE) {
        $this->encript_name = $encript_name;
    }

    public function load(array $values) {
        $this->file_name = $values['file_name'];
        $this->file_types = $values['file_types'];
        $this->max_file_size = $values['max_file_size'];
    }
     
    /**
     * Retorna o nome do arquivo
     * 
     * @return string|bool Nome do arquivo ou FALSE
     */
    public function get_file_name() {
        return $this->name;
    }    
    /**
     * Processa o upload do arquivo
     * 
     * @return bool TRUE se o upload foi bem sucedido, FALSE caso contrário
     */
    public function show() {        
        try {            
            if(!$this->validade()){
                return FALSE;
            }

            // Prepara o nome do arquivo
            $this->prepend_file_name();
            
            // Define o caminho de destino
            $destination = "{$this->directory}/{$this->name}";
            
            // Tenta mover o arquivo
            if (!move_uploaded_file($_FILES[$this->file_name]['tmp_name'], $destination)) {
                $error = error_get_last();
                throw new Exception("Erro ao mover o arquivo para o diretório de destino: " . ($error ? $error['message'] : 'Erro desconhecido'));
            }

            // Verifica se o arquivo foi realmente movido
            if (!file_exists($destination)) {
                throw new Exception("O arquivo não foi movido corretamente para o destino.");
            }

            // Prepara as informações do arquivo
            $infoFile = [
                "old"       => $_FILES[$this->file_name]['name'],
                "name"      => $this->name,
                "size"      => $this->WA_helper()->format_size($this->file_size),
                "type"      => $this->type,
                "path"      => $destination,
                "mime_type" => $_FILES[$this->file_name]['type'],
                "upload_date" => date('Y-m-d H:i:s')
            ];

            $this->set_info($infoFile);

            return TRUE;
            
        } catch (Exception $e) {
            $this->set_error($e->getMessage());
            return FALSE;
        }
    }

    /**
     * Define o diretório de upload
     * 
     * @param string $directory Caminho do diretório
     */
    public function set_directory($directory) {
        $this->directory = trim($directory, "/");
    }

    /**
     * Define o nome do arquivo
     * 
     * @param string $file_name Nome do arquivo
     */
    public function set_file_name($file_name) {
        $this->name = $file_name;
    }

    /**
     * Define os tipos de arquivo permitidos
     * 
     * @param string $val Tipos de arquivo separados por |
     */
    public function set_file_types($val) {
        $this->file_types = $val;
    }

    /**
     * Define o tamanho máximo do arquivo
     * 
     * @param string|int $val Tamanho máximo (ex: "10MB", 10485760)
     */
    public function set_max_file_size($val) {
        $this->max_file_size = $val;
    }
    /**
     * Valida os dados do upload
     * 
     * @return bool TRUE se os dados são válidos, FALSE caso contrário
     */
    private function validade(){
        // Verifica se o diretório existe
        if (!$this->check_directory()) {
            $this->set_error("Erro ao verificar/criar diretório de upload.");
            return FALSE;
        }

        // Verifica se o arquivo foi enviado
        if (!isset($_FILES[$this->file_name])) {
            $this->set_error("Nenhum arquivo foi enviado.");
            return FALSE;
        }

        // Verifica se houve erro no upload
        if ($_FILES[$this->file_name]['error'] !== UPLOAD_ERR_OK) {
            $this->set_error($this->error_message($_FILES[$this->file_name]['error']));
            return FALSE;
        }

        // Verifica se o arquivo temporário existe
        if (!file_exists($_FILES[$this->file_name]['tmp_name'])) {
            $this->set_error("Arquivo temporário não encontrado.");
            return FALSE;
        }

        // Verifica o tamanho do arquivo
        $this->file_size = $_FILES[$this->file_name]['size'];
        if ($this->file_size === 0) {
            $this->set_error("O arquivo está vazio.");
            return FALSE;
        }

        // Verifica o tipo do arquivo
        if (!$this->validate_file_types()) {
            $this->set_error("Tipo de arquivo inválido.");
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Prepara o nome do arquivo para upload
     */
    private function prepend_file_name() {
        $fileInfo = pathinfo($_FILES[$this->file_name]['name']);
        $this->type = strtolower($fileInfo['extension']);
        
        if ($this->encript_name) {
            $this->name = $this->WA_Helper()->create_key() . '.' . $this->type;
            return;
        }
        
        if ($this->name) {
            $this->name = trim($this->name, '.' . $this->type) . '.' . $this->type;
        } else {
            $this->name = $_FILES[$this->file_name]['name'];
        }
    }    
    /**
     * Obtém a mensagem de erro do upload
     * 
     * @param int $error_code Código do erro
     * @return string Mensagem de erro
     */
    private function error_message($error_code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => "O arquivo excede o tamanho máximo permitido pelo PHP",
            UPLOAD_ERR_FORM_SIZE => "O arquivo excede o tamanho máximo permitido pelo formulário",
            UPLOAD_ERR_PARTIAL => "O upload do arquivo foi feito parcialmente",
            UPLOAD_ERR_NO_FILE => "Nenhum arquivo foi enviado",
            UPLOAD_ERR_NO_TMP_DIR => "Pasta temporária não encontrada",
            UPLOAD_ERR_CANT_WRITE => "Falha ao gravar arquivo em disco",
            UPLOAD_ERR_EXTENSION => "Uma extensão PHP interrompeu o upload"
        ];

        return $errors[$error_code] ?? "Erro desconhecido no upload (código: $error_code)";
    }    
    /**
     * Verifica e cria o diretório se necessário
     * 
     * @return bool TRUE se o diretório existe ou foi criado
     */
    private function check_directory() {
        $directory = "";
        $array = explode("/", $this->directory);

        foreach ($array as $dir) {
            $directory .= "{$dir}/";

            if (!file_exists($directory)) {
                $FILE = $this->WA_file();
                if (!$FILE->create_directory($directory)) {
                    return FALSE;
                }
                $FILE->copy_file("system/index.html", "{$directory}index.html");
            }
        }

        return TRUE;
    }
    
    /**
     * Valida os tipos de arquivo permitidos
     * 
     * @return bool TRUE se o tipo é válido
     */
    private function validate_file_types() {
        if ($this->file_types === "ALL") {
            return TRUE;
        }

        $allowed_types = explode("|", $this->file_types);
        $fileInfo = pathinfo($_FILES[$this->file_name]['name']);
        $this->type = strtolower($fileInfo['extension']);

        return in_array($this->type, $allowed_types);
    }

    /**
     * Retorna o erro ocorrido
     * 
     * @return string Mensagem de erro
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Obtém informações do arquivo enviado
     *      
     * @return array Informações do arquivo
     */
    public function get_info() {
        return $this->infoFile;
    }
    
    /**
     * Define a mensagem de erro
     * 
     * @param string $erro Mensagem de erro
     */
    private function set_error($error) {
        $this->error = $error;
    }

    /**
     * Define as informações do arquivo
     * 
     * @param array $info Informações do arquivo
     */
    private function set_info(array $info) {
        $this->infoFile = $info;
    }    
    /**
     * Retorna uma instância da classe WA_file
     * 
     * @return WA_file
     */
    private function WA_file() {        
        return new File();
    }

    private function WA_helper() {
        return new Helper();
    }
}