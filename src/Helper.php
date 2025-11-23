<?php

namespace WAFrame;

use DateTime;
use DateInterval;
use Exception;

class Helper {

    public function attr(array $attr){    
        $attributes = [];

        // Itera sobre o array de atributos
        foreach ($attr as $key => $value) {
            // Adiciona o atributo formatado ao array
            $attributes[] = sprintf('%s="%s"', htmlspecialchars($key), htmlspecialchars($value));
        }

        // Junta os atributos em uma string e retorna
        return implode(' ', $attributes);
    }

    public function array_key(array $Array, $key, $value){
        $tmp = FALSE;
        foreach ($Array as $k=>$Obj){
            if(isset($Obj[$key]) && $Obj[$key]==$value){
                $tmp = $key;
                break;
            }
        }

        return $tmp;
    }

    public function array_keys($array, $search, $path = []) {
        // Adiciona a chave atual ao caminho
        foreach ($array as $key => $value) {
            $new_path = array_merge($path, [$key]);

            // Se a chave procurada for encontrada, retorna o caminho
            if ($key === $search) {
                return $new_path;
            }

            // Se o valor for um array, chama a função recursivamente
            if (is_array($value)) {
                $resultado = $this->array_keys($value, $search, $new_path);
                if ($resultado) {
                    return $resultado;
                }
            }
        }
        
        // Retorna null se a chave não for encontrada
        return null;
    }

    public function array_lowercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[strtolower($key)] = $this->lowercase($val); } return $tmp;
    }

    public function array_uppercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[strtoupper($key)] = $this->uppercase($val); } return $tmp;
    }

    public function array_key_lowercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[strtolower($key)] = $val; } return $tmp;
    }

    public function array_key_uppercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[strtoupper($key)] = $val; } return $tmp;
    }

    public function array_val_lowercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[$key] = $this->lowercase($val); } return $tmp;
    }

    public function array_val_uppercase(array $Array){
        $tmp = []; foreach ($Array as $key=>$val){ $tmp[$key] = $this->uppercase($val); } return $tmp;
    }

    public function array_remove_keys($data, $level = 1) {
        if (!is_array($data) || $level < 1) {
            return $data;
        }
        
        if ($level === 1) {
            // Para nível 1, remove chaves numéricas e merge os arrays internos
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $result = array_merge($result, $item);
                }
            }
            return $result;
        }
        
        // Para outros níveis, usa recursão
        return $this->remove_keys_recursive($data, $level, 1);
    }

    public function array_search(array $array, $key, $value) {
        // Verifica se o array é realmente um array
        if (!is_array($array)) {
            return false; // Retorna null se não for um array
        }

        // Percorre cada item do array
        foreach ($array as $item) {
            // Se o item for um array, chama a função recursivamente
            if (is_array($item)) {
                // Verifica se a chave e o valor correspondem
                if (isset($item[$key]) && $item[$key] === $value) {
                    return $item; // Retorna o item se a chave e o valor corresponderem
                }

                // Chama a função recursivamente para verificar sub-arrays
                $result = $this->array_search($item, $key, $value);
                if (!$result) {
                    return $result; // Retorna o resultado se encontrado
                }
            }
        }

        return false;
    }

    public function array_search_key(array $array, $key, $value) {
        if (!is_array($array)) {
            return false; // Retorna null se não for um array
        }

        // Percorre cada item do array
        foreach ($array as $k=>$item) {
            // Se o item for um array, chama a função recursivamente
            if (is_array($item)) {
                // Verifica se a chave e o valor correspondem
                if (isset($item[$key]) && $item[$key] === $value) {
                    return $k; // Retorna o item se a chave e o valor corresponderem
                }

                // Chama a função recursivamente para verificar sub-arrays
                $result = $this->array_search_key($item, $key, $value);
                if (!$result) {
                    return $result; // Retorna o resultado se encontrado
                }
            }
        }

        return false;
    }

    public function array_values(array $Array, $item){
        $tmp = FALSE;

        if(is_array($item)){
            $tmp = $Array;
            foreach ($item as $n=>$i){
                if(isset($tmp[$i])){
                    $tmp = $tmp[$i];
                }else{
                    $tmp = FALSE;
                    break;
                }
            }
        }else if(isset($Array[$item])){
            $tmp = $Array[$item];
        }

        return $tmp;
    }

    public function array_value_key(array $Array, $value){
        if(count($Array)>0){
            
        }
        return FALSE;
    }

    public function array_first(array $Array,$key=FALSE){
        $tmp = FALSE;

        if(is_array($Array)){
            $keys = array_keys($Array);

            if($key){
                $tmp = $keys[0];
            }else{
                $tmp = $Array[$keys[0]];
            }
        }

        return $tmp;
    }

    public function array_end(array $Array, $key=FALSE){
        $tmp = FALSE;

        if(is_array($Array)){
            $t = count($Array) - 1;
            $keys = array_keys($Array);

            if($key){
                $tmp = $keys[$t];
            }else{
                $tmp = $Array[$keys[$t]];
            }
        }

        return $tmp;
    }

    public function cellphone_string($cell){
        $cell = $this->cellphone_number($cell);
        $num = strlen($cell);

        if($num==11){
            $tmp = "(".substr($cell, 0, 2).")";
            $tmp.= " ";
            $tmp.= substr($cell, 2, 5);
            $tmp.= "-";
            $tmp.= substr($cell, 7, 4);
        }elseif ($num==9) {
            $tmp = substr($cell, 0, 5);
            $tmp.= "-";
            $tmp.= substr($cell, 4, 4);
        }elseif ($num==8) {
            $tmp = substr($cell, 0, 4);
            $tmp.= "-";
            $tmp.= substr($cell, 5, 4);
        }else{
            $tmp = $cell;
        }

        return $tmp;
    }

    public function cellphone_number($cell){
        return str_replace(['(',')',' ','-'],'',$cell);
    }

    public function cellphone_ddd($number) {
        // Remove espaços, parênteses e traços
        $telefone = preg_replace('/[\s()-]/', '', $number);

        // Verifica se o número tem 11 dígitos (com DDD)
        if (strlen($number) === 11) {
            $ddd = substr($number, 0, 2);
            $numero = substr($number, 2);
        } else {
            // Caso contrário, assume que é um número sem DDD
            $ddd = null;
            $numero = $number;
        }

        return ['ddd' => $ddd, 'number' => $numero];
    }

    public function cpf_string($cpf){
        if(!$this->in_string(['.','-'],$cpf)){
            $cpf = substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9, 2);
        }
        return $cpf;    
    }

    public function cpf_number($cpf){
        if($this->in_string(['.','-'], $cpf)){
            $cpf = str_replace(['.','-'],['',''],$cpf);
        }
        return $cpf;
    }

    public function data_br($data){
        return $this->date_br($data);
    }

    public function data_us($data_br){
        return $this->date_us($data_br);
    }

    public function date_br($data){  
        if($this->is_valid($data) && $this->in_string('-',$data)){
            $arr = explode('-', $data);
            return "{$arr[2]}/{$arr[1]}/{$arr[0]}";
        }

        return $data;
    }

    public function date_us($data_br){
        if($this->is_valid($data_br) && $this->in_string('/',$data_br)){
            $arr = explode('/', $data_br);
            return "{$arr[2]}-{$arr[1]}-{$arr[0]}";
        }

        return $data_br;
    }

    public function date_compare($date_1,$date_2,$comparator){
        try {
            $d1 = new DateTime($date_1);
            $d2 = new DateTime($date_2);

            // Se as datas não têm horário especificado, comparar apenas a data
            $date1_has_time = (strpos($date_1, ':') !== false);
            $date2_has_time = (strpos($date_2, ':') !== false);
            
            // Se ambas as datas não têm horário, comparar apenas a data
            if (!$date1_has_time && !$date2_has_time) {
                $format = 'Y-m-d';
            } else {
                $format = 'Y-m-d H:i:s';
            }

            // Comparar baseado no operador
            switch ($comparator) {
                case '>':
                    return $d1 > $d2;
                case '>=':
                    return $d1 >= $d2;
                case '<':
                    return $d1 < $d2;
                case '<=':
                    return $d1 <= $d2;
                case '=':
                case '==':
                    return $d1->format($format) === $d2->format($format);
                case '!=':
                case '<>':
                    return $d1->format($format) !== $d2->format($format);
                default:
                    throw new Exception("Operador de comparação inválido: {$comparator}");
            }
        } catch (Exception $e) {
            error_log("Erro em date_compare: " . $e->getMessage());
            return false;
        }
    }

    public function date_format($date, $format){
        $date = new DateTime($date);
        return $date->format($format);
    }

    public function date_sum($date, $num){
        if($this->in_string('/',$date)){ $date = $this->date_br($date); }  

        return date('Y-m-d', strtotime("+{$num} days", strtotime($date)));
    }

    public function date_sub($date, $num){
        if($this->in_string('/',$date)){ $date = $this->date_br($date); }
        return date('Y-m-d', strtotime("-{$num} days", strtotime($date)));
    }

    public function date_interval($ini,$fim){
        if($this->in_string('/',$ini)){ $ini = $this->date_br($ini); }
        if($this->in_string('/',$fim)){ $fim = $this->date_br($fim); }

        $interval = strtotime($fim) - strtotime($ini);

        return floor($interval / (60 * 60 * 24));
    }

    public function date_next($date, array $validate){
        $num_week = $this->day_week_num($date);

        if(in_array($num_week, $validate)){
            return $date;
        }else{
            $next = $this->date_sum($date, 1);
            return $this->date_next($next, $validate);
        }
    }

    public function datetime_br($datetime){
        if(strstr($datetime,'-')){
            $arr = explode(' ', $datetime);
            $data = explode('-', $arr[0]);
            
            return "{$data[2]}/{$data[1]}/{$data[0]} {$arr[1]}";
        }

        return $datetime;
    }

    public function datetime_us($datetime){    
        if(strstr($datetime,'/')){
            $arr = explode(' ', $datetime);
            $data = explode('/', $arr[0]);
            
            return "{$data[2]}-{$data[1]}-{$data[0]} {$arr[1]}";
        }

        return $datetime;
    }

    public function datetime_now(){
        return date('Y-m-d H:i:s');
    }

    public function datetime_compare($datetime_1,$datetime_2,$operator){
        $dt_1 = new DateTime($datetime_1);
        $dt_2 = new DateTime($datetime_2);

        switch($operator){
            case'==': if($dt_1==$dt_2){ return TRUE; } break;
            case'>': if($dt_1>$dt_2){ return TRUE; } break;    
            case'>=': if($dt_1>=$dt_2){ return TRUE; } break;
            case'<': if($dt_1<$dt_2){ return TRUE; } break;        
            case'<=': if($dt_1<=$dt_2){ return TRUE; } break;
        }

        return FALSE;
    }

    public function datetime_sum(string $date_time, string $time): string {
        if($this->in_string('/',$date_time)){
            $date_time = $this->datetime_us($date_time);
        }
        // Cria um objeto DateTime a partir da string fornecida
        $dateTime = new DateTime($date_time);
        
        // Separa as partes da string de tempo
        list($hours, $minutes, $seconds) = explode(':', $time);
            
        // Cria um objeto DateInterval a partir das partes da string de tempo
        $timeInterval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
        
        // Soma o intervalo de tempo ao DateTime
        $dateTime->add($timeInterval);
        
        // Retorna a data e hora resultante como string
        return $dateTime->format('Y-m-d H:i:s');
    }

    public function days($ini, $qtd=10){
        if($this->is_date($ini)){
            if($this->in_string('/',$ini)){ $ini = $this->date_us($ini); }
            [$ano,$mes,$dia] = explode('-',$ini);
        
            $last = (int) $this->last_day($mes, $ano);
            $tmp=[]; $d=(int) $dia; $n=1;
        
            do {
                $tmp[] = date("Y-m-d", mktime(0,0,0,$mes,$d,$ano));
        
                if($d==$last){
                    $mes = $mes + 1;
                    $d = 0;
                    if($mes>12){
                        $ano=$ano+1;
                        $mes=1;
                    }
                    $last = (int) $this->last_day($mes, $ano);
                }
        
                $d++;
                $n++;
            } while ($n <= $qtd);
        
            return $tmp;
        }
        return FALSE;
    }

    public function days_week(array $days, array $weeks){
        $tmp = [];
        foreach($days as $day){
            $week = date("N", strtotime($day));

            if(in_array($week, $weeks)){
                $tmp[] = $day;
            }
        }

        return $tmp;
    }

    public function daysToSeconds($days) {
        // There are 24 hours in a day
        $hours = $days * 24;    
        // There are 60 minutes in an hour
        $minutes = $hours * 60;    
        // There are 60 seconds in a minute
        $seconds = $minutes * 60;
        
        return $seconds;
    }


    public function day_week_num($date){
        return date("N", strtotime($date));
    }

    public function day_week_text($date){
        $text=[1=>'SEG',2=>'TER',3=>'QUA',4=>'QUI',5=>'SEX',6=>'SAB',7=>'DOM'];
        $num = date("N", strtotime($date));
        return ($num && isset($text[$num])) ? $text[$num] : FALSE;
    }

    public function last_day($mes, $ano){
        return date("t", mktime(0,0,0,$mes,1,$ano));
    }

    public function extend(array $arr_1, array $arr_2){
        foreach ($arr_2 as $key => $val) {
            $arr_1[$key] = $val;
        }

        return $arr_1;
    }

    public function generete_number($qtd, $min, $max) {
        $numeros = [];
        // Gera números aleatórios até atingir a qtd desejada
        while (count($numeros) < $qtd) {
            $numero = rand($min, $max); // Gera um número aleatório entre $min e $max
            if (!in_array($numero, $numeros)) { // Verifica se o número já foi gerado
                $numeros[] = $numero; // Adiciona o número ao array se for único
            }
        }
        return $numeros;
    }

    public function http($url, $config=null){
        $_config = [
            'type'=>'get',
            'data'=>null,
            'header'=>['Content-Type:application/json']
        ];
        if(isset($config) && is_array($config)){
            $_config = array_replace_recursive($_config,$config);
        }

        // Inicializa o cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_config['header']);

        if($config['type']==='post'){
            // Configurações do cURL
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_config['data']);
        }

        // Executa o cURL
        $response = curl_exec($ch);

        // Fecha o cURL
        curl_close($ch);

        if(!$response){
            return false;
        }
        // Exibe a resposta
        return json_decode($response,true);
    }

    public function is_valid($val){
        $tmp = TRUE;

        if(!isset($val) || empty($val) || $val=="-" || $val=='false' || $val=='FALSE' || $val=='NULL' || $val=='null' || $val=='undefined'){
            $tmp = FALSE;
        }

        return $tmp;
    }

    public function is_date($date){
        $tmp = FALSE;
        if($this->is_valid($date)){
            if($this->in_string('/',$date)){ $date = $this->date_us($date); }
            if(in_array($date, ['0000-00-00'])){
                return $tmp;
            }

            $arr = explode('-', $date);
            $ano=$arr[0]; $mes=$arr[1];  $dia=$arr[2];

            if((int) $dia > 31 || (int) $dia <= 0){ return FALSE; }
            if((int) $mes > 12 || (int) $mes <= 0){ return FALSE; }
            if(strlen($ano) < 4){ return FALSE; }

            return TRUE;
        }

        return $tmp;
    }

    public function is_date_br($data_br){
        return $this->is_date($data_br);
    }

    public function is_datetime($val){   
        if($this->in_string('/',$val)){ $val = $this->datetime_us($val); }
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $val);
        return $dateTime && $dateTime->format('Y-m-d H:i:s') === $val;
    }

    public function is_email($email) {    
        $email = trim($email);
        // Verifica se o e-mail é válido
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true; // E-mail válido
        }
        return false; // E-mail inválido
    }

    public function is_time($time, $max=23){
        if(isset($time) && $time != "" && strstr($time, ':')){
            $t = explode(":",$time);
            $h = $t[0];
            $m = $t[1];

            if($h < 0 || $h > $max){
                return FALSE;
            }
            
            if($m < 0 || $m > 59){
                return FALSE;
            }
            
            return TRUE;
        }

        return FALSE;
    }

    public function in_string($val, $string){    
        if(is_array($val)){
            $status = TRUE;
            foreach ($val as $str){
                if(!mb_strpos($string, $str)){
                    $status = FALSE;
                    break;
                }
            }
            return $status;
        }

        return mb_strpos($string, $val);
    }

    public function time_compare($time_1,$time_2,$verificador){
        $tmp = FALSE;
        $tmp_1 = $this->time_to_minutes($time_1);
        $tmp_2 = $this->time_to_minutes($time_2);

        switch ($verificador){
            case ">":
                if($tmp_1 > $tmp_2){
                    $tmp = TRUE;
                }
                break;
            case ">=":
                if($tmp_1 >= $tmp_2){
                    $tmp = TRUE;
                }
                break;    
            case "<":
                if($tmp_1 < $tmp_2){
                    $tmp = TRUE;
                }
                break;
            case "<=":
                if($tmp_1 <= $tmp_2){
                    $tmp = TRUE;
                }
                break;    
            case "==":
                if($tmp_1 == $tmp_2){
                    $tmp = TRUE;
                }
                break;
            case "!=":
                if($tmp_1 != $tmp_2){
                    $tmp = TRUE;
                }
                break;
        }

        return $tmp;
    }

    public function time_key($time){
        $tmp = str_replace(":", "", $time);
        return (int) $tmp;
    }

    /**
     * Converte um número inteiro para formato de tempo (HH:MM:SS ou HH:MM)
     * 
     * @param int $key Número inteiro representando o tempo
     * @param bool $seconds Se true, retorna formato com segundos (HH:MM:SS), senão retorna (HH:MM)
     * @return string Tempo formatado
     */
    public function time($key, $seconds = FALSE) {
        // Converte para string e preenche com zeros à esquerda
        $key = str_pad($key, 6, '0', STR_PAD_LEFT);
        
        if ($seconds) {
            // Formato com segundos (HH:MM:SS)
            $hours = (int)substr($key, -6, 2);
            $minutes = (int)substr($key, -4, 2);
            $secs = (int)substr($key, -2, 2);
            
            // Ajusta segundos se maior que 60
            if ($secs >= 60) {
                $minutes += floor($secs / 60);
                $secs = $secs % 60;
            }
            
            // Ajusta minutos se maior que 60
            if ($minutes >= 60) {
                $hours += floor($minutes / 60);
                $minutes = $minutes % 60;
            }
            
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            // Formato sem segundos (HH:MM)
            $hours = (int)substr($key, -4, 2);
            $minutes = (int)substr($key, -2, 2);
            
            // Ajusta minutos se maior que 60
            if ($minutes >= 60) {
                $hours += floor($minutes / 60);
                $minutes = $minutes % 60;
            }
            
            return sprintf('%02d:%02d', $hours, $minutes);
        }
    }

    public function time_sub($time_1, $time_2){
        $s=FALSE;
        if(strlen($time_1)==8 || strlen($time_2)==8){
            $s=TRUE;        
        }

        $tmp_1 = $this->time_to_seconds($time_1);
        $tmp_2 = $this->time_to_seconds($time_2);

        if($tmp_1 > $tmp_2){
            $tmp = $tmp_1 - $tmp_2;
        }else{
            $tmp = $tmp_2 - $tmp_1;
        }

        return ($s) ? $this->seconds_to_time($tmp, 'H:i:s') : $this->time_to_minutes($tmp); 
    }

    public function time_sum($time_1, $time_2){
        $time_1 = $this->time_to_minutes($time_1);
        $time_2 = $this->time_to_minutes($time_2);
        
        $soma = $time_1 + $time_2;
        
        return $this->time_to_minutes($soma);
    }

    public function time_to_minutes($time){ 
        $arr = explode(':', $time);   
        // Divide a string de tempo em horas, minutos e segundos
        $hours = $arr[0];
        $minutes = $arr[1];
        $seconds = (isset($arr[2])) ? $arr[2] : false;
        // Converte horas para minutos e soma com os minutos
        $totalMinutes = ($hours * 60) + $minutes;

        if($seconds){
            $totalMinutes += $seconds / 60;
        }
        
        return $totalMinutes;
    }

    public function time_to_seconds($time){
        $arr = explode(":", $time);
        $s = 0;
        $h = $arr[0];
        $m = $arr[1];

        if(count($arr)==3){
            $s = $arr[2];
        }

        return ($h * 3600) + ($m * 60) + $s;
    }

    public function minutes_to_time($val){
        // Calcula horas, minutos e segundos
        $hours = floor($val / 60);
        $minutes = $val % 60;
        $seconds = ($val - floor($val)) * 60;

        // Formata a string de tempo
        $timeString = sprintf('%02d:%02d', $hours, $minutes);

        // Se houver segundos, adiciona ao formato
        if ($seconds > 0) {
            $timeString .= sprintf(':%02d', round($seconds));
        }

        return $timeString;
    }

    public function times_interval($ini, $fim, $frequency=5){
        if(is_int($frequency)){
            $tmp = [];
            $ini_s = $this->time_to_seconds($ini);
            $fim_s = $this->time_to_seconds($fim);
            $format = (strlen($ini)==5) ? 'H:i' : 'H:i:s';
            $frequency = $frequency * 60;

            $tmp[$ini] = $ini;        

            do {
                $ini_s = $ini_s + $frequency;            
                $time = $this->seconds_to_time($ini_s, $format);
                
                if($ini_s <= $fim_s){
                    $tmp[$time] = $time;
                }

            } while ($ini_s <= $fim_s);
            
            return $tmp;
        }
        return FALSE;
    }

    public function seconds_to_time($seconds, $format = 'H:i:s'){
        return gmdate($format, $seconds);
    }

    public function subtrai_dias($data, $num){
        return $this->date_sub($data, $num);
    }

    public function soma_dias($data, $num){    
        return $this->date_sum($data, $num);
    }

    public function value_get($name, $default=NULL){
        return (isset($_GET) && key_exists($name, $_GET)) ? $_GET[$name] : $default;
    }

    public function value_post($name, $default=NULL, $strip=TRUE){    
        return (isset($_POST) && key_exists($name, $_POST)) ? (($strip) ? strip_tags($_POST[$name]) : $_POST[$name]) : $default;
    }

    public function load_values_post(array $itens){
        $tmp = FALSE;

        if(isset($_POST)){
            $tmp = array();
            foreach ($itens as $i=>$key) {
                if(isset($_POST[$key])){
                    $tmp[$key] = $_POST[$key];
                }
            }
            if(count($tmp)==0){ $tmp = FALSE; }
        }

        return $tmp;
    }

    public function params_encode(array $itens){
        if(is_array($itens)){
            $tmp = "";
            foreach($itens as $key=>$val){
                $tmp.= "{$key}={$val}&";
            }
            return substr($tmp,0,-1);
        }

        return FALSE;
    }

    public function params_decode($url){
        $query = parse_url($url, PHP_URL_QUERY);
        if(!$query){
            return false;
        }
        parse_str($query, $params);
        return $params;
    }

    public function lowercase($string){
        $val = trim($string);
        $val = str_replace(["Ã","Á","À","É","Ê","Í","Ó","Õ","Ô","Ú","Ç"], ["ã","á","à","é","ê","í","ó","õ","ô","ú","ç"], $val);

        return mb_strtolower($val);
    }

    public function uppercase($string){
        $val = trim($string);
        $val = str_replace(["ã","á","à","é","ê","í","ó","õ","ô","ú","ç"],["Ã","Á","À","É","Ê","Í","Ó","Õ","Ô","Ú","Ç"],$val);

        return mb_strtoupper($val);
    }

    public function remove_characters($string, $space=TRUE){
        $de = ['`','"',"'",'À','Á','Ã','Â','à','á','ã','â','Ê','É','Í','í','Ó','Õ','Ô','ó','õ','ô','Ú','Ü','Ç','ç','é','ê','ú','ü'];
        $para = ['','',"",'A','A','A','A','a','a','a','a','E','E','I','i','O','O','O','o','o','o','U','U','C','c','e','e','u','u'];

        if($space){ $de[] = " "; $para[] = ""; }

        $val = trim($string);
        $val = str_replace($de,$para,$val);

        return urldecode($val);
    }

    public function json_decode($json){
        if(is_string($json)){
            $temp = str_replace("|", '"', $json);
            return json_decode($temp, TRUE);
        }

        return $json;
    }

    public function json_encode($json){
        $array = json_encode($json);
        $temp  = str_replace('"', '|', $array);

        return $temp;
    }

    public function css(array $input){
        $tmp="";
        foreach ($input as $key => $val) {
            $tmp.= "$key:$val;";
        }
        return $tmp;
    }

    public function css_array($input,$st=null){
        if(!$this->is_valid($input)){
            return false;
        }
        $result = [];
        $input = str_replace(array("\r\n", "\n", "\r"), '', trim($input));
        // Encontrar partes que estão dentro de {} e removê-las temporariamente
        preg_match_all('/\w+:\{[^}]+\}/', $input, $matches);

        if(count($matches[0])>0){
            $tmp = [];        
            foreach($matches[0] as $match){
                [$key,$val] = explode(':', $match, 2);
                
                $input = str_replace($val, $key, $input);

                $tmp[$key] = $this->css_array(trim($val, '{}'));
            }

            return $this->css_array($input, $tmp);        
        }else{
            $arr = explode(';', $input);
            foreach ($arr as $item) {
                if(!empty($item)){
                    $a = explode(':',$item);
                    if(count($a)==2){
                        $k = $a[0];
                        $v = $a[1];
        
                        if(isset($st) && isset($st[$k])){
                            $result[$k] = $st[$k];
                        }else{
                            $result[$k] = $v;
                        }
                    }             
                }            
            }

            return $result;        
        }
    }

    public function style_encode(array $value){
        $box = "";
        foreach ($value as $key => $val) {
            $box.= "{$key}:{$val};";
        }
        return $box;
    }

    public function style_decode($val){
        $tmp=[];
        $input = str_replace(array("\r\n","\n","\r"), '', trim($val));
        $arr = explode(';', $input);
        
        foreach ($arr as $item) {
            if(is_string($item) && $this->in_string(':',$item)){
                $a = explode(':',$item);
                $key = trim($a[0]);
                $tmp[$key] = trim($a[1]);
            }
        }
        return $tmp;
    }

    public function mount_html($arr, $contents=null) {
        if(is_string($arr)){
            $content = (isset($contents) && isset($contents[$arr])) ? $contents[$arr] : "";

            return "<div id='$arr'>$content</div>";
        }else if(is_array($arr)){
            $html = '';
            foreach ($arr as $k => $item) {
                if(is_string($k)){
                    $html .= "<div id='$k'>";
                        $html .= $this->mount_html($item, $contents);
                    $html .= "</div>";
                }else{
                    $html .= $this->mount_html($item, $contents);
                }
            }
            return $html;
        }
    }

    public function html_encode($html){
        $search  = array('"',"&","<",">","%","(",")");
        $replace = array("!#1","!#2","!#3","!#4","!#5","!#6","!#7");
        
        return str_replace($search, $replace, $html);
    }

    public function html_decode($html){
        $search  = array("!#1","!#2","!#3","!#4","!#5","!#6","!#7");
        $replace = array('"',"&","<",">","%","(",")");

        return str_replace($search, $replace, $html);
    }

    public function method_name($str, $separator){
        $name = "";
        $array = explode($separator, $str);

        foreach ($array as $val){
            $name.= ucfirst($val);
        }

        return $name;
    }

    public function real_to_double($valor) {
        // Remove o símbolo de moeda e espaços
        $valor = str_replace('R$', '', $valor);
        $valor = str_replace(' ', '', $valor);
        
        // Substitui o ponto por nada e a vírgula por ponto
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        
        // Converte para double
        return (double)$valor;
    }

    public function fx_hour($time, $frequencia, $limit=FALSE){
        $tmp  = FALSE;
        $freq = array(3, 5, 10, 15, 20, 30, 60);

        if($frequencia > 60){
            $hora = (int) $frequencia / 60;
            $tmp  = $this->fx_hour($time, $hora, $limit);
        }
        else if(in_array($frequencia, $freq))
        {
            $fx  = 0;
            $min = 0;
            $max = 60;

            [$h,$m] = explode(":", $time);
            
            $m = (int) $m;
            
            for($n=1; $n<=($max/$frequencia); $n++){
            $min = $n * $frequencia - 1;

            if($m <= $min){
                if($fx<10) { $fx = "0{$fx}"; }

                $tmp = "{$h}:{$fx}";
                
                if($limit){
                    $tmp.= " - {$h}:{$min}";
                }

                return $tmp;
            }
            
            $fx = $min + 1;
            }
        }

        return $tmp;
    }

function get_faixa_horaria_hora($time, $hora, $limit=FALSE){
    $TMP   = FALSE;
    $_HORA = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

    if(in_array($hora, $_HORA)){
        list($h, $m) = explode(":", $time);

        $result =       $h % $hora;
        $fx_h   = (int) $h - $result;

        if($fx_h < 10) { $fx_h = "0{$fx_h}"; }

        $TMP = "{$fx_h}:00";

        if($limit){
            $fx_h = (int) $fx_h + $hora - 1;

            if($fx_h < 10) { $fx_h = "0{$fx_h}"; }
            if($fx_h > 23) { $fx_h = "23"; }

            $TMP.= " - {$fx_h}:59";
        }
    }

    return $TMP;
}

    public function period_br($key){
        $tmp = "";

        if( isset($key) && !empty($key) ){
            switch($key)
            {
                case 1:
                case "1":
                    $tmp = "DIA ÚTIL";
                    break;
                case 2:
                case "2":
                    $tmp = "SÁBADO";
                    break;
                case 3:
                case "3":
                    $tmp = "DOMINGO";
                    break;
            }
        }
        
        return $tmp;
    }

    public function month_br($num_mes){
        $array = array(
                    "01"=>"Janeiro","02"=>"Fevereiro","03"=>"Março"   ,"04"=>"Abril"  ,"05"=>"Maio"    ,"06"=>"Junho",
                    "07"=>"Julho"  ,"08"=>"Agosto"   ,"09"=>"Setembro","10"=>"Outubro","11"=>"Novembro","12"=>"Dezembro",
                    1=>"Janeiro"   ,2=>"Fevereiro"   ,3=>"Março"      ,4=>"Abril"     ,5=>"Maio"       ,6=>"Junho",
                    7=>"Julho"     ,8=>"Agosto"      ,9=>"Setembro"   ,10=>"Outubro"  ,11=>"Novembro"  ,12=>"Dezembro"
        );

        return (isset($array[$num_mes])) ? $array[$num_mes] : NULL;
    }
 
    public function list_month_br(){
        return array(
                    "01"=>"Janeiro"     ,
                    "02"=>"Fevereiro"   ,
                    "03"=>"Março"       ,
                    "04"=>"Abril"       ,
                    "05"=>"Maio"        ,
                    "06"=>"Junho"       ,
                    "07"=>"Julho"       ,
                    "08"=>"Agosto"      ,
                    "09"=>"Setembro"    ,
                    "10"=>"Outubro"     ,
                    "11"=>"Novembro"    ,
                    "12"=>"Dezembro"
        );
    }

    public function reduce(array $obj, callable $call, $init){
        $acc = $init;
        foreach ($obj as $key => $val) {
            $acc = $call($key, $val, $acc);
        }
        return $acc;
    }

    public function create_key(){
        return md5(uniqid(time()));
    }

    public function create_id(){
    $time  = microtime(TRUE);
    $micro = sprintf("%06d", ($time - floor($time) ) * 1000000);
    $date  = new DateTime( date('Y-m-d H:i:s.'.$micro, $time) );

    return $date->format("YmdHisu");
    }

    public function uuid(){
        // Gera um UUID usando a função random_bytes para garantir a aleatoriedade
        $data = random_bytes(16);
            
        // Define os bits de versão e variante de acordo com o padrão UUID
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versão 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante RFC 4122

        // Converte os bytes em uma string UUID
        return vsprintf('%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x', array_map('ord', str_split($data)));
    }

    public function uuidv7(){
        // Gera um UUID v7 com timestamp para melhor ordenação
        $timestamp = microtime(true) * 1000; // milissegundos desde epoch
        $timestamp_ms = intval($timestamp);
        
        // Converte timestamp para 48 bits (6 bytes)
        $timestamp_bytes = pack('J', $timestamp_ms << 16); // shift left para deixar espaço para sub-millisecond
        $timestamp_bytes = substr($timestamp_bytes, 2, 6); // pega apenas os 6 bytes necessários
        
        // Gera 10 bytes aleatórios para o resto
        $random_bytes = random_bytes(10);
        
        // Monta o UUID v7
        $data = $timestamp_bytes . $random_bytes;
        
        // Define os bits de versão e variante
        $data[6] = chr(ord($data[6]) & 0x0f | 0x70); // Versão 7
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante RFC 4122
        
        // Converte os bytes em uma string UUID
        return vsprintf('%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x', array_map('ord', str_split($data)));
    }

    public function url($str, $separator = 'dash', $lowercase = FALSE){
        $replace = ($separator == 'dash') ? '-' : '_';
        $trans = array(
                        '&\#\d+?;'	=> '',
                        '&\S+?;'	=> '',
                        '\s+'		=> $replace,
                        '[^a-z0-9\-\._]'=> '',
                        $replace.'+'	=> $replace,
                        $replace.'$'	=> $replace,
                        '^'.$replace	=> $replace,
                        '\.+$'          => ''
        );

        $str = strip_tags($str);

        foreach ($trans as $key => $val){
            $str = preg_replace("#".$key."#i", $val, $str);
        }

        if ($lowercase === TRUE){
            $str = strtolower($str);
        }

        return trim(stripslashes($str));
    }
    /**
     * Converte um valor de tamanho para bytes
     * Suporta formatos como: 10MB, 20GB, 10KB, 50000
     * 
     * @param string|int $size Tamanho a ser convertido
     * @return int Tamanho em bytes
     */
    public function to_bytes($size) {
        // Se for um número, retorna direto
        if (is_numeric($size)) {
            return (int) $size;
        }

        // Remove espaços e converte para maiúsculo
        $size = trim(strtoupper($size));
        
        // Define os multiplicadores para cada unidade
        $units = [
            'B' => 1,
            'KB' => 1024,
            'MB' => 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
            'TB' => 1024 * 1024 * 1024 * 1024,
            'PB' => 1024 * 1024 * 1024 * 1024 * 1024
        ];
        
        // Extrai o número e a unidade
        if (preg_match('/^([\d.]+)\s*([A-Z]+)$/', $size, $matches)) {
            $number = (float) $matches[1];
            $unit = $matches[2];
            
            // Verifica se a unidade existe
            if (isset($units[$unit])) {
                return (int) ($number * $units[$unit]);
            }
        }
        
        // Se não conseguir converter, retorna 0
        return 0;
    }
    /**
     * Converte um valor para Kilobytes (KB)
     * 
     * @param string|int $size Tamanho a ser convertido (ex: 1024, "1MB", "2GB")
     * @param int $decimals Número de casas decimais
     * @return float Tamanho em KB
     */
    public function to_kb($size, $decimals = 2) {
        $bytes = $this->to_bytes($size);
        return round($bytes / 1024, $decimals);
    }
    /**
     * Converte um valor para Megabytes (MB)
     * 
     * @param string|int $size Tamanho a ser convertido (ex: 1048576, "1GB", "500KB")
     * @param int $decimals Número de casas decimais
     * @return float Tamanho em MB
     */
    public function to_mb($size, $decimals = 2) {
        $bytes = $this->to_bytes($size);
        return round($bytes / (1024 * 1024), $decimals);
    }
    /**
     * Converte um valor para Gigabytes (GB)
     * 
     * @param string|int $size Tamanho a ser convertido (ex: 1073741824, "1TB", "500MB")
     * @param int $decimals Número de casas decimais
     * @return float Tamanho em GB
     */
    public function to_gb($size, $decimals = 2) {
        $bytes = $this->to_bytes($size);
        return round($bytes / (1024 * 1024 * 1024), $decimals);
    }
    /**
     * Converte um valor para a unidade mais apropriada (B, KB, MB, GB, TB)
     * 
     * @param string|int $size Tamanho a ser convertido
     * @param int $decimals Número de casas decimais
     * @return string Tamanho formatado com unidade
     */
    public function format_size($size, $decimals = 2) {
        $bytes = $this->to_bytes($size);
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        
        // Limita o power ao número de unidades disponíveis
        $power = min($power, count($units) - 1);
        
        $value = $bytes / pow(1024, $power);
        return round($value, $decimals) . ' ' . $units[$power];
    }

    /**
     * Substitui as chaves de um array usando um mapeamento fornecido
     * 
     * @param array $data Array com os dados originais
     * @param array $keyMapping Array com o mapeamento de chaves (chave_original => nova_chave)
     * @return array Array com as chaves substituídas
     * 
     */
    public function replace_keys($data, $keyMapping) {
        if (!is_array($data) || !is_array($keyMapping)) {
            return $data;
        }
        
        $result = [];
        
        foreach ($data as $key => $value) {
            // Se a chave existe no mapeamento, usa a nova chave
            if (isset($keyMapping[$key])) {
                $result[$keyMapping[$key]] = $value;
            } else {
                // Se não existe no mapeamento, mantém a chave original
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Função auxiliar recursiva para remover chaves de níveis específicos
     * 
     * @param array $data Array de dados
     * @param int $targetLevel Nível alvo para remoção
     * @param int $currentLevel Nível atual
     * @return array Array processado
     */
    private function remove_keys_recursive($data, $targetLevel, $currentLevel) {
        if (!is_array($data)) {
            return $data;
        }
        
        if ($currentLevel === $targetLevel) {
            // Se chegou no nível alvo, remove as chaves e merge
            $result = [];
            foreach ($data as $item) {
                if (is_array($item)) {
                    $result = array_merge($result, $item);
                }
            }
            return $result;
        }
        
        // Continua recursivamente para o próximo nível
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->remove_keys_recursive($value, $targetLevel, $currentLevel + 1);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
}