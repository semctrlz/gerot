<?php

namespace Zware\Model;

use \Zware\Mailer;
use \Zware\Model;

class Funcoes extends Model {    

    public static function RandomString($quant, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $randstring = '';
        for ($i = 0; $i < $quant; $i++) {
            $randstring .= $characters[rand(0, strlen($characters) -1)];
        }
        return $randstring;
    }    

    public static function preparaParaUrl($texto){
        
        $retorno = Funcoes::removeAcentos($texto);
        $retorno = Funcoes::removeNonAlfaNumeric($retorno);
        $retorno = strtolower($retorno);
        $retorno = str_replace(" ", "_",trim($retorno));

        return $retorno;
    }

    public static function removeAcentos($texto){
        $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
        $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U');
        return str_replace($comAcentos, $semAcentos, $texto);
    }

    public static function removeNonAlfaNumeric($texto){
        $limpa = preg_replace('/[^\p{L}\p{N}\.]/', '', utf8_decode($texto) );
        return $limpa;
    }

    public static function prepararParaBanco(string $t, bool $trim = false, bool $upper = false){
        
        $texto = $t;

        if($upper){
            $texto = mb_strtoupper($t, 'UTF-8');   
        }
        $texto = addslashes($texto);
        $caracteresProibidos = ['%','='];
        $texto = str_replace($caracteresProibidos,'',$texto);

        if($trim){
            $texto = trim($texto);
        }

        return $texto;
    }

    public static function GetExtension(string $arquivo){
        return pathinfo($arquivo, PATHINFO_EXTENSION);
    }

    public static function separaTexto(string $texto, string $separador = ";"){
        return explode($separador, $texto);
    }

    public static function FormataNomeProprio($nome, $encodado = true)
    {
        if($encodado){
            $nome = utf8_decode($nome);
        }
        $saida = "";
        $encoding = $encoding = 'UTF-8';
        $nome = mb_convert_case($nome, MB_CASE_LOWER, $encoding); // Converter o nome todo para minúsculo
        $nome = explode(" ", $nome); // Separa o nome por espaços
        for ($i = 0; $i < count($nome); $i++) {

            // Tratar cada palavra do nome
            if ($nome[$i] == "de" or $nome[$i] == "da" or $nome[$i] == "e" or $nome[$i] == "dos" or $nome[$i] == "do") {
                $saida .= $nome[$i] . ' '; // Se a palavra estiver dentro das complementares mostrar toda em minúsculo
            } else {
                $saida .= ucfirst($nome[$i]) . ' '; // Se for um nome, mostrar a primeira letra maiúscula
            }
        }
        return trim($saida);
    }

}