<?php

return [
    'client_side_timestamp' => 201811231027, // Timestamp to prevent CSS/JS caching

    'name' => env('APP_NAME', 'Business Manager'), // Name used for page titles and header display
    'legal_name' => env('APP_NAME'), // Used in terms and policy

    'default_signup_role' => 5, // 1 = Admin, 2 = Manager, 3 = Employee, 4 = Contractor, 5 = Client, 6 = Lead

    // Email defaults
    'mail_address_from' => env('MAIL_FROM_ADDRESS'),
    'mail_name_from' => env('MAIL_FROM_NAME'),

    // Available languages
    'available_languages' => [
      'en' => 'English'
    ],

    // Localization defaults
    'default_language' => 'en',
    'default_locale' => 'en-US',
    'default_timezone' => 'UTC',
    'default_date_format' => 'm/d/Y',
    'default_currency' => 'USD',
    'default_time_format' => 'g:i a', // 24-hour use date("H:i"), 12-hour use date("g:i a") (a = am/pm, A = AM/PM)
    'default_decimals' => 2,
    'default_decimal_seperator' => '.',
    'default_thousands_seperator' => ',',
 
    // Override localization defaults per language
    'language_defaults' => [
      'en' => [
        'locale' => 'en-US',
        'timezone' => 'America/New_York',
        'date_format' => 'm/d/Y',
        'currency' => 'USD',
        'time_format' => 'g:i a',
        'decimals' => 2,
        'decimal_seperator' => '.',
        'thousands_seperator' => ','
      ]
    ],
];