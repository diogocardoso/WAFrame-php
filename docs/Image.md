# Documentação da Classe Image

## Visão Geral

A classe `Image` do WAFrame fornece funcionalidades completas para processamento de imagens, incluindo redimensionamento, recorte, conversão de formatos e criação de thumbnails. Suporta os formatos GIF, JPEG, PNG e WebP.

**Versão**: 2.0  
**Autor**: DiogoCardoso  
**Copyright**: (c) 2025, webavance.com.br

## Requisitos

- PHP 7.4 ou superior
- Extensão GD2 habilitada
- Suporte a WebP (opcional, mas recomendado)

## Índice

1. [Instalação e Uso Básico](#instalação-e-uso-básico)
2. [Redimensionamento de Imagens](#redimensionamento-de-imagens)
3. [Recorte de Imagens](#recorte-de-imagens)
4. [Criação de Thumbnails](#criação-de-thumbnails)
5. [Movimentação de Arquivos](#movimentação-de-arquivos)
6. [Informações da Imagem](#informações-da-imagem)
7. [Configurações Avançadas](#configurações-avançadas)
8. [Tratamento de Erros](#tratamento-de-erros)
9. [Exemplos Completos](#exemplos-completos)

---

## Instalação e Uso Básico

### Importação

```php
use WAFrame\Image;
use WAFrame\Exceptions\ImageException;
use WAFrame\Exceptions\ImageNotFoundException;
use WAFrame\Exceptions\ImageInvalidTypeException;
use WAFrame\Exceptions\ImageProcessingException;
```

### Criação de Instância

```php
try {
    $image = new Image('path/to/image.jpg');
} catch (ImageNotFoundException $e) {
    echo "Imagem não encontrada: " . $e->getMessage();
}
```

---

## Redimensionamento de Imagens

### Redimensionamento Básico

Redimensiona uma imagem mantendo a proporção:

```php
$image = new Image('uploads/original.jpg');

// Define as dimensões desejadas
$image->set_width(800);
$image->set_height(600);

// Define o diretório de destino
$image->set_directory('uploads/resized');

// Define o nome do arquivo de saída
$image->set_name('resized_image.jpg');

// Redimensiona e salva
$image->resize(true);
```

### Redimensionamento com Qualidade Personalizada

```php
$image = new Image('uploads/original.jpg');
$image->set_width(1920);
$image->set_height(1080);
$image->set_quality(95); // Qualidade de 0 a 100
$image->set_directory('uploads/high_quality');
$image->set_name('hd_image.jpg');
$image->resize(true);
```

### Exibir Imagem Redimensionada no Navegador

```php
$image = new Image('uploads/original.jpg');
$image->set_width(800);
$image->set_height(600);
$image->resize(false); // false = exibe no navegador ao invés de salvar
```

### Redimensionamento Automático Mantendo Proporção

A classe calcula automaticamente as dimensões mantendo a proporção:

```php
$image = new Image('uploads/photo.jpg');

// Imagem original: 4000x3000
// Ao definir 800x600, a classe ajusta automaticamente para manter proporção
$image->set_width(800);
$image->set_height(600);
$image->set_directory('uploads');
$image->resize(true);
```

---

## Recorte de Imagens

### Recorte Básico

Recorta uma imagem nas dimensões especificadas:

```php
$image = new Image('uploads/original.jpg');

// Define a área de recorte
$image->set_width(200);
$image->set_height(200);

// Define a posição inicial (opcional, padrão: 0,0)
$image->set_x(100); // Posição X
$image->set_y(50);  // Posição Y

$image->set_directory('uploads/cropped');
$image->set_name('cropped_image.jpg');

$image->crop(true);
```

### Recorte Centralizado

```php
$image = new Image('uploads/photo.jpg');

// Obtém dimensões originais
$info = $image->get_info();
$originalWidth = $info['width'];
$originalHeight = $info['height'];

// Define recorte de 300x300 no centro
$cropSize = 300;
$image->set_width($cropSize);
$image->set_height($cropSize);

// Calcula posição central
$image->set_x(($originalWidth - $cropSize) / 2);
$image->set_y(($originalHeight - $cropSize) / 2);

$image->set_directory('uploads');
$image->set_name('centered_crop.jpg');
$image->crop(true);
```

---

## Criação de Thumbnails

### Thumbnail Simples

```php
$image = new Image('uploads/original.jpg');

// Dimensões da imagem principal
$image->set_width(800);
$image->set_height(600);
$image->set_directory('uploads/resized');
$image->set_name('main_image.jpg');

// Dimensões do thumbnail
$image->set_width_thumb(150);
$image->set_height_thumb(150);

// Diretório do thumbnail (opcional, padrão: diretorio/thumbs)
$image->set_directory_thumb('uploads/thumbnails');

// Redimensiona e cria thumbnail automaticamente
$image->resize(true);
```

### Múltiplos Thumbnails

```php
$image = new Image('uploads/original.jpg');

// Imagem principal
$image->set_width(1200);
$image->set_height(800);
$image->set_directory('uploads/large');
$image->set_name('large.jpg');

// Thumbnail pequeno
$image->set_width_thumb(150);
$image->set_height_thumb(150);
$image->set_directory_thumb('uploads/thumbs/small');
$image->resize(true);

// Thumbnail médio (nova instância)
$image2 = new Image('uploads/original.jpg');
$image2->set_width(1200);
$image2->set_height(800);
$image2->set_width_thumb(300);
$image2->set_height_thumb(300);
$image2->set_directory_thumb('uploads/thumbs/medium');
$image2->resize(true);
```

---

## Movimentação de Arquivos

### Mover Imagem para Novo Diretório

```php
$image = new Image('uploads/temp/image.jpg');

// Define o diretório de destino
$image->set_directory('uploads/permanent');
$image->set_name('moved_image.jpg');

// Move o arquivo
$image->mover();

// Se thumbnail estiver configurado, também será movido
if ($image->get_width_thumb() && $image->get_height_thumb()) {
    // Thumbnail será criado automaticamente no diretório especificado
}
```

### Deletar Imagem

```php
$image = new Image('uploads/image.jpg');

try {
    $image->drop('uploads/image.jpg');
    echo "Imagem deletada com sucesso";
} catch (ImageNotFoundException $e) {
    echo "Arquivo não encontrado";
}
```

---

## Informações da Imagem

### Obter Informações Básicas

```php
$image = new Image('uploads/photo.jpg');

// Retorna array com informações
$info = $image->get_info();

print_r($info);
/*
Array
(
    [width] => 1920
    [height] => 1080
    [bits] => 8
    [mime] => image/jpeg
    [type] => jpeg
    [date] => 1704067200
    [name] => photo.jpg
)
*/
```

### Obter Informações em JSON

```php
$image = new Image('uploads/photo.jpg');
$infoJson = $image->get_info(true);
echo $infoJson;
```

### Obter Nome da Imagem

```php
$image = new Image('uploads/original.jpg');
$image->set_name('new_name.jpg');

$name = $image->get_imagem_name();
echo $name; // "new_name.jpg"
```

---

## Configurações Avançadas

### Configurar Biblioteca de Imagem

```php
$image = new Image('uploads/photo.jpg');
$image->set_image_library('gd2'); // gd2, gd
```

### Configurar Qualidade (JPEG/WebP)

```php
$image = new Image('uploads/photo.jpg');
$image->set_quality(90); // 0 a 100 (padrão: 80)
$image->set_width(800);
$image->set_height(600);
$image->resize(true);
```

### Validação de Dimensões

A classe valida automaticamente as dimensões (mínimo: 1px, máximo: 10000px):

```php
try {
    $image = new Image('uploads/photo.jpg');
    $image->set_width(15000); // Erro: excede o máximo
} catch (ImageException $e) {
    echo $e->getMessage(); // "Width must be between 1 and 10000"
}
```

### Validação de Qualidade

```php
try {
    $image = new Image('uploads/photo.jpg');
    $image->set_quality(150); // Erro: excede o máximo
} catch (ImageException $e) {
    echo $e->getMessage(); // "Quality must be between 0 and 100"
}
```

---

## Tratamento de Erros

A classe utiliza exceptions customizadas para tratamento de erros:

### Exceptions Disponíveis

- `ImageException` - Exception base para todos os erros de imagem
- `ImageNotFoundException` - Arquivo de imagem não encontrado
- `ImageInvalidTypeException` - Tipo de imagem inválido ou não suportado
- `ImageProcessingException` - Erro durante o processamento

### Exemplo Completo de Tratamento

```php
use WAFrame\Image;
use WAFrame\Exceptions\ImageException;
use WAFrame\Exceptions\ImageNotFoundException;
use WAFrame\Exceptions\ImageInvalidTypeException;
use WAFrame\Exceptions\ImageProcessingException;

try {
    $image = new Image('uploads/photo.jpg');
    $image->set_width(800);
    $image->set_height(600);
    $image->set_directory('uploads/resized');
    $image->resize(true);
    
    echo "Imagem processada com sucesso!";
    
} catch (ImageNotFoundException $e) {
    echo "Erro: Imagem não encontrada - " . $e->getMessage();
    
} catch (ImageInvalidTypeException $e) {
    echo "Erro: Tipo de imagem inválido - " . $e->getMessage();
    echo "Tipos permitidos: " . implode(', ', ['GIF', 'JPEG', 'PNG', 'WebP']);
    
} catch (ImageProcessingException $e) {
    echo "Erro durante processamento - " . $e->getMessage();
    
} catch (ImageException $e) {
    echo "Erro geral: " . $e->getMessage();
}
```

---

## Exemplos Completos

### Exemplo 1: Upload e Processamento de Imagem

```php
<?php
use WAFrame\Image;
use WAFrame\Exceptions\ImageException;

// Simula upload de arquivo
$uploadedFile = $_FILES['image']['tmp_name'];
$uploadDir = 'uploads/';

// Move arquivo temporário
move_uploaded_file($uploadedFile, $uploadDir . $_FILES['image']['name']);

try {
    $image = new Image($uploadDir . $_FILES['image']['name']);
    
    // Cria versão grande
    $image->set_width(1920);
    $image->set_height(1080);
    $image->set_quality(90);
    $image->set_directory('uploads/large');
    $image->set_name('large_' . $_FILES['image']['name']);
    $image->resize(true);
    
    // Cria versão média
    $image->set_width(800);
    $image->set_height(600);
    $image->set_directory('uploads/medium');
    $image->set_name('medium_' . $_FILES['image']['name']);
    $image->resize(true);
    
    // Cria thumbnail
    $image->set_width(150);
    $image->set_height(150);
    $image->set_directory('uploads/thumbs');
    $image->set_name('thumb_' . $_FILES['image']['name']);
    $image->resize(true);
    
    echo "Imagem processada com sucesso!";
    
} catch (ImageException $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Exemplo 2: Galeria com Thumbnails

```php
<?php
use WAFrame\Image;

function processGalleryImage($sourcePath, $filename) {
    $image = new Image($sourcePath);
    
    // Imagem principal para galeria
    $image->set_width(1200);
    $image->set_height(800);
    $image->set_quality(85);
    $image->set_directory('gallery/images');
    $image->set_name($filename);
    
    // Thumbnail para lista
    $image->set_width_thumb(300);
    $image->set_height_thumb(200);
    $image->set_directory_thumb('gallery/thumbs');
    
    $image->resize(true);
    
    return [
        'image' => 'gallery/images/' . $filename,
        'thumb' => 'gallery/thumbs/' . $filename
    ];
}

// Processa múltiplas imagens
$images = ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'];

foreach ($images as $img) {
    $result = processGalleryImage('uploads/' . $img, $img);
    echo "Processado: {$result['image']}\n";
}
```

### Exemplo 3: Conversão de Formato

```php
<?php
use WAFrame\Image;

function convertToWebP($sourcePath, $outputPath) {
    $image = new Image($sourcePath);
    
    // Obtém dimensões originais
    $info = $image->get_info();
    
    // Mantém dimensões originais
    $image->set_width($info['width']);
    $image->set_height($info['height']);
    $image->set_quality(90);
    
    // Define novo nome com extensão .webp
    $pathInfo = pathinfo($outputPath);
    $image->set_directory($pathInfo['dirname']);
    $image->set_name($pathInfo['filename'] . '.webp');
    
    $image->resize(true);
    
    return $image->get_imagem_name();
}

// Converte JPEG para WebP
$webpFile = convertToWebP('uploads/photo.jpg', 'uploads/photo.webp');
echo "Convertido para: $webpFile";
```

### Exemplo 4: Avatar com Recorte Centralizado

```php
<?php
use WAFrame\Image;

function createAvatar($sourcePath, $userId) {
    $image = new Image($sourcePath);
    
    // Obtém informações da imagem
    $info = $image->get_info();
    $size = min($info['width'], $info['height']);
    
    // Recorta quadrado centralizado
    $image->set_width($size);
    $image->set_height($size);
    
    // Calcula posição para centralizar
    $x = ($info['width'] - $size) / 2;
    $y = ($info['height'] - $size) / 2;
    
    $image->set_x($x);
    $image->set_y($y);
    
    // Redimensiona para tamanho final
    $image->set_width(200);
    $image->set_height(200);
    $image->set_quality(90);
    $image->set_directory('avatars');
    $image->set_name("avatar_{$userId}.jpg");
    
    $image->crop(true);
    
    return "avatars/avatar_{$userId}.jpg";
}

$avatarPath = createAvatar('uploads/user_photo.jpg', 123);
echo "Avatar criado: $avatarPath";
```

### Exemplo 5: Processamento em Lote com Tratamento de Erros

```php
<?php
use WAFrame\Image;
use WAFrame\Exceptions\ImageException;

function processBatchImages($imagePaths) {
    $results = [
        'success' => [],
        'errors' => []
    ];
    
    foreach ($imagePaths as $path) {
        try {
            $image = new Image($path);
            
            // Processa imagem
            $image->set_width(800);
            $image->set_height(600);
            $image->set_directory('processed');
            $image->set_name(basename($path));
            $image->resize(true);
            
            $results['success'][] = $path;
            
        } catch (ImageNotFoundException $e) {
            $results['errors'][] = [
                'file' => $path,
                'error' => 'Arquivo não encontrado'
            ];
            
        } catch (ImageInvalidTypeException $e) {
            $results['errors'][] = [
                'file' => $path,
                'error' => 'Tipo de imagem inválido'
            ];
            
        } catch (ImageException $e) {
            $results['errors'][] = [
                'file' => $path,
                'error' => $e->getMessage()
            ];
        }
    }
    
    return $results;
}

// Processa múltiplas imagens
$images = [
    'uploads/img1.jpg',
    'uploads/img2.png',
    'uploads/img3.gif',
    'uploads/invalid.txt'
];

$results = processBatchImages($images);

echo "Sucesso: " . count($results['success']) . " imagens\n";
echo "Erros: " . count($results['errors']) . " arquivos\n";

foreach ($results['errors'] as $error) {
    echo "  - {$error['file']}: {$error['error']}\n";
}
```

---

## Formatos Suportados

### Formatos de Entrada
- **GIF** - Graphics Interchange Format
- **JPEG** - Joint Photographic Experts Group
- **PNG** - Portable Network Graphics
- **WebP** - Web Picture Format

### Formatos de Saída
- **GIF** - Com suporte a transparência
- **JPEG** - Com controle de qualidade (0-100)
- **PNG** - Com suporte a transparência
- **WebP** - Com controle de qualidade (0-100) e transparência

---

## Limitações e Validações

### Dimensões
- **Mínimo**: 1 pixel
- **Máximo**: 10.000 pixels

### Qualidade
- **Mínimo**: 0
- **Máximo**: 100
- **Padrão**: 80

### Validações Automáticas
- Verificação de existência do arquivo
- Validação de tipo MIME
- Validação de magic bytes (assinatura do arquivo)
- Proteção contra path traversal
- Validação de dimensões e qualidade

---

## Dicas de Performance

1. **Cache de Propriedades**: A classe cacheia automaticamente as propriedades da imagem para evitar múltiplas leituras
2. **Reutilização de Instância**: Para thumbnails, a classe reutiliza a instância ao invés de criar nova
3. **Gerenciamento de Memória**: Recursos GD são liberados automaticamente após uso
4. **Lazy Loading**: Propriedades são carregadas apenas quando necessário

---

## Troubleshooting

### Erro: "Image file not found"
- Verifique se o caminho do arquivo está correto
- Verifique permissões de leitura do arquivo

### Erro: "Invalid image type"
- Verifique se o arquivo é realmente uma imagem
- Confirme que o formato é suportado (GIF, JPEG, PNG, WebP)
- Verifique se a extensão GD está habilitada

### Erro: "GD function is not available"
- Instale ou habilite a extensão GD no PHP
- Para WebP, certifique-se de que o PHP foi compilado com suporte a WebP

### Erro: "Failed to create image resource"
- Verifique se o arquivo não está corrompido
- Confirme que há memória suficiente disponível
- Verifique se o arquivo não está sendo usado por outro processo

---

## Changelog

### Versão 2.0
- Refatoração completa com type hints
- Suporte a WebP
- Exceptions customizadas
- Validação de MIME types e magic bytes
- Proteção contra path traversal
- Cache de propriedades
- Melhor gerenciamento de memória
- Documentação completa

---

## Suporte

Para dúvidas ou problemas:
- Consulte a documentação completa
- Verifique os exemplos fornecidos
- Revise o tratamento de erros

---

**Versão**: 2.0  
**Data**: Janeiro 2025  
**Autor**: Sistema WAFrame

