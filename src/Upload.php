<?php

namespace WAFrame;

use Exception;
use WAFrame\File;
use WAFrame\Helper;

/**
 * Classe para gerenciamento de upload de arquivos
 * Suporta dois tipos de upload:
 * 1. Upload via formulário ($_FILES)
 * 2. Upload manual via set_file()
 *
 * @version 2.0
 * @author Di0g0 CaRd0s0
 * @link http://www.webadvance.com.br
 */
class Upload 
{
    // Constantes
    private const ALLOW_ALL_TYPES = "ALL";
    private const DEFAULT_MIME_TYPE = 'application/octet-stream';
    
    // Propriedades de configuração
    private ?string $directory = null;
    private ?string $file_name = null;
    private ?string $types = null;
    private ?int $max_file_size = null;
    private bool $encript_name = true;
    
    // Propriedades do arquivo
    private bool $isManualUpload = false;
    private ?string $file_tmp_name = null;
    private ?string $file_type = null;
    private ?int $file_size = null;
    private ?string $name = null;
    
    // Propriedades de estado
    private ?string $error = null;
    private ?array $infoFile = null;
    
    // Dependências
    private ?File $fileHandler = null;
    private ?Helper $helper = null;

    /**
     * Construtor da classe
     * 
     * @param bool $encript_name Define se o nome do arquivo será criptografado
     */
    public function __construct(bool $encript_name = true) 
    {
        $this->encript_name = $encript_name;
    }

    /**
     * Carrega configurações iniciais
     * 
     * @param array $values Configurações: file_name, types, max_file_size
     * @return void
     */
    public function load(array $values): void 
    {
        $this->file_name = $values['file_name'] ?? null;
        $this->types = $values['types'] ?? null;
        $this->max_file_size = $values['max_file_size'] ?? null;
    }

    /**
     * Define arquivo manualmente (upload tipo 2)
     * 
     * @param string $name Nome do arquivo
     * @param string $tmp_name Caminho temporário do arquivo
     * @param string $type Extensão do arquivo
     * @param int $size Tamanho do arquivo em bytes
     * @return void
     */
    public function set_file(string $name, string $tmp_name, string $type, int $size): void 
    {
        $this->isManualUpload = true;
        $this->file_name = $name;
        $this->file_tmp_name = $tmp_name;
        $this->file_type = strtolower($type);
        $this->file_size = $size;
    }

    /**
     * Define o diretório de upload
     * 
     * @param string $directory Caminho do diretório
     * @return void
     */
    public function set_directory(string $directory): void 
    {
        $this->directory = trim($directory, "/");
    }

    /**
     * Define o nome do arquivo
     * 
     * @param string $file_name Nome do arquivo
     * @return void
     */
    public function set_file_name(string $file_name): void 
    {
        $this->name = $file_name;
    }

    /**
     * Define os tipos de arquivo permitidos
     * 
     * @param string $types Tipos de arquivo separados por |
     * @return void
     */
    public function set_file_types(string $types): void 
    {
        $this->types = $types;
    }

    /**
     * Define o tamanho máximo do arquivo
     * 
     * @param int $max_size Tamanho máximo em bytes
     * @return void
     */
    public function set_max_file_size(int $max_size): void 
    {
        $this->max_file_size = $max_size;
    }

    /**
     * Processa o upload do arquivo
     * 
     * @return bool TRUE se o upload foi bem sucedido, FALSE caso contrário
     */
    public function show(): bool 
    {
        try {
            if (!$this->validate()) {
                return false;
            }

            $this->prepareFileName();
            $destination = $this->buildDestinationPath();
            
            $this->moveFileToDestination($destination);
            $this->verifyFileWasMoved($destination);
            $this->buildFileInfo($destination);

            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * Retorna o nome do arquivo após upload
     * 
     * @return string|false Nome do arquivo ou FALSE
     */
    public function get_file_name() 
    {
        return $this->name ?: false;
    }

    /**
     * Retorna o erro ocorrido
     * 
     * @return string|null Mensagem de erro
     */
    public function get_error(): ?string 
    {
        return $this->error;
    }

    /**
     * Obtém informações do arquivo enviado
     * 
     * @return array|null Informações do arquivo
     */
    public function get_info(): ?array 
    {
        return $this->infoFile;
    }

    // ==========================================
    // Métodos Privados - Validação
    // ==========================================

    /**
     * Valida os dados do upload
     * 
     * @return bool TRUE se os dados são válidos
     */
    private function validate(): bool 
    {
        if (!$this->ensureDirectoryExists()) {
            $this->setError("Erro ao verificar/criar diretório de upload.");
            return false;
        }

        if ($this->isManualUpload) {
            return $this->validateManualUpload();
        }

        return $this->validateFormUpload();
    }

    /**
     * Valida upload manual via set_file()
     * 
     * @return bool
     */
    private function validateManualUpload(): bool 
    {
        if (!file_exists($this->file_tmp_name)) {
            $this->setError("Arquivo temporário não encontrado.");
            return false;
        }

        if ($this->file_size === 0) {
            $this->setError("O arquivo está vazio.");
            return false;
        }

        return $this->validateFileSize() && $this->validateFileTypes();
    }

    /**
     * Valida upload via formulário ($_FILES)
     * 
     * @return bool
     */
    private function validateFormUpload(): bool 
    {
        if (!isset($_FILES[$this->file_name])) {
            $this->setError("Nenhum arquivo foi enviado.");
            return false;
        }

        $uploadError = $_FILES[$this->file_name]['error'];
        if ($uploadError !== UPLOAD_ERR_OK) {
            $this->setError($this->getUploadErrorMessage($uploadError));
            return false;
        }

        $tmpName = $_FILES[$this->file_name]['tmp_name'];
        if (!file_exists($tmpName)) {
            $this->setError("Arquivo temporário não encontrado.");
            return false;
        }

        $this->file_size = $_FILES[$this->file_name]['size'];
        if ($this->file_size === 0) {
            $this->setError("O arquivo está vazio.");
            return false;
        }

        return $this->validateFileSize() && $this->validateFileTypes();
    }

    /**
     * Valida o tamanho do arquivo
     * 
     * @return bool
     */
    private function validateFileSize(): bool 
    {
        if ($this->max_file_size && $this->file_size > $this->max_file_size) {
            $this->setError("O arquivo excede o tamanho máximo permitido.");
            return false;
        }

        return true;
    }

    /**
     * Valida os tipos de arquivo permitidos
     * 
     * @return bool
     */
    private function validateFileTypes(): bool 
    {
        if ($this->types === self::ALLOW_ALL_TYPES || !$this->types) {
            return true;
        }

        $allowedTypes = explode("|", $this->types);
        $fileExtension = $this->getFileExtension();

        return in_array(strtolower($fileExtension), $allowedTypes);
    }

    /**
     * Obtém a extensão do arquivo
     * 
     * @return string
     */
    private function getFileExtension(): string 
    {
        if ($this->file_type) {
            return $this->file_type;
        }

        if ($this->isManualUpload) {
            $fileInfo = pathinfo($this->file_name);
        } else {
            $fileInfo = pathinfo($_FILES[$this->file_name]['name']);
        }

        $extension = $fileInfo['extension'] ?? '';
        $this->file_type = strtolower($extension);

        return $this->file_type;
    }

    // ==========================================
    // Métodos Privados - Processamento
    // ==========================================

    /**
     * Prepara o nome do arquivo para upload
     * 
     * @return void
     */
    private function prepareFileName(): void 
    {
        $this->ensureFileTypeIsSet();

        if ($this->encript_name) {
            $this->name = $this->getHelper()->create_key() . '.' . $this->file_type;
            return;
        }

        if ($this->name) {
            $this->name = trim($this->name, '.' . $this->file_type) . '.' . $this->file_type;
        } else {
            $this->name = $this->file_name;
        }
    }

    /**
     * Garante que o tipo do arquivo está definido
     * 
     * @return void
     */
    private function ensureFileTypeIsSet(): void 
    {
        if ($this->file_type) {
            return;
        }

        if ($this->isManualUpload) {
            $fileInfo = pathinfo($this->file_name);
        } else {
            $fileInfo = pathinfo($_FILES[$this->file_name]['name']);
        }

        $this->file_type = strtolower($fileInfo['extension'] ?? '');
    }

    /**
     * Constrói o caminho de destino do arquivo
     * 
     * @return string
     */
    private function buildDestinationPath(): string 
    {
        return "{$this->directory}/{$this->name}";
    }

    /**
     * Move o arquivo para o destino
     * 
     * @param string $destination Caminho de destino
     * @return void
     * @throws Exception
     */
    private function moveFileToDestination(string $destination): void 
    {
        if ($this->isManualUpload) {
            $this->copyManualFile($destination);
        } else {
            $this->moveFormFile($destination);
        }
    }

    /**
     * Copia arquivo manual (upload tipo 2)
     * 
     * @param string $destination Caminho de destino
     * @return void
     * @throws Exception
     */
    private function copyManualFile(string $destination): void 
    {
        if (!copy($this->file_tmp_name, $destination)) {
            $error = error_get_last();
            $message = $error ? $error['message'] : 'Erro desconhecido';
            throw new Exception("Erro ao copiar o arquivo para o diretório de destino: {$message}");
        }
    }

    /**
     * Move arquivo de formulário (upload tipo 1)
     * 
     * @param string $destination Caminho de destino
     * @return void
     * @throws Exception
     */
    private function moveFormFile(string $destination): void 
    {
        $source = $_FILES[$this->file_name]['tmp_name'];
        if (!move_uploaded_file($source, $destination)) {
            $error = error_get_last();
            $message = $error ? $error['message'] : 'Erro desconhecido';
            throw new Exception("Erro ao mover o arquivo para o diretório de destino: {$message}");
        }
    }

    /**
     * Verifica se o arquivo foi movido corretamente
     * 
     * @param string $destination Caminho de destino
     * @return void
     * @throws Exception
     */
    private function verifyFileWasMoved(string $destination): void 
    {
        if (!file_exists($destination)) {
            throw new Exception("O arquivo não foi movido corretamente para o destino.");
        }
    }

    /**
     * Constrói informações do arquivo após upload
     * 
     * @param string $destination Caminho de destino
     * @return void
     */
    private function buildFileInfo(string $destination): void 
    {
        $oldName = $this->getOriginalFileName();
        $mimeType = $this->getMimeType();

        $this->infoFile = [
            "old" => $oldName,
            "name" => $this->name,
            "size" => $this->getHelper()->format_size($this->file_size),
            "type" => $this->file_type,
            "path" => $destination,
            "mime_type" => $mimeType,
            "upload_date" => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtém o nome original do arquivo
     * 
     * @return string
     */
    private function getOriginalFileName(): string 
    {
        if ($this->isManualUpload) {
            return $this->file_name;
        }

        return $_FILES[$this->file_name]['name'];
    }

    /**
     * Obtém o MIME type do arquivo
     * 
     * @return string
     */
    private function getMimeType(): string 
    {
        if ($this->isManualUpload) {
            $source = $this->file_tmp_name;
            return mime_content_type($source) ?: self::DEFAULT_MIME_TYPE;
        }

        return $_FILES[$this->file_name]['type'] ?: self::DEFAULT_MIME_TYPE;
    }

    // ==========================================
    // Métodos Privados - Utilitários
    // ==========================================

    /**
     * Verifica e cria o diretório se necessário
     * 
     * @return bool
     */
    private function ensureDirectoryExists(): bool 
    {
        $directory = "";
        $parts = explode("/", $this->directory);

        foreach ($parts as $part) {
            $directory .= "{$part}/";

            if (!file_exists($directory)) {
                if (!$this->createDirectory($directory)) {
                    return false;
                }
                $this->createIndexFile($directory);
            }
        }

        return true;
    }

    /**
     * Cria um diretório
     * 
     * @param string $directory Caminho do diretório
     * @return bool
     */
    private function createDirectory(string $directory): bool 
    {
        return $this->getFileHandler()->create_directory($directory);
    }

    /**
     * Cria arquivo index.html no diretório
     * 
     * @param string $directory Caminho do diretório
     * @return void
     */
    private function createIndexFile(string $directory): void 
    {
        $this->getFileHandler()->copy_file("system/index.html", "{$directory}index.html");
    }

    /**
     * Obtém mensagem de erro do upload
     * 
     * @param int $errorCode Código do erro
     * @return string
     */
    private function getUploadErrorMessage(int $errorCode): string 
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => "O arquivo excede o tamanho máximo permitido pelo PHP",
            UPLOAD_ERR_FORM_SIZE => "O arquivo excede o tamanho máximo permitido pelo formulário",
            UPLOAD_ERR_PARTIAL => "O upload do arquivo foi feito parcialmente",
            UPLOAD_ERR_NO_FILE => "Nenhum arquivo foi enviado",
            UPLOAD_ERR_NO_TMP_DIR => "Pasta temporária não encontrada",
            UPLOAD_ERR_CANT_WRITE => "Falha ao gravar arquivo em disco",
            UPLOAD_ERR_EXTENSION => "Uma extensão PHP interrompeu o upload"
        ];

        return $errors[$errorCode] ?? "Erro desconhecido no upload (código: {$errorCode})";
    }

    /**
     * Define mensagem de erro
     * 
     * @param string $error Mensagem de erro
     * @return void
     */
    private function setError(string $error): void 
    {
        $this->error = $error;
    }

    /**
     * Obtém instância do File Handler (lazy loading)
     * 
     * @return File
     */
    private function getFileHandler(): File 
    {
        if ($this->fileHandler === null) {
            $this->fileHandler = new File();
        }

        return $this->fileHandler;
    }

    /**
     * Obtém instância do Helper (lazy loading)
     * 
     * @return Helper
     */
    private function getHelper(): Helper 
    {
        if ($this->helper === null) {
            $this->helper = new Helper();
        }

        return $this->helper;
    }
}
