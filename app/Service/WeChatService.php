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

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\WeChat\WeChatFactory;
use EasyWeChat\MiniProgram\Application as MiniProgram;
use Hyperf\Di\Annotation\Inject;
use Swoole\Coroutine\Channel;

class WeChatService extends Service
{
    /**
     * @Inject
     * @var WeChatFactory
     */
    protected $factory;

    /**
     * @param $data = [
     *     'code' => '',
     *     'iv' => '',
     *     'encrypted_data' => '',
     * ]
     */
    public function login(array $data)
    {
        $channel = new Channel(1);
        $step = [];
        go(function () use ($data, $channel, &$step) {
            try {
                $step[] = 1;
                /** @var MiniProgram $app */
                $app = $this->factory->make('mini', WeChatFactory::MINI_PROGRAM);
                $step[] = 2;
                $session = $app->auth->session($data['code'])['session_key'] ?? '';
                $step[] = 4;
                if (empty($session)) {
                    throw new BusinessException(ErrorCode::SERVER_ERROR, '小程序登录失败');
                }
                $result = $app->encryptor->decryptData($session, $data['iv'], $data['encrypted_data']);
                $step[] = 5;
            } finally {
                $channel->push($result);
            }
        });
        $step[] = 3;

        $result = $channel->pop();

        $this->logger->info('当前SWOOLE_HOOK_FLAGS = ' . SWOOLE_HOOK_FLAGS);
        $this->logger->info('小程序登录步骤 应该是1,2,3,4,5 实际是' . implode(',', $step));

        return $result;
    }
}
