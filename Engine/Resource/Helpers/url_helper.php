<?php
/**
 * Site URL
 *
 * Create a local URL based on your basepath. Segments can be passed via the
 * first parameter either as a string or an array.
 *
 * @param	string	$uri
 * @param	string	$protocol
 * @return	string
 */
function site_url($uri = '', $protocol = null)
{
    return get_instance()->config->site_url($uri, $protocol);
}


/**
 * Base URL
 *
 * Create a local URL based on your basepath.
 * Segments can be passed in as a string or an array, same as site_url
 * or a URL to a file can be passed in, e.g. to an image file.
 *
 * @param	string	$uri
 * @param	string	$protocol
 * @return	string
 */
function base_url($uri = '', $protocol = null)
{
    return get_instance()->config->base_url($uri, $protocol);
}

/**
 * Current URL
 *
 * Returns the full URL (including segments) of the page where this
 * function is placed
 *
 * @return	string
 */
function current_url()
{
    $CI =& get_instance();
    return $CI->config->site_url($CI->uri->uri_string());
}

/**
 * URL String
 *
 * Returns the URI segments.
 *
 * @return	string
 */
function uri_string()
{
    return get_instance()->uri->uri_string();
}

/**
 * Index page
 *
 * Returns the "index_page" from your config file
 *
 * @return	string
 */
function index_page()
{
    return get_instance()->config->item('index_page');
}

/**
 * Anchor Link
 *
 * Creates an anchor based on the local URL.
 *
 * @param	string	$uri        the URL
 * @param	string	$title      the link title
 * @param	mixed	$attributes any attributes
 * @return	string
 */
function anchor($uri = '', $title = '', $attributes = '')
{
    $title = (string) $title;

    $site_url = is_array($uri)
        ? site_url($uri)
        : (preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri));

    if ($title === '') {
        $title = $site_url;
    }

    if ($attributes !== '') {
        $attributes = _stringify_attributes($attributes);
    }

    return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
}


/**
 * Anchor Link - Pop-up version
 *
 * Creates an anchor based on the local URL. The link
 * opens a new window based on the attributes specified.
 *
 * @param	string	the URL
 * @param	string	the link title
 * @param	mixed	any attributes
 * @return	string
 */
function anchor_popup($uri = '', $title = '', $attributes = false)
{
    $title = (string) $title;
    $site_url = preg_match('#^(\w+:)?//#i', $uri) ? $uri : site_url($uri);

    if ($title === '') {
        $title = $site_url;
    }

    if ($attributes === false) {
        return '<a href="'.$site_url.'" onclick="window.open(\''.$site_url."', '_blank'); return false;\">".$title.'</a>';
    }

    if (! is_array($attributes)) {
        $attributes = array($attributes);

        // Ref: http://www.w3schools.com/jsref/met_win_open.asp
        $window_name = '_blank';
    } elseif (! empty($attributes['window_name'])) {
        $window_name = $attributes['window_name'];
        unset($attributes['window_name']);
    } else {
        $window_name = '_blank';
    }

    foreach (array('width' => '800', 'height' => '600', 'scrollbars' => 'yes', 'menubar' => 'no', 'status' => 'yes', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0') as $key => $val) {
        $atts[$key] = isset($attributes[$key]) ? $attributes[$key] : $val;
        unset($attributes[$key]);
    }

    $attributes = _stringify_attributes($attributes);

    return '<a href="'.$site_url
           .'" onclick="window.open(\''.$site_url."', '".$window_name."', '"._stringify_attributes($atts, true)."'); return false;\""
           .$attributes.'>'.$title.'</a>';
}

/**
 * Mailto Link
 *
 * @param	string	the email address
 * @param	string	the link title
 * @param	mixed	any attributes
 * @return	string
 */
function mailto($email, $title = '', $attributes = '')
{
    $title = (string) $title;

    if ($title === '') {
        $title = $email;
    }

    return '<a href="mailto:'.$email.'"'._stringify_attributes($attributes).'>'.$title.'</a>';
}

/**
 * Encoded Mailto Link
 *
 * Create a spam-protected mailto link written in Javascript
 *
 * @param	string	the email address
 * @param	string	the link title
 * @param	mixed	any attributes
 * @return	string
 */
function safe_mailto($email, $title = '', $attributes = '')
{
    $title = (string) $title;

    if ($title === '') {
        $title = $email;
    }

    $x = str_split('<a href="mailto:', 1);

    for ($i = 0, $l = strlen($email); $i < $l; $i++) {
        $x[] = '|'.ord($email[$i]);
    }

    $x[] = '"';

    if ($attributes !== '') {
        if (is_array($attributes)) {
            foreach ($attributes as $key => $val) {
                $x[] = ' '.$key.'="';
                for ($i = 0, $l = strlen($val); $i < $l; $i++) {
                    $x[] = '|'.ord($val[$i]);
                }
                $x[] = '"';
            }
        } else {
            for ($i = 0, $l = strlen($attributes); $i < $l; $i++) {
                $x[] = $attributes[$i];
            }
        }
    }

    $x[] = '>';

    $temp = array();
    for ($i = 0, $l = strlen($title); $i < $l; $i++) {
        $ordinal = ord($title[$i]);

        if ($ordinal < 128) {
            $x[] = '|'.$ordinal;
        } else {
            if (count($temp) === 0) {
                $count = ($ordinal < 224) ? 2 : 3;
            }

            $temp[] = $ordinal;
            if (count($temp) === $count) {
                $number = ($count === 3)
                    ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64)
                    : (($temp[0] % 32) * 64) + ($temp[1] % 64);
                $x[] = '|'.$number;
                $count = 1;
                $temp = array();
            }
        }
    }

    $x[] = '<';
    $x[] = '/';
    $x[] = 'a';
    $x[] = '>';

    $x = array_reverse($x);

    $output = "<script type=\"text/javascript\">\n"
              ."\t//<![CDATA[\n"
              ."\tvar l=new Array();\n";

    for ($i = 0, $c = count($x); $i < $c; $i++) {
        $output .= "\tl[".$i."] = '".$x[$i]."';\n";
    }

    $output .= "\n\tfor (var i = l.length-1; i >= 0; i=i-1) {\n"
               ."\t\tif (l[i].substring(0, 1) === '|') document.write(\"&#\"+unescape(l[i].substring(1))+\";\");\n"
               ."\t\telse document.write(unescape(l[i]));\n"
               ."\t}\n"
               ."\t//]]>\n"
               .'</script>';

    return $output;
}

/**
 * Auto-linker
 *
 * Automatically links URL and Email addresses.
 * Note: There's a bit of extra code here to deal with
 * URLs or emails that end in a period. We'll strip these
 * off and add them after the link.
 *
 * @param	string	the string
 * @param	string	the type: email, url, or both
 * @param	bool	whether to create pop-up links
 * @return	string
 */
function auto_link($str, $type = 'both', $popup = false)
{
    // Find and replace any URLs.
    if ($type !== 'email' && preg_match_all('#(\w*://|www\.)[^\s()<>;]+\w#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        // Set our target HTML if using popup links.
        $target = ($popup) ? ' target="_blank"' : '';

        // We process the links in reverse order (last -> first) so that
        // the returned string offsets from preg_match_all() are not
        // moved as we add more HTML.
        foreach (array_reverse($matches) as $match) {
            // $match[0] is the matched string/link
            // $match[1] is either a protocol prefix or 'www.'
            //
            // With PREG_OFFSET_CAPTURE, both of the above is an array,
            // where the actual value is held in [0] and its offset at the [1] index.
            $a = '<a href="'.(strpos($match[1][0], '/') ? '' : 'http://').$match[0][0].'"'.$target.'>'.$match[0][0].'</a>';
            $str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
        }
    }

    // Find and replace any emails.
    if ($type !== 'url' && preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE)) {
        foreach (array_reverse($matches[0]) as $match) {
            if (filter_var($match[0], FILTER_VALIDATE_EMAIL) !== false) {
                $str = substr_replace($str, safe_mailto($match[0]), $match[1], strlen($match[0]));
            }
        }
    }

    return $str;
}


/**
 * Prep URL
 *
 * Simply adds the http:// part if no scheme is included
 *
 * @param	string	the URL
 * @return	string
 */
function prep_url($str = '')
{
    if ($str === 'http://' or $str === '') {
        return '';
    }

    $url = parse_url($str);

    if (! $url or ! isset($url['scheme'])) {
        return 'http://'.$str;
    }

    return $str;
}


/**
 * Create URL Title
 *
 * Takes a "title" string as input and creates a
 * human-friendly URL string with a "separator" string
 * as the word separator.
 *
 * @todo	Remove old 'dash' and 'underscore' usage in 3.1+.
 * @param	string	$str		Input string
 * @param	string	$separator	Word separator
 *			(usually '-' or '_')
 * @param	bool	$lowercase	Whether to transform the output string to lowercase
 * @return	string
 */
function url_title($str, $separator = '-', $lowercase = false)
{
    if ($separator === 'dash') {
        $separator = '-';
    } elseif ($separator === 'underscore') {
        $separator = '_';
    }

    $q_separator = preg_quote($separator, '#');

    $trans = array(
        '&.+?;'            => '',
        '[^\w\d _-]'        => '',
        '\s+'            => $separator,
        '('.$q_separator.')+'    => $separator
    );

    $str = strip_tags($str);
    foreach ($trans as $key => $val) {
        $str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
    }

    if ($lowercase === true) {
        $str = strtolower($str);
    }

    return trim(trim($str, $separator));
}

/**
 * Header Redirect
 *
 * Header redirect in two flavors
 * For very fine grained control over headers, you could use the Output
 * Library's set_header() function.
 *
 * @param	string	$uri	URL
 * @param	string	$method	Redirect method
 *			'auto', 'location' or 'refresh'
 * @param	int	$code	HTTP Response status code
 * @return	void
 */
function redirect($uri = '', $method = 'auto', $code = null)
{
    if (! preg_match('#^(\w+:)?//#i', $uri)) {
        $uri = site_url($uri);
    }

    // IIS environment likely? Use 'refresh' for better compatibility
    if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
        $method = 'refresh';
    } elseif ($method !== 'refresh' && (empty($code) or ! is_numeric($code))) {
        if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
            $code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
                ? 303    // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
                : 307;
        } else {
            $code = 302;
        }
    }

    switch ($method) {
        case 'refresh':
            header('Refresh:0;url='.$uri);
            break;
        default:
            header('Location: '.$uri, true, $code);
            break;
    }
    exit;
}

/**
 * Get base url within defined Path
 *
 * @param string $rootpath
 *
 * @return bool|string
 */
function get_path_from_root($rootpath)
{
    if (is_string($rootpath) && ($rootpath = realpath($rootpath)) !== false) {
        static $fcpath;
        if (!isset($fcpath)) {
            $fcpath = realpath(FCPATH);
        }
        if (strpos($rootpath, $fcpath) === 0) {
            return '/'. ltrim(
                str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    substr($rootpath, strlen($fcpath)-1)
                ),
                '/'
            );
        }
    }

    return false;
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function asset_url($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $path = get_path_from_root(ASSETPATH);
    }
    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function templates_uri($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $path = get_path_from_root(TEMPLATEPATH);
    }
    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}

/**
 * Get asset URL
 *
 * @param string $url_path
 *
 * @return string
 */
function template_uri($url_path = '')
{
    static $path;
    if (!isset($path)) {
        $CI =& get_instance();
        $CI->load->model('TemplateModel', MODEL_NAME_TEMPLATE_USER);
        $path = get_path_from_root($CI->load->get(MODEL_NAME_TEMPLATE_USER)->getActiveTemplateDirectory());
    }

    if (!is_string($url_path)) {
        settype($url_path, 'string');
    }
    if (trim($url_path, '/') == '') {
        $url_path = '/';
    } elseif (! strpos($url_path, '\\') !== false || strpos($url_path, '/') !== false) {
        $url_path = preg_replace('/(\\\|\/)+/', '/', $url_path);
        $url_path = '/'.ltrim($url_path, '/');
    }
    return base_url($path . $url_path);
}
/**
 * Current URL WIth Query
 *
 * Returns the full URL (including segments) of the page where this
 * function is placed
 *
 * @return	string
 */
function current_really_url()
{
    $CI =& get_instance();
    $query = isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '';
    return $CI->config->site_url($CI->uri->uri_string().$query);
}