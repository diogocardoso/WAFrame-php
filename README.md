# WAFrame

WA Framework - Framework personalizado para PHP

## Instalação

```bash
composer require waframe/waframe
```

## Uso

```php
use WAFrame\WA;

// Helper
$helper = WA::helper();
$data = $helper->date_br('2024-01-15');

// Validação
$validate = WA::validate();
$isValid = $validate->email('test@example.com');

// Validação brasileira
$validateBR = WA::validateBR();
$isValidCPF = $validateBR->cpf('123.456.789-00');

// Upload
$upload = WA::upload();
$result = $upload->file($_FILES['file']);

// Arquivo
$file = WA::file();
$content = $file->read('path/to/file.txt');
```

## Funcionalidades

- **Helper**: Funções utilitárias para datas, strings, arrays, etc.
- **Validate**: Validação de dados básicos (email, data, datetime)
- **ValidateBR**: Validação específica para Brasil (CPF, CNPJ, CEP, telefone, etc.)
- **Upload**: Upload de arquivos com validação
- **File**: Manipulação de arquivos

## Versão

1.0.0

## Autor

WA Team - dev@wa.com