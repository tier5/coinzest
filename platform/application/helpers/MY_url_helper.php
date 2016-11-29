<?php
function anchor($uri = '', $title = '', $attributes = '')
{
    $title = (string) $title;

    if ( ! is_array($uri))
    {
        $site_url = ( ! preg_match('!^\w+://! i', $uri)) ? ci_site_url($uri) : $uri;
    }
    else
    {
        $site_url = ci_site_url($uri);
    }

    if ($title == '')
    {
        $title = $site_url;
    }

    if ($attributes != '')
    {
        $attributes = _parse_attributes($attributes);
    }

    return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
}


if ( ! function_exists('ci_site_url'))
{
    function ci_site_url($uri = '')
    {
        $CI =& get_instance();
        return $CI->config->site_url($uri);
    }
}

function current_url()
{
    $CI =& get_instance();
    return $CI->config->ci_site_url($CI->uri->uri_string());
}


function anchor_popup($uri = '', $title = '', $attributes = FALSE)
{
    $title = (string) $title;

    $site_url = ( ! preg_match('!^\w+://! i', $uri)) ? ci_site_url($uri) : $uri;

    if ($title == '')
    {
        $title = $site_url;
    }

    if ($attributes === FALSE)
    {
        return "<a href='javascript:void(0);' onclick=\"window.open('".$site_url."', '_blank');\">".$title."</a>";
    }

    if ( ! is_array($attributes))
    {
        $attributes = array();
    }

    foreach (array('width' => '800', 'height' => '600', 'scrollbars' => 'yes', 'status' => 'yes', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0', ) as $key => $val)
    {
        $atts[$key] = ( ! isset($attributes[$key])) ? $val : $attributes[$key];
        unset($attributes[$key]);
    }

    if ($attributes != '')
    {
        $attributes = _parse_attributes($attributes);
    }

    return "<a href='javascript:void(0);' onclick=\"window.open('".$site_url."', '_blank', '"._parse_attributes($atts, TRUE)."');\"$attributes>".$title."</a>";
}



function redirect($uri = '', $method = 'location', $http_response_code = 302)
{
    if ( ! preg_match('#^https?://#i', $uri))
    {
        $uri = ci_site_url($uri);
    }

    switch($method)
    {
        case 'refresh'  : header("Refresh:0;url=".$uri);
            break;
        default         : header("Location: ".$uri, TRUE, $http_response_code);
            break;
    }
    exit;
}

?>
