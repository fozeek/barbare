<?php

namespace Barbare\Framework\Util;

class Debug {

    public static function d($data, $end = false) {
        echo '<pre>';
        var_dump($data);
        if($end) {
            var_dump(debug_print_backtrace());
        }
        echo '</pre>';
    }

}