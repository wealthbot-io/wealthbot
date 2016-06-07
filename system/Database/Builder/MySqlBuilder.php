<?php

namespace Database\Builder;

class MySqlBuilder
{
    protected static $lastQuery; 
    
    /**
     * @param $str
     * @param bool $doubleQuote
     * @return int|string
     */
    protected static function quote($str, $doubleQuote = false)
    {
        if ($str instanceof \Expr || $str instanceof self) {
            return $str->__toString();
        }

        if (is_object($str)) {
            $str .= '';
        }

        if (true === $str) {
            return 1;
        }

        if (false === $str) {
            return 0;
        }

        if (is_null($str)) {
            return gettype($str);
        }
        
        if (is_array($str)) {
            foreach ($str as &$item) {
                $item = self::quote($item, $doubleQuote);
            }
            return $str;
        }

        switch (gettype($str)) {
            case 'integer':
            case 'double':
                return $str;

            default:
                if (is_numeric($str)) {
                    return $str;
                }
                $char = $doubleQuote ? '"' : "'";
                return $char . addslashes($str) . $char;

        }
    }

    /**
     * @param $str
     * @param array $parameters
     * @return string
     */
    public static function bind($str, $parameters)
    {
        if (!is_array($parameters)) {
            $parameters = (array) $parameters;
        }

        foreach ($parameters as &$param) {
            if (is_array($param)) {
                $param = implode(', ', self::quote($param));
            } else {
                $param = self::quote($param);
            }
        }

        if (($questionMarkCount = substr_count($str, '?')) > ($paramCount = count($parameters))) {
            $last = end($parameters);
            $parameters = array_merge($parameters, array_fill(0, $questionMarkCount - $paramCount, $last));
        }

        $str = str_replace('?', '%s', $str);

        if ($questionMarkCount == 1 && !is_array(current($parameters))) {
            $parameters = implode(', ', $parameters);
        }

        static::$lastQuery = vsprintf($str, $parameters);

        return static::$lastQuery;
    }
    
    public static function getLastQuery()
    {
        return static::$lastQuery;
    }
}