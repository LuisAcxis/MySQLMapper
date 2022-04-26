<?php

namespace AppsLine\MySQLMapper\Test;

abstract class Test {
    private static $tests = [];

    static public function create($nameTest, $fn) {
        if(array_search($nameTest, self :: $tests) === false) {
            self :: $tests[] = $nameTest;
        } else {
            echo '<span style="color:red">② Repeated Test</span> -> ';
        }

        $no_calls_success = 0;
        $success = function() use ($nameTest, &$no_calls_success) {
            $no_calls_success++;
            echo '<span style="color:green">✔</span> ' . $nameTest . '<br>';
        };

        $no_calls_error = 0;
        $error = function() use ($nameTest, &$no_calls_error) {
            $no_calls_error++;
            echo '<span style="color:red">✘</span> ' . $nameTest . '<br>';
        };

        $warning = function() use ($nameTest) {
            echo '<span style="color:orange">▲</span> ' . $nameTest . '<br>';
        };

        return function() use($fn, $success, &$no_calls_success, $error, &$no_calls_error, $warning) {
            $fn($success, $error);

            if($no_calls_success === 0 && $no_calls_error === 0) {
                $warning();
            }
        };
    }

    static public function debugger($object, $extend = false) {
        echo '<pre>';
        if($extend)
            var_dump($object);
        else
            print_r($object);
        echo '</pre>';
        exit;
    }
}