<?php

namespace Neutron;

class View
{
    public static function get($name, $data = [])
    {
        extract($data);

        return require APP_DIR.'/views/'.$name.'.tpl.php';
    }
}
