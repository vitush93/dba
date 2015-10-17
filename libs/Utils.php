<?php

namespace Libs;


class Utils
{
    /**
     * @param string $data comma-delimited string.
     * @return array|string array of items.
     */
    public static function safeExplodeByComma($data)
    {
        $data = trim($data, ' ,');
        $data = explode(',', $data);
        array_walk($data, function (&$value, $key) {
            $value = trim($value, ' ,');
        });
        $data = array_values(array_unique(array_filter($data)));

        return $data;
    }
}