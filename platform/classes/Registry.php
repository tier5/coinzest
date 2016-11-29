<?php

class Registry {

        static $val;

        public static function get($key) {
                return self::$val[$key];
        }

        public static function set($key, $value) {
                self::$val[$key] = $value;
        }

}
?>
