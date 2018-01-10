<?php

use App\Helpers\DataStore;

/**
 * Include a php file.
 *
 * @param array  $payload
 * @param string $templateName
 *
 * @return
 */
function DvInclude($payload, $templateName)
{
    $serviceName = $payload['service_name'];
    $viewPath = config('devless')['views_directory'];

    include $viewPath.$serviceName.'/'.$templateName;
}
/**
 * Get asset path.
 *
 * @param string $payload
 * @param string $assetPath
 *
 * @return string
 */
function DvAssetPath($payload, $partialAssetPath)
{
    $serviceName = $payload['service_name'];

    $assetPath = url('/').'/service_views/'.$serviceName.'/'.$partialAssetPath;

    return $assetPath;
}
/**
 * allow access to admin only.
 *
 * @param bool $message
 */
function DvAdminOnly($message = "Sorry you don't have access to this page")
{
    $helper = app(\App\Helpers\Helper::class);
    $is_admin = $helper->is_admin_login();

    ($is_admin) ? true : $helper->interrupt(1001, $message);
}
/**
 * Get url for specified page name.
 *
 * @param type $payload
 * @param type $pageName
 *
 * @return string
 */
function DvNavigate($payload, $pageName)
{
    $pagePath = url('/').'/service/'.$payload['service_name'].'/view/'.$pageName;

    return  $pagePath;
}

/**
 * Redirect to url after time.
 *
 * @param type $url
 * @param type $time
 */
function DvRedirect($url, $time)
{
    header('refresh:'.$time.';url='.$url);
}

/**
 * Get Instance Token
 */
function DvAppToken()
{
    $instance = DataStore::instanceInfo();
    return $instance['app']->token;
}

/**
 * Get Instance SDK
 */
function DvJSSDK()
{
    return '<script src="'.URL('/').'/js/devless-sdk.js" class="devless-connection" devless-con-token="'. DataStore::instanceInfo()['app']->token .'"></script>';
}

/**
 * Get JS SDK URL
 */
function DvSDKURL()
{
    return URL('/').'/js/devless-sdk.js';
}
