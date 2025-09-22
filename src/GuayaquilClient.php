<?php
declare(strict_types=1);

namespace App;

/**
 * Обёртка над laximo/guayaquillib.
 * ВНИМАНИЕ: классы SDK находятся в ГЛОБАЛЬНОМ пространстве имён: \ServiceOem, \Oem
 */
final class GuayaquilClient
{
    /** @var \ServiceOem */
    private \ServiceOem $oem;

    public function __construct(string $login, string $password)
    {
        // глобальный класс из guayaquillib
        $this->oem = new \ServiceOem($login, $password);
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
            \Oem::listCatalogs(),
            \Oem::getCatalogInfo($code),
        ]);
    }
}
