<?

class Library_Html_Util {

        function build_drop_down_options($selected = 0, $arr_options) {
                for($n = 0; $n < count($arr_options);$n++) {
                        $key = $arr_options[$n]['key'];
                        $value = $arr_options[$n]['value'];
                        $is_selected = "";
                        if (is_array($selected)) {
                                if (in_array($key, $selected)) {
                                        $is_selected = " selected";
                                }
                        } else {
                                if ($selected == $key) {
                                        $is_selected = " selected";
                                }
                        }
                        $s.= "<option value=\"".$key."\" $is_selected>".$value."</option>\n";
                }
                return $s;
        }
}

?>
