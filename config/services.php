<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'esb' => [
        'services_base_url' => env('ESB_SERVICES_BASE_URL', 'https://services.esb.co.id'),
        'core_base_url'     => env('ESB_CORE_BASE_URL', 'https://core-api.esb.co.id'),
    
        // bearer login
        'username' => env('ESB_USERNAME'),
        'password' => env('ESB_PASSWORD'),
    
        // default fallback key
        'default_token_key' => env('ESB_DEFAULT_TOKEN_KEY', 'OKNHO'),


        // static token mapping (semua ambil dari .env)
        'static_tokens' => [
            'BOD'   => env('ESB_TOKEN_BOD'),
            'GP100' => env('ESB_TOKEN_GP100'),
            'GP101' => env('ESB_TOKEN_GP101'),
            'GP102' => env('ESB_TOKEN_GP102'),
            'GP103' => env('ESB_TOKEN_GP103'),
            'GP104' => env('ESB_TOKEN_GP104'),
            'GP105' => env('ESB_TOKEN_GP105'),
            'GP106' => env('ESB_TOKEN_GP106'),
            'GP107' => env('ESB_TOKEN_GP107'),
            'GP108' => env('ESB_TOKEN_GP108'),
            'GP109' => env('ESB_TOKEN_GP109'),
            'GP110' => env('ESB_TOKEN_GP110'),
            'GP111' => env('ESB_TOKEN_GP111'),
            'GP112' => env('ESB_TOKEN_GP112'),
            'GP113' => env('ESB_TOKEN_GP113'),
            'GP114' => env('ESB_TOKEN_GP114'),
            'GP115' => env('ESB_TOKEN_GP115'),
            'GP116' => env('ESB_TOKEN_GP116'),
            'GP117' => env('ESB_TOKEN_GP117'),
            'GP118' => env('ESB_TOKEN_GP118'),
            'GP119' => env('ESB_TOKEN_GP119'),
            'GP120' => env('ESB_TOKEN_GP120'),
            'GP121' => env('ESB_TOKEN_GP121'),
            'GP122' => env('ESB_TOKEN_GP122'),
            'GP123' => env('ESB_TOKEN_GP123'),
            'GP124' => env('ESB_TOKEN_GP124'),
            'GP125' => env('ESB_TOKEN_GP125'),
            'GP126' => env('ESB_TOKEN_GP126'),
            'GP129' => env('ESB_TOKEN_GP129'),
            'GP130' => env('ESB_TOKEN_GP130'),
            'GP131' => env('ESB_TOKEN_GP131'),
            'GP132' => env('ESB_TOKEN_GP132'),
            'GP133' => env('ESB_TOKEN_GP133'),
            'GP134' => env('ESB_TOKEN_GP134'),
            'GP135' => env('ESB_TOKEN_GP135'),
            'GP136' => env('ESB_TOKEN_GP136'),
            'GP137' => env('ESB_TOKEN_GP137'),
            'GP138' => env('ESB_TOKEN_GP138'),
            'GP139' => env('ESB_TOKEN_GP139'),
            'GP140' => env('ESB_TOKEN_GP140'),
            'GP141' => env('ESB_TOKEN_GP141'),
            'GP142' => env('ESB_TOKEN_GP142'),
            'GP143' => env('ESB_TOKEN_GP143'),

            'GPR01' => env('ESB_TOKEN_GPR01'),
            'GPR02' => env('ESB_TOKEN_GPR02'),
            'GPR03' => env('ESB_TOKEN_GPR03'),
            'GPR04' => env('ESB_TOKEN_GPR04'),
            'GPR05' => env('ESB_TOKEN_GPR05'),
            'GPR06' => env('ESB_TOKEN_GPR06'),
            'GPR07' => env('ESB_TOKEN_GPR07'),
            'GPR08' => env('ESB_TOKEN_GPR08'),
            'GPR09' => env('ESB_TOKEN_GPR09'),
            'GPR10' => env('ESB_TOKEN_GPR10'),
            'GPR11' => env('ESB_TOKEN_GPR11'),
            'GPR12' => env('ESB_TOKEN_GPR12'),
            'GPR13' => env('ESB_TOKEN_GPR13'),
            'GPR14' => env('ESB_TOKEN_GPR14'),
            'GPR15' => env('ESB_TOKEN_GPR15'),
            'GPR16' => env('ESB_TOKEN_GPR16'),
            'GPR17' => env('ESB_TOKEN_GPR17'),
            'GPR18' => env('ESB_TOKEN_GPR18'),
            'GPR19' => env('ESB_TOKEN_GPR19'),
            'GPR20' => env('ESB_TOKEN_GPR20'),
            'GPR21' => env('ESB_TOKEN_GPR21'),
            'GPR22' => env('ESB_TOKEN_GPR22'),
            'GPR23' => env('ESB_TOKEN_GPR23'),
            'GPR24' => env('ESB_TOKEN_GPR24'),
            'GPR25' => env('ESB_TOKEN_GPR25'),
            'GPR26' => env('ESB_TOKEN_GPR26'),
            'GPR27' => env('ESB_TOKEN_GPR27'),
            'GPR28' => env('ESB_TOKEN_GPR28'),
            'GPR29' => env('ESB_TOKEN_GPR29'),
            'GPR30' => env('ESB_TOKEN_GPR30'),
            'GPR31' => env('ESB_TOKEN_GPR31'),
            'GPR32' => env('ESB_TOKEN_GPR32'),
            'GPR33' => env('ESB_TOKEN_GPR33'),
            'GPR34' => env('ESB_TOKEN_GPR34'),
            'GPR35' => env('ESB_TOKEN_GPR35'),
            'GPR36' => env('ESB_TOKEN_GPR36'),
            'GPR37' => env('ESB_TOKEN_GPR37'),
            'GPR38' => env('ESB_TOKEN_GPR38'),
            'GPR39' => env('ESB_TOKEN_GPR39'),
            'GPR40' => env('ESB_TOKEN_GPR40'),
            'GPR41' => env('ESB_TOKEN_GPR41'),
            'GPR42' => env('ESB_TOKEN_GPR42'),
            'GPR43' => env('ESB_TOKEN_GPR43'),
            'GPR44' => env('ESB_TOKEN_GPR44'),
            'GPR45' => env('ESB_TOKEN_GPR45'),
            'GPR46' => env('ESB_TOKEN_GPR46'),
            'GPR47' => env('ESB_TOKEN_GPR47'),
            'GPR48' => env('ESB_TOKEN_GPR48'),
            'GPR49' => env('ESB_TOKEN_GPR49'),
            'GPR50' => env('ESB_TOKEN_GPR50'),
            'GPR51' => env('ESB_TOKEN_GPR51'),
            'GPR52' => env('ESB_TOKEN_GPR52'),
            'GPR53' => env('ESB_TOKEN_GPR53'),
            'GPR54' => env('ESB_TOKEN_GPR54'),
            'GPR55' => env('ESB_TOKEN_GPR55'),
            'GPR56' => env('ESB_TOKEN_GPR56'),
            'GPR57' => env('ESB_TOKEN_GPR57'),
            'GPR58' => env('ESB_TOKEN_GPR58'),
            'GPR59' => env('ESB_TOKEN_GPR59'),
            'GPR60' => env('ESB_TOKEN_GPR60'),
            'GPR61' => env('ESB_TOKEN_GPR61'),
            'GPR62' => env('ESB_TOKEN_GPR62'),
            'GPR63' => env('ESB_TOKEN_GPR63'),
            'GPR64' => env('ESB_TOKEN_GPR64'),
            'GPR65' => env('ESB_TOKEN_GPR65'),
            'GPR66' => env('ESB_TOKEN_GPR66'),
            'GPR67' => env('ESB_TOKEN_GPR67'),
            'GPR68' => env('ESB_TOKEN_GPR68'),
            'GPR69' => env('ESB_TOKEN_GPR69'),
            'GPR70' => env('ESB_TOKEN_GPR70'),
            'GPR71' => env('ESB_TOKEN_GPR71'),
            'GPR72' => env('ESB_TOKEN_GPR72'),
            'GPR73' => env('ESB_TOKEN_GPR73'),
            'GPR74' => env('ESB_TOKEN_GPR74'),
            'GPR75' => env('ESB_TOKEN_GPR75'),
            'GPR76' => env('ESB_TOKEN_GPR76'),
            'GPR77' => env('ESB_TOKEN_GPR77'),
            'GPR78' => env('ESB_TOKEN_GPR78'),
            'GPR79' => env('ESB_TOKEN_GPR79'),
            'GPR80' => env('ESB_TOKEN_GPR80'),
            'GPR81' => env('ESB_TOKEN_GPR81'),
            'GPR82' => env('ESB_TOKEN_GPR82'),
            'GPR83' => env('ESB_TOKEN_GPR83'),
            'GPR84' => env('ESB_TOKEN_GPR84'),
            'GPR85' => env('ESB_TOKEN_GPR85'),
            'GPR86' => env('ESB_TOKEN_GPR86'),
            'GPR87' => env('ESB_TOKEN_GPR87'),
            'GPR88' => env('ESB_TOKEN_GPR88'),
            'GPR89' => env('ESB_TOKEN_GPR89'),
            'GPR90' => env('ESB_TOKEN_GPR90'),
            'GPR91' => env('ESB_TOKEN_GPR91'),
            'GPR92' => env('ESB_TOKEN_GPR92'),
            'GPR93' => env('ESB_TOKEN_GPR93'),
            'GPR94' => env('ESB_TOKEN_GPR94'),
            'GPR95' => env('ESB_TOKEN_GPR95'),
            'GPR96' => env('ESB_TOKEN_GPR96'),
            'GPR97' => env('ESB_TOKEN_GPR97'),
            'GPR98' => env('ESB_TOKEN_GPR98'),
            'GPR99' => env('ESB_TOKEN_GPR99'),

            'MIRZA' => env('ESB_TOKEN_MIRZA'),
            'OKNHO' => env('ESB_TOKEN_OKNHO'),
            'OPNSC' => env('ESB_TOKEN_OPNSC'),
        ],
    ],
    
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],


];
