<?php
declare(strict_types=1);

namespace App;

/**
 * Обёртка над laximo/guayaquillib.
 * Классы SDK: \GuayaquilLib\ServiceOem и \GuayaquilLib\Oem
 */
final class GuayaquilClient
{
    private \GuayaquilLib\ServiceOem $oem;

    public function __construct(string $login, string $password)
    {
        $this->oem = new \GuayaquilLib\ServiceOem($login, $password);
    }

    /** Список доступных каталогов */
    public function listCatalogs(): array
    {
        return $this->oem->listCatalogs();
    }

    /** Информация по конкретному каталогу */
    public function getCatalogInfo(string $code): array
    {
        return $this->oem->getCatalogInfo($code);
    }

    /** Пример батча: список + инфо по каталогу */
    public function catalogsWithInfo(string $code): array
    {
        return $this->oem->queryButch([
            \GuayaquilLib\Oem::listCatalogs(),
            \GuayaquilLib\Oem::getCatalogInfo($code),
        ]);
    }
}
