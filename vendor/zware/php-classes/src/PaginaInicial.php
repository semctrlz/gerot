<?php

namespace Zware;

class PaginaInicial extends Pagina{

    public function __construct($values = array(array()), $local, $opts = array(array()), $tpl_dir = "/views/inicial/")
    {
        parent::__construct($values, $local, $opts, $tpl_dir);
    }
}

?>