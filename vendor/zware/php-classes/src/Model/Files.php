<?php

namespace Zware\Model;


use \Zware\Model;


class Files extends Model{

    //Local Arquivos
    
    //Base arquivos
    const RAIZ_UPLOADS = "views/uploads/";
    
    //Fotos do usuário
    const RAIZ_FOTOS = "upics/";
    //Miniaturas de fotos do perfil 
    const RAIZ_FOTOSPERFIL_P = "profilex160/";
    const RAIZ_FOTOSEMPRESA_P = "empresax160/";
    const RAIZ_FOTOSDIVISAO_P = "divisaox160/";
    
    const RAIZ_TEMP_FILES = "temp/";
    
    //Ferramentas extras
    const MASCARAPASTAFINAL = "000000";

    //Tamanho máximo (de largura ou altura) para as imagens pequenas (em pixels)
    const TAMANHOLIMITEPX_P = 160;
    //Tamanho máximo (de largura ou altura) para as imagens grandes (em pixels)
    const TAMANHOLIMITEPX_G = 1600;

    //Quantidade máxima de imagens por pasta
    const QUANT_FILES_PER_PASTA = 1000;

    public function __construct(){
        //cria as pastas se não existirem

        //Pasta raiz de uploads
        $pasta = $_SERVER['DOCUMENT_ROOT']."/".Files::RAIZ_UPLOADS;
        if(!is_dir($pasta)){
            mkdir($pasta);            
        }

        //Pasta raiz fotos
        $pasta = Files::RaizFotos();
        if(!is_dir($pasta)){            
            mkdir($pasta);            
        }

        //Pasta de fotos de perfil pequenas
        $pasta = Files::RaizFotosPerfilP();
        if(!is_dir($pasta)){
            mkdir($pasta);            
        }        
    }

    public static function RaizFotos(){        
        return  Files::RAIZ_UPLOADS.Files::RAIZ_FOTOS;
    }

    public static function RaizFotosPerfilP(){        
        return Files::RaizFotos().Files::RAIZ_FOTOSPERFIL_P;        
    }

    public static function RaizFotosEmpresaP(){        
        return Files::RaizFotos().Files::RAIZ_FOTOSEMPRESA_P;        
    }

    public static function RaizFotosDivisaoP(){        
        return Files::RaizFotos().Files::RAIZ_FOTOSDIVISAO_P;        
    }

    public static function RaizFotosTemp(){        
        return Files::RaizFotos().Files::RAIZ_TEMP_FILES;        
    }

    public function SelecionarCaminhoFotoPerfil($foto = ""){       
        $caminhoBase = Files::RaizFotosPerfilP();        
        return Files::selecionaPastaDestino($caminhoBase, $foto);
    }

    public function SelecionarCaminhoFotoEmpresa($foto = ""){
       
        $caminhoBase = Files::RaizFotosEmpresaP();
        return Files::selecionaPastaDestino($caminhoBase, $foto);
    }
    
    public function SelecionarCaminhoFotoDivisao($foto = ""){       
        $caminhoBase = Files::RaizFotosDivisaoP();
        return Files::selecionaPastaDestino($caminhoBase, $foto);
    }

    public static function selecionaPastaDestino($caminhoBase, $foto){
        $retorno = "";       
        $pastaAtual = 1;        
        $buscando = true;
                
        if(!is_dir($caminhoBase)){
            mkdir($caminhoBase);
        }

        while($buscando){
            
            $pasta = $caminhoBase.trim(sprintf("%'.06d\n", $pastaAtual))."/";            
            //Caso não exista a pasta, criá-la e retornar seu caminho
            if(!is_dir($pasta)){
                mkdir($pasta);
                $retorno = $pasta;
                break;
            }
            else
            {
                $files = scandir($pasta);
                $num_files = count($files) - 2;
                if($num_files >= Files::QUANT_FILES_PER_PASTA)
                {
                    $pasta = $caminhoBase.trim(sprintf("%'.06d\n", $pastaAtual+1))."/";   
                    if(!is_dir($pasta)){
                        mkdir($pasta);
                        $retorno = $pasta;
                        break;
                    }
                }
                else
                {
                    $retorno = $pasta;
                    break;
                }
            }
            $pastaAtual ++;
        }   

        return $pasta.$foto;
    }

    public static function GeraNomeFoto($somenteNumeros = false, $quantFragmentos = array(8,11,7), $separador = "_", $sufixo = ".jpg"){
        
        $caracteres = $somenteNumeros?'0123456789':'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $retorno = "";     
        
        $primeiro = true;
        foreach($quantFragmentos as $num){    

            if(!$primeiro)
            {
                $retorno .= $separador;
            }
            
            $numero = Funcoes::RandomString($num, $caracteres);

            $retorno .= $numero;
            
            $primeiro = false;
        }

        return $retorno.$sufixo;
    }

    public static function geraNovoNomeFotoEmpresa(){

        $nomeFoto = Files::GeraNomeFoto(TRUE,array(7,4,8), "_");        
        
        return Files::SelecionarCaminhoFotoEmpresa($nomeFoto);
    }

    public function uploadFotoPerfil($imagem, $recortar = true){     
       
        $tamanho_foto = Files::TAMANHOLIMITEPX_P;

        $nomeFoto = Files::GeraNomeFoto(TRUE,array(7,4,8), "_", "");

        $caminho_foto = $this->SelecionarCaminhoFotoPerfil($nomeFoto);

        $this->image_resize($imagem, $caminho_foto.".jpg", $tamanho_foto,$tamanho_foto,$recortar);

        return $caminho_foto.".jpg";
    }

    public function uploadFotoEmpresa($imagem, $recortar = true){     

        $tamanho_foto = Files::TAMANHOLIMITEPX_P;

        $nomeFoto = Files::GeraNomeFoto(TRUE,array(7,4,8), "_", "");        
        
        $caminho_foto = Files::SelecionarCaminhoFotoEmpresa($nomeFoto);

        Files::image_resize($imagem, $caminho_foto.".jpg", $tamanho_foto, $tamanho_foto, $recortar);        

        Files::deletaFotoTemp($imagem);

        return $caminho_foto.".jpg";
    }   

    public function uploadFotoDivisao($imagem, $recortar = true){
        
        $tamanho_foto = Files::TAMANHOLIMITEPX_P;

        $nomeFoto = Files::GeraNomeFoto(TRUE,array(7,4,8), "_", "");        
        
        $caminho_foto = Files::SelecionarCaminhoFotoDivisao($nomeFoto);

        Files::image_resize($imagem, $caminho_foto.".jpg", $tamanho_foto, $tamanho_foto, $recortar);        

        Files::deletaFotoTemp($imagem);

        return $caminho_foto.".jpg";
    }   
    
    public static function deletaFotoTemp($foto){
        $uploadfile = Files::RaizFotosTemp() . basename($foto['name']);

        try{
            unlink($uploadfile);
        }catch(\Exception $e){

        }

    }

    public static function image_resize($srcImg, $dst, $width, $height, $crop=0){
       
        //Salva arquivo temporário        
        $uploadfile = Files::RaizFotosTemp() . basename($srcImg['name']);
        
        $ext = Funcoes::GetExtension(basename($srcImg['name']));

        $arquivoTemporario = Files::RaizFotosTemp() . Files::GeraNomeFoto(TRUE,array(7,4,8), "_", "").".".$ext;
        
        $src = "";

        if (move_uploaded_file($srcImg['tmp_name'], $arquivoTemporario)) {
            $src = $arquivoTemporario;
        } else {
            throw new \Exception("Imagem inválida, por favor, verifique! Arquivo = ". $arquivoTemporario);           
        }       

        if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";
      
        $type = strtolower(substr(strrchr($src,"."),1));
        if($type == 'jpeg') $type = 'jpg';
        switch($type){
          case 'bmp': $img = imagecreatefromwbmp($src); break;
          case 'gif': $img = imagecreatefromgif($src); break;
          case 'jpg': $img = imagecreatefromjpeg($src); break;
          case 'png': $img = imagecreatefrompng($src); break;          
          default : return "Unsupported picture type!";
        }
      
        // resize
        if($crop){
          if($w < $width or $h < $height) return "Picture is too small!";
          $ratio = max($width/$w, $height/$h);
          $h = $height / $ratio;
          $x = ($w - $width / $ratio) / 2;
          $y=0;
          $w = $width / $ratio;
        }
        else{
          if($w < $width and $h < $height) return "Picture is too small!";
          $ratio = min($width/$w, $height/$h);
          $width = $w * $ratio;
          $height = $h * $ratio;
          $y = ($h - $height / $ratio) / 2;
          $x = 0;
        }
      
        $new = imagecreatetruecolor($width, $height);
      
        // preserve transparency
        if($type == "gif" or $type == "png"){
          imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
          imagealphablending($new, false);
          imagesavealpha($new, true);
        }
      
        imagecopyresampled($new, $img, 0, 0, $x, $y, $width, $height, $w, $h);
      
        switch($type){
          case 'bmp': imagewbmp($new, $dst); break;
          case 'gif': imagegif($new, $dst); break;
          case 'jpg': imagejpeg($new, $dst); break;
          case 'png': imagepng($new, $dst); break;
        }

        unlink($arquivoTemporario);

        return true;
      }

}
