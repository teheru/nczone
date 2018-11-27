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

    /**
     * @param $num1
     * @param $num2
     * @return float|int
     */
    public static function diff($num1, $num2)
    {
        return \abs($num1 - $num2);
    }
}
