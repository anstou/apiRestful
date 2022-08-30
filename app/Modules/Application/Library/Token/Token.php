<?php

namespace App\Modules\Application\Library\Token;

use App\Library\Cache\FileStorage;
use App\Library\InterfaceWarehouse\CacheInterface;

class Token
{
//    protected Storage $storage;

    /**
     *
     * @param CacheInterface $storage 储存驱动
     */
    public function __construct(protected CacheInterface $storage = new FileStorage('token'))
    {
    }

    /**
     * token是否存在
     *
     * @param string $token
     * @return bool
     */
    public function has(string $token): bool
    {
        return $this->storage->has($token);
    }

    /**
     * 储存token
     *
     * @param string $token
     * @param array $data token包含的值,储存用户信息
     * @param int|\DateInterval|null $ttl
     * @return $this
     */
    public function set(string $token, array $data, null|int|\DateInterval $ttl = null): static
    {
        $this->storage->set($token, $data, $ttl);
        return $this;
    }

    public function get(string $token, mixed $default = null): mixed
    {
        return $this->storage->get($token, $default);
    }

    /**
     * 删除token
     *
     * @param string $token
     * @return $this
     */
    public function delete(string $token): static
    {
        $this->storage->delete($token);
        return $this;
    }
}