<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * prepare imap email body html.
 *
 * @param string $body
 *
 * @return string
 */
function prepare_imap_email_body_html($body)
{
    $body = trim($body);
    $body = str_replace('&nbsp;', ' ', $body);
    $body = trim(strip_html_tags($body, '<br/>, <br>, <a>'));
    $body = preg_replace("/[\r\n]+/", "\n", $body);
    $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);
    $body = preg_replace('/\n/', '<br>', $body);
    
    return $body;
}

function convert_to_body($body)
{
    $CI = &get_instance();
    $CI->load->library('security');
    $body = trim($body);
    $body = str_replace('&nbsp;', ' ', $body);
    $body = trim(strip_html_tags($body, '<br/>, <br>, <a>'));
    $body = $CI->security->xss_clean($body);
    $body = preg_replace("/[\r\n]+/", "\n", $body);
    $body = preg_replace('/\n(\s*\n)+/', '<br />', $body);
    $body = preg_replace('/\n/', '<br>', $body);
    
    return $body;
}

function fix_encoding_chars($text)
{
    $text = str_replace('ð', 'ğ', $text);
    $text = str_replace('þ', 'ş', $text);
    $text = str_replace('ý', 'ı', $text);
    $text = str_replace('Ý', 'İ', $text);
    $text = str_replace('Ð', 'Ğ', $text);
    $text = str_replace('Þ', 'Ş', $text);
    
    return $text;
}

