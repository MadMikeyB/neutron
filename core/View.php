<?php

namespace Neutron;

class View
{
    public static function get($name, $data = [])
    {
        extract($data);
        $tpl = '';
        $tpl .= static::getHeader();
        $tpl .= require VIEW_DIR.$name.'.tpl.php';
        $tpl .= static::getFooter();

        return $tpl;
    }

    public static function getTitle()
    {
        echo getenv('APP_TITLE');
    }

    public static function getHeader()
    {
        return require VIEW_DIR.'header.tpl.php';
    }

    public static function getFooter()
    {
        return require VIEW_DIR.'footer.tpl.php';
    }
}
