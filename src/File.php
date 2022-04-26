<?php

namespace AppsLine\MySQLMapper;

use AppsLine\MySQLMapper\Utils;

use AppsLine\MySQLMapper\Exception\FileException;

class File {

    public $path;

    function __construct($path) {
        $this -> path = $path;
    }
    
    public function read($path = null) {
        $path = Utils :: isDefined($path) ? $path : $this -> path;

        if (is_file($path) && is_readable($path)) {
            try {
                $sql_query = fread(fopen($path, 'r'), filesize($path));
                $sql_query = $this -> remove_remarks($sql_query);
                $sql_query = $this -> split_sql_file($sql_query, ';');
                return $sql_query;
            } catch (\Exception $e) {
                throw new FileException($e -> getMessage());
            }
        }

        throw new FileException('El archivo '.$path.' no es un archivo valido');
    }

    private function remove_remarks($sql) {
        $lines = explode("\n", $sql);
        $sql = "";
        $linecount = count($lines);
        $output = "";
    
        for($i = 0; $i < $linecount; $i++) {
            if(($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
                if(isset($lines[$i][0]) && $lines[$i][0] != "#") {
                    $output .= $lines[$i] . "\n";
                }
                else {
                    $output .= "\n";
                }
                $lines[$i] = "";
            }
        }
        return $output;
    }
    
    private function split_sql_file($sql, $delimiter) {
        $tokens = explode($delimiter, $sql);
        $sql = "";
        $output = array();
        $matches = array();
        $token_count = count($tokens);

        for($i = 0; $i < $token_count; $i++) {
            if(($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
                $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
                $unescaped_quotes = $total_quotes - $escaped_quotes;
    
                if(($unescaped_quotes % 2) == 0) {
                    $output[] = $tokens[$i];
                    $tokens[$i] = "";
                }
                else {
                    $temp = $tokens[$i] . $delimiter;
                    $tokens[$i] = "";
                    $complete_stmt = false;
        
                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                        $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                        $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
                        $unescaped_quotes = $total_quotes - $escaped_quotes;
        
                        if(($unescaped_quotes % 2) == 1) {
                            $output[] = $temp . $tokens[$j];
                            $tokens[$j] = "";
                            $temp = "";
                            $complete_stmt = true;
                            $i = $j;
                        }
                        else {
                            $temp .= $tokens[$j] . $delimiter;
                            $tokens[$j] = "";
                        }
                    }
                }
            }
        }
        return $output;
    }

    private function remove_comments(&$output) {
        $lines = explode("\n", $output);
        $output = "";
    
        $linecount = count($lines);
    
        $in_comment = false;
        for($i = 0; $i < $linecount; $i++) {
            if(preg_match("/^\/\*/", preg_quote($lines[$i]))){
                $in_comment = true;
            }
    
            if(!$in_comment) {
                $output .= $lines[$i] . "\n";
            }

            if(preg_match("/\*\/$/", preg_quote($lines[$i]))) {
                $in_comment = false;
            }
        }
        unset($lines);
        return $output;
    }
}