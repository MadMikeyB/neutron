<?php

namespace Neutron;

use \Exception;

class View
{
    public static function get($name, $data = [])
    {
        $tpl = '';
        $tpl .= static::getHeader();
        $tpl .= static::getTemplate($name, $data);
        $tpl .= static::getFooter();

        return $tpl;
    }

    public static function getTitle()
    {
        echo getenv('APP_TITLE');
    }

    public static function getTemplate($name, $data = [])
    {
    	if ( file_exists(VIEW_DIR . $name . '.tpl.php') )
    	{
	        extract($data);
    		return require VIEW_DIR . $name . '.tpl.php';
    	}
    	else
    	{
    		throw new Exception($name . '.tpl.php not found');
    	}
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
