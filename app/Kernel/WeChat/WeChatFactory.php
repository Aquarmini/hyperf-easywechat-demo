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

namespace App\Kernel\WeChat;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\Http\Response;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\ServiceContainer;
use GuzzleHttp\HandlerStack;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Overtrue\Socialite\Providers\AbstractProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WeChatFactory
{
    const OFFICIAL_ACCOUNT = 'officialAccount';

    const MINI_PROGRAM = 'miniProgram';

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var
     */
    protected $config;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
        $this->response = $this->container->get(Response::class);
        $this->request = $this->container->get(RequestInterface::class);
    }

    public function make($source, $class = self::OFFICIAL_ACCOUNT)
    {
        $config = $this->config->get('wechat');
        if (! $config[$source]) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '配置不存在');
        }

        $config = $config[$source];

        $app = Factory::$class($config);
        // 重写 Handler
        $app['guzzle_handler'] = CoroutineHandler::class;
        // 设置缓存
        $app['cache'] = $this->container->get(RedisCache::class);
        // 设置 OAuth 授权的 Guzzle 配置
        AbstractProvider::setGuzzleOptions([
            'http_errors' => false,
            'handler' => HandlerStack::create(new CoroutineHandler()),
        ]);

        return $app;
    }

    /**
     * 初始化Request.
     * @return ServiceContainer
     */
    public function initRequest(ServiceContainer $container)
    {
        $container['request']->query = new ParameterBag($this->request->all());
        return $container;
    }

    /**
     * 页面重定向.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function redirect(RedirectResponse $redirectResponse)
    {
        $url = $redirectResponse->headers->get('Location');

        return $this->response->redirect($url);
    }
}
