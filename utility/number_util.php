<?php

namespace eru\nczone\utility;

class number_util
{
    public static function cmp($num1, $num2): int
    {
        if ($num1 == $num2) {
            return 0;
        }
        return $num1 < $num2 ? 1 : -1;
    }
}
