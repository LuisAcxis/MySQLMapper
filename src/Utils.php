<?php

namespace AppsLine\MySQLMapper;

class Utils {
    static function isDefined($var) {
        return isset($var);
    }

    static function cleanString($string) {
        $string = preg_replace('[áàâãªä@]', 'a', $string);
        $string = preg_replace('[ÁÀÂÃÄ]', 'A', $string);
        $string = preg_replace('[éèêë]', 'e', $string);
        $string = preg_replace('[ÉÈÊË]', 'E', $string);
        $string = preg_replace('[íìîï]', 'i', $string);
        $string = preg_replace('[ÍÌÎÏ]', 'I', $string);
        $string = preg_replace('[óòôõºö]', 'o', $string);
        $string = preg_replace('[ÓÒÔÕÖ]', 'O', $string);
        $string = preg_replace('[úùûü]', 'u', $string);
        $string = preg_replace('[ÚÙÛÜ]', 'U', $string);
        $string = str_replace('[¿?]', '_', $string);
        $string = str_replace('ñ', 'n', $string);
        $string = str_replace('Ñ', 'N', $string);
        
        return $string;
    }

    static function constExist($class, $name) {
        try {
            $constantReflex = new \ReflectionClass($class);
            $constants = $constantReflex -> getConstants();
            return array_key_exists($name, $constants);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    static function getConst($class, $name) {
        try {
            $constantReflex = new \ReflectionClassConstant($class, $name);
            return $constantReflex->getValue();
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    static function isRegularExpression($string) {
        return @preg_match($string, '') !== FALSE;
    }

    static function strReplaceFirst($search, $replace, $subject) {
        $search = '/'.preg_quote($search, '/').'/';
        return preg_replace($search, $replace, $subject, 1);
    }
}