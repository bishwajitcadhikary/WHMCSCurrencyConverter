<?php

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\WovoCurrencyConverter\Admin\AdminDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function wovocurrencyconverter_config()
{
    return [
        "name" => "Wovo Currency Converter",
        "description" => "An addon module for WHMCS which converts currency daily for 168 currencies. ".
            "Just set your currencies in WHMCS and add static rates. ".
            "It'll update them automatically with WHMCS cron job. ".
            "(see <a href=\"http://Fixer.IO\">Fixer.IO</a> for supported currency list)",
        "author" => "<a href=\"https://wovosoft.com\">WovoSoft</a>",
        "language" => "english",
        "version" => "1.0",
        "fields" => [
            "apiKey" => [
                "FriendlyName" => "API Key",
                "Type" => "text",
                "Size" => "100",
                "Default" => "",
                "Description" => "Add Your FIXER.IO API KEY Here or Get one for FREE from <b><a href=\"https://fixer.io\">Fixer.IO</a></b>"
            ],
            "currencies" => [
                "FriendlyName" => "Currencies",
                "Type" => "text",
                "Size" => "1000",
                "Default" => "",
                "Description" => "Comma Separated list of Currency 3-Letter Codes. e.g : NGN,PKR,AUD"
            ],
            "baseCurrency" => [
                "FriendlyName" => "Base Currency Code",
                "Type" => "text",
                "Size" => "1000",
                "Default" => "",
                "Description" => "Write base currency code e.g : USD or EUR"
            ],
        ]
    ];
}

function wovocurrencyconverter_activate()
{
    return [
        "status" => "success",
        "description" => "WHMCS Currency Converter Module has been activated. Activate The Module By Adding License Key."
    ];
}

function wovocurrencyconverter_deactivate()
{
    return [
        "status" => "success",
        "description" => "WHMCS Currency Converter Module has been de-activated. Currency won't be updated daily."
    ];
}

function wovocurrencyconverter_output($vars)
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response;
}

function wovocurrencyconverter_sidebar($vars)
{
    $sidebar = '<p style="padding: 5px">Developed by: <a href="https://wovosoft.com" target="_blank">WovoSoft</a></p>';
    return $sidebar;
}
