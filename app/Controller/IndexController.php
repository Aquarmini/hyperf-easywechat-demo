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

namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Service\WeChatService;
use Hyperf\Di\Annotation\Inject;
use Inhere\Validate\Validation;

class IndexController extends Controller
{
    /**
     * @Inject
     * @var WeChatService
     */
    protected $service;

    public function index()
    {
        $code = $this->request->input('code');

        $result = $this->service->info($code);

        return $this->response->success($result);
    }

    public function authorize()
    {
        return $this->service->authorize();
    }

    /**
     * 小程序登录.
     */
    public function login()
    {
        $validator = Validation::check($this->request->all(), [
            [['code', 'iv', 'encrypted_data'], 'required', 'filter' => 'string'],
        ]);

        if (! $validator->isOk()) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $validator->firstError());
        }

        $result = $this->service->login($validator->getSafeData());

        return $this->response->success($result);
    }
}
