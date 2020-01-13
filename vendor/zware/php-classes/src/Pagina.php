<?php

namespace Zware;

use Rain\Tpl;
class Pagina {

    private $tpl;
    private $options = [];
    private $defaults = [
        "data"=>[]
    ];        

    public function __construct($values = array(array()), $local, $opts = array(array()), $tpl_dir = "/views/contents/com login/"){         
        $this->options = array_merge($this->defaults, $opts);

        $config = array(
            "tpl_dir"   => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"     => True
        );

        $valores = array_merge_recursive($opts, $values);
        
        Tpl::configure($config);

        $this->tpl = new Tpl();
        
        foreach ($values as $key => $value) 
        {				
            $this->tpl->assign($key, $value);           
        }

        foreach ($opts as $key => $value) 
        {				
            $this->tpl->assign($key, $value);            
        }

        
        $this->tpl->assign("local", $local);

		$this->tpl->draw('header', false);
        
    }

    public function setTpl($tplname, $data = array(), $returnHTML = false){
        
        $this->setData($data);
		return $this->tpl->draw($tplname, $returnHTML);
    }

    private function setData($data = array()){
        foreach($data as $key => $value){
            $this->tpl->assign($key, $value);
        }
    }

    public function __destruct()
    {       
        $this->tpl->draw("footer");
    }
}