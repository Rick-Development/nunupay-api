<?php
return [

    'default' => env('TRANSLATE_METHOD', 'azure'),

    'translate_method' => [
        'azure' => [
            'end_point_url' => ['value' => env('END_POINT_URL'), 'is_protected' => false],
            'subscription_key' => ['value' => env('SUBSCRIPTION_KEY'), 'is_protected' => true],
            'subscription_region' => ['value' => env('SUBSCRIPTION_REGION'), 'is_protected' => false],
        ],
        'google' => [
            'project_id' => ['value' => env('PROJECT_ID'), 'is_protected' => false],
            'google_credentials' => ['value' => env('GOOGLE_CREDENTIALS'), 'is_protected' => false],
        ],
    ]

];
