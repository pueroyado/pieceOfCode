<?php

namespace app\components\cache\v3;

use app\components\cache\v3\entity\CacheRequest;

final class CacheManager
{
    private const LABEL = "cache.v3-";
    private const USR = "usr";
    private const EL = "el";

    /**
     * Генерация ключа, по которому доступен кэш.
     *
     * @param CacheRequest $request
     * @return string
     */
    public function generateKey(CacheRequest $request): string
    {
        $key = self::LABEL;
        /* В префикс ключа включаем id юзера и элемента */
        $key .= $this->createWrappedPart(self::USR, $request->getUserId());
        $key .= $this->createWrappedPart(self::EL, $request->getElementId());
        /* Обязательная часть (тело) ключа кэша */
        $key .= $request->getSource();
        /* В постфикс ключа включаем элементы мапы */
        foreach ($request->getTail() as $param => $value) {
            $key .= $this->createWrappedPart($param, $value);
        }
        return $key;
    }

    /**
     * Заключение параметра в обертку по шаблону /-NAME-{value}-NAME-/.
     *
     * @param string $param
     * @param string|null $value
     * @return string
     */
    private function createWrappedPart(string $param, ?string $value): string
    {
        $part = "-$param-";
        $part .= $value;
        $part .= "-$param-";
        return $part;
    }
}
