<?php
/**
 * Function collection
 */

/**
 * @return array
 */
function admin_notice_info()
{
    $ci = get_instance();
    $record = $ci->load->get(MODEL_NAME_NOTICE);
    $notice = array();
    if ($record instanceof \NoticeRecord) {
        $info = $record->get('info');
        $info = is_array($info) ? $info : array();
        ob_start();
        /** @noinspection PhpUndefinedMethodInspection */
        $notice = Hook::apply('admin_notice_info', $info);
        ob_end_clean();
        if (! is_array($notice)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Hook::removeAll('admin_notice_info');
            /** @noinspection PhpUndefinedMethodInspection */
            $notice = Hook::apply('admin_notice_info', $info);
        } else {
            if ($info !== $notice) {
                $record->clearNotice('info');
                $record->set('info', $notice);
            }
        }
    }

    return $notice;
}

/**
 * @return array
 */
function admin_notice_warning()
{
    $ci = get_instance();
    $record = $ci->load->get(MODEL_NAME_NOTICE);
    $notice = array();
    if ($record instanceof \NoticeRecord) {
        $warning = $record->get('warning');
        $warning = is_array($warning) ? $warning : array();
        ob_start();
        /** @noinspection PhpUndefinedMethodInspection */
        $notice = Hook::apply('admin_notice_warning', $warning);
        ob_end_clean();
        if (! is_array($notice)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Hook::removeAll('admin_notice_warning');
            /** @noinspection PhpUndefinedMethodInspection */
            $notice = Hook::apply('admin_notice_warning', $warning);
        } else {
            $notice = array_unique($notice);
            $record->clearNotice('warning');
            $record->set('warning', $notice);
        }
    }

    return $notice;
}

/**
 * @return array
 */
function admin_notice_error()
{
    $ci = get_instance();
    $record = $ci->load->get(MODEL_NAME_NOTICE);
    $notice = array();
    if ($record instanceof \NoticeRecord) {
        $error = $record->get('error');
        $error = is_array($error) ? $error : array();
        ob_start();
        /** @noinspection PhpUndefinedMethodInspection */
        $notice = Hook::apply('admin_notice_error', $error);
        ob_end_clean();
        if (! is_array($notice)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Hook::removeAll('admin_notice_error');
            /** @noinspection PhpUndefinedMethodInspection */
            $notice = Hook::apply('admin_notice_error', $error);
        } else {
            $notice = array_unique($notice);
            if ($error !== $notice) {
                $record->clearNotice('error');
                $record->set('error', $notice);
            }
        }
    }

    return $notice;
}

/**
 * @return array
 */
function admin_notice_success()
{
    $ci = get_instance();
    $record = $ci->load->get(MODEL_NAME_NOTICE);
    $notice = array();
    if ($record instanceof \NoticeRecord) {
        $success = $record->get('success');
        $success = is_array($success) ? $success : array();
        ob_start();
        /** @noinspection PhpUndefinedMethodInspection */
        $notice = Hook::apply('admin_notice_success', $success);
        ob_end_clean();
        if (! is_array($notice)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Hook::removeAll('admin_notice_success');
            /** @noinspection PhpUndefinedMethodInspection */
            $notice = Hook::apply('admin_notice_success', $success);
        } else {
            if ($success !== $notice) {
                $record->clearNotice('success');
                $record->set('success', $notice);
            }
        }
    }

    return $notice;
}

/* ------------------------------------------------
 * CONTEXT SITE
 * ------------------------------------------------
 */

/**
 * check if is on admin area
 *
 * @return bool
 */
function is_admin_area()
{
    $ci = get_instance();
    if (isset($ci->router) && $ci->router instanceof \PentagonalRouter) {
        return $ci->router->isAdminRoute();
    }

    return false;
}

/**
 * Getting Header.php
 */
function get_header()
{
    $dir = get_instance()->load->getActiveTemplate();
    if ($dir && strpos($dir, TEMPLATEPATH) === 0) {
        $file = $dir . DIRECTORY_SEPARATOR . 'header.php';
        if (file_exists($file)) {
            get_instance()->load->file($file);
        }
    }
}

/**
 * Getting Footer.php
 */
function get_footer()
{
    $dir = get_instance()->load->getActiveTemplate();
    if ($dir && strpos($dir, TEMPLATEPATH) === 0) {
        $file = $dir . DIRECTORY_SEPARATOR . 'footer.php';
        if (file_exists($file)) {
            get_instance()->load->file($file);
        }
    }
}

/**
 * Sanitizes an HTML classname to ensure it only contains valid characters.
 *
 * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty
 * string then it will return the alternative value supplied.
 *
 * @todo Expand to support the full range of CDATA that a class attribute can contain.
 *
 * @since 2.8.0
 *
 * @param string $class    The classname to be sanitized
 * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
 * 	Defaults to an empty string.
 * @return string The sanitized value
 */
function sanitize_html_class($class, $fallback = '')
{
    if (is_array($class)) {
        foreach ($class as $key => $value) {
            if (!is_string($value)) {
                unset($class[$key]);
            }
        }
        return $class;
    }

    //Strip out any % encoded octets
    $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $class);

    //Limit to A-Z,a-z,0-9,_,-
    $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

    if ( '' == $sanitized && $fallback ) {
        return sanitize_html_class( $fallback );
    }
    /**
     * Filter a sanitized HTML class string.
     *
     * @since 2.8.0
     *
     * @param string $sanitized The sanitized HTML class.
     * @param string $class     HTML class before sanitization.
     * @param string $fallback  The fallback string.
     */
    /** @noinspection PhpUndefinedMethodInspection */
    return Hook::apply( 'sanitize_html_class', $sanitized, $class, $fallback);
}

/**
 * Getting body class
 *
 * @return string
 */
function bodyClass($echo = true)
{
    $class = getBodyClass();
    $retval = '';
    if (trim($class) != '') {
        $retval = ' class="' . getBodyClass() . '"';
    }
    if ($echo === true) {
        echo $retval;
    }
    return $retval;
}

function body_class($echo = true)
{
    return bodyClass($echo);
}

function getBodyClass()
{
    $arr = array();
    is_admin_area() && $arr[] = 'admin-page';
    /** @noinspection PhpUndefinedMethodInspection */
    $arrs = Hook::apply('body_class', $arr);
    if (!is_array($arrs)) {
        $arrs = $arr;
    }
    $arrs = array_unique($arrs);
    $arrs = sanitize_html_class($arrs);
    $arrs = array_filter($arrs);
    return implode(' ', $arrs);
}

function get_body_class()
{
    return getBodyClass();
}

function getSiteInfo($type = null)
{
    $CI =& get_instance();
    switch ($type) {
        case 'language':
            /** @noinspection PhpUndefinedFieldInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            return Hook::apply('site_language', $CI->lang->getCurrentLanguage());
    }

    return null;
}

/**
 * @param string $name
 * @param null   $default
 *
 * @return mixed
 */
function getOption($name, $default = null)
{
    $ci = &get_instance();
    $option = $ci->load->get(MODEL_NAME_OPTION);
    if ($option instanceof \DataModel) {
        return $option->get($name, $default);
    }
    return $default;
}
/**
 * @param string $name
 * @param null   $default
 *
 * @return mixed
 */
function get_option($name, $default)
{
    return getOption($name, $default);
}

/**
 * @param string $doctype
 *
 * @return mixed
 */
function get_language_attributes($doctype = 'html')
{
    $attributes = array();

    if ( function_exists('is_rtl') && is_rtl()) {
        $attributes[] = 'dir="rtl"';
    }

    if ( $lang = getSiteInfo('language') ) {
        if ($doctype == 'html' ) {
            $attributes[] = "lang=\"$lang\"";
        }
        if ($doctype == 'xhtml') {
            $attributes[] = "xml:lang=\"$lang\"";
        }
    }

    $output = implode(' ', $attributes);
    /** @noinspection PhpUndefinedMethodInspection */
    return Hook::apply('language_attributes', $output, $doctype);
}

/**
 * @param string $doctype
 */
function language_attributes($doctype = 'html') {
    echo get_language_attributes($doctype);
}

/**
 * @return string
 */
function get_the_title()
{
    /** @noinspection PhpUndefinedMethodInspection */
    return (string) Hook::apply('the_title', '');
}