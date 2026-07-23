<?php

return [
    'username' => env('NETGSM_USERNAME'),
    'password' => env('NETGSM_PASSWORD'),
    'header' => env('NETGSM_HEADER'),
    'send_url' => env('NETGSM_SEND_URL', 'https://api.netgsm.com.tr/sms/rest/v2/send'),
    'encoding' => env('NETGSM_ENCODING', 'TR'),
];
