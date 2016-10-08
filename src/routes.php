<?php

namespace yvz;

class Route
{
    private static $error = true;
    private static $extension = null;
    private static $case_sensitive = true;
    /**
     * Route settings
     * @param array $config
     */
    public static function config($config=array())
    {
        if(is_array($config))
        {
            if(isset($config["extension"]))
            {
                self::$extension = $config["extension"];
            }
            if(isset($config["case_sensitive"]))
            {
                self::$case_sensitive = $config["case_sensitive"];
            }
        }else
        {
            echo "<span style='color:#F00;width:100%;'>Config metodu parametre olarak sadece dizi değerler alabilir!</span>";
            exit;
        }
    }

    /**
     * @param string $pattern
     * @param function|string $function
     */
    public static function get($pattern, $function)
    {
        if(!is_string($pattern))
        {
            echo "<span style='color:#F00;width:100%;'>Get metodunun ilk parametresi string değerler alabilir!</span>";
            exit;
        }if (!is_string($function) && !is_callable($function)) {
        echo "<span style='color:#F00;width:100%;'>Get metodunun ikinci parametresi string bir değer veya fonksiyon olmalıdır!</span>";
        exit;
    }
        if(self::$extension != null && $pattern!="/")
        {
            $pattern = $pattern.".".self::$extension;
        }
        if($pattern !="/")
        {
            $pattern = "/".$pattern;
        }
        self::run($pattern,$function,"GET");
    }
    /**
     * @param string $pattern
     * @param function|string $function
     */
    public static function post($pattern, $function)
    {
        if(!is_string($pattern))
        {
            echo "<span style='color:#F00;width:100%;'>Post metodunun ilk parametresi string değerler alabilir!</span>";
            exit;
        }if (!is_string($function) && !is_callable($function)) {
        echo "<span style='color:#F00;width:100%;'>Post metodunun ikinci parametresi string bir değer veya fonksiyon olmalıdır!</span>";
        exit;
    }
        if(self::$extension != null && $pattern!="/")
        {
            $pattern = $pattern.".".self::$extension;
        }
        if($pattern !="/")
        {
            $pattern = "/".$pattern;
        }
        self::run($pattern,$function,"POST");
    }
    /**
     * @param function $function
     */
    public static function error($function)
    {
        if(self::$error == true)
        {
            call_user_func($function);
        }
    }
    /**
     * @param  string $pattern
     * @param  function $function
     * @param  string $method
     */
    private static function run($pattern, $function, $method)
    {
        if(empty($pattern) || empty($function) || empty($method))
        {
            echo "<span style='color:#F00;width:100%;'>Parametre eksik!</span>";
            exit;
        }

        $path_info = empty($_SERVER["PATH_INFO"]) ? "/" : $_SERVER["PATH_INFO"];
        $pattern = str_replace("(:any)", "(.*?)", $pattern);
        $pattern = str_replace("(:number)", "(\d+)", $pattern);
        if(self::$case_sensitive == false)
        {
            $pattern = "#^$pattern$#";
        }else
        {
            $pattern = "#^$pattern$#i";
        }

        if(preg_match($pattern,$path_info, $param) &&  $_SERVER["REQUEST_METHOD"]==$method)
        {
            if(is_callable($function))
            {
                array_shift($param);
                call_user_func_array($function, $param);
            }else if(is_string($function))
            {
                $control = explode("@",$function);

                if(count($control) == 1)
                {
                    if(is_callable($function))
                    {
                        call_user_func($function);
                    }else
                    {
                        echo "<span style='color:#F00;width:100%;'>Fonksiyon bulunamadı!</span>";
                        exit;
                    }
                }else
                {
                    if(method_exists($control[0], $control[1]))
                    {
                        array_shift($param);
                        $class = new $control[0]();
                        call_user_func_array(array($class,$control[1]), $param);
                    }else
                    {
                        echo "<span style='color:#F00;width:100%;'>Sınıf bulunamadı!</span>";
                        exit;
                    }

                }

            }
            self::$error = false;
        }
    }
}
