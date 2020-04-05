<?php

use Zware\Model\Chaves;


function FormataNomeProprio($nome, $encodado = true)
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

function FormataData_mmm_AAAA($data){

    $d = date_parse_from_format("Y-m-d", $data);
    $ano = $d["year"];
    $mes = "";
    switch($d["month"]){
        case 1:
            $mes = "Jan.";
        break;
        case 2:
            $mes = "Fev.";
        break;
        case 3:
            $mes = "Mar.";
        break;
        case 4:
            $mes = "Abr.";
        break;
        case 5:
            $mes = "Mai.";
        break;
        case 6:
            $mes = "Jun.";
        break;
        case 7:
            $mes = "Jul.";
        break;
        case 8:
            $mes = "Ago.";
        break;
        case 9:
            $mes = "Set.";
        break;
        case 10:
            $mes = "Out.";
        break;
        case 11:
            $mes ="Nov.";
        break;
        case 12:
            $mes ="Dez.";
        break;
    }

    if(isset($ano) && $mes != ""){
        return "$mes $ano";
    }else{
        return "";
    }

}

function formataData($data){
    if($data == ""){
        return "";
    }
    $st = strtotime(str_replace("/","-",$data));
    $d = date_parse_from_format("Y-m-d", $data);
    return date('d/m/Y',$st);
}

function formataDataHora($data){
	if($data == ""){
			return "";
	}
	$st = strtotime(str_replace("/","-",$data));
	$d = date_parse_from_format("Y-m-d", $data);
	return date('d/m/Y - H:i',$st);
}

function obtemFotoPerfil($imagem){
    $img = isset($imagem)?$imagem:"";
    return $img!=""?$img:"views/uploads/upics/noprofilepicP.jpg";

}

function obtemFotoEmpresa($imagem){
    $img = isset($imagem)?$imagem:"";
    return $img!=""?$img:"views/uploads/upics/noempresapicP.jpg";
}

function imagem($imagem){
    return $imagem?$imagem:"https://www.zware.com.br/views/uploads/upics/base.jpg";
}






