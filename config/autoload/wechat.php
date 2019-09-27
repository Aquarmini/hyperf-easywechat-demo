<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'mini' => [
        'app_id' => env('WECHAT_MINI_APP_ID'),
        'secret' => env('WECHAT_MINI_SECRET'),
        'response_type' => 'array',
        'log' => [
            'level' => 'debug',
            'file' => BASE_PATH . '/runtime/logs/wechat.log',
        ],
    ],
    'h5' => [
    ],
];
