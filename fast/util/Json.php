<?php

namespace fast\util;


use fast\Exception;

class Json
{
    /**
     * json_decode
     * @param $json
     * @param false $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     * @throws Exception
     */
    public static function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * json_encode
     * @param $value
     * @param int $options
     * @param int $depth
     * @return false|string
     * @throws Exception
     */
    public static function encode($value, $options = 0, $depth = 512)
    {
        $json = \json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return $json;
    }
}