<?php
declare(strict_types=1);

namespace App;

/**
 * Обёртка над laximo/guayaquillib для нашего микросервиса.
 * Требует переменные окружения LAXIMO_LOGIN и LAXIMO_PASSWORD (передаются извне).
 */
final class GuayaquilClient
{
    private ServiceOem $oem;

    public function __construct(string $login, string $password)
    {
        $this->oem = new ServiceOem($login, $password);
    }

    /** Пример: список доступных каталогов */
    public function listCatalogs(): array
    {
        return $this->oem->listCatalogs();
    }

    /** Пример: информация по конкретному каталогу */
    public function getCatalogInfo(string $code): array
    {
        return $this->oem->getCatalogInfo($code);
    }

    /** Пример батч-запроса: объединяем список + инфо по каталогу */
    public function catalogsWithInfo(string $code): array
    {
        return $this->oem->queryButch([
            \Guayaquil\Oem::listCatalogs(),
            \Guayaquil\Oem::getCatalogInfo($code),
        ]);
    }
}

