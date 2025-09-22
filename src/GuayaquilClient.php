<?php
declare(strict_types=1);

namespace App;

final class GuayaquilClient
{
    private \GuayaquilLib\ServiceOem $oem;

    public function __construct(string $login, string $password)
    {
        $this->oem = new \GuayaquilLib\ServiceOem($login, $password);
    }

    public function listCatalogs(): array
    {
        return $this->oem->listCatalogs();
    }

    public function getCatalogInfo(string $code): array
    {
        return $this->oem->getCatalogInfo($code);
    }

    public function catalogsWithInfo(string $code): array
    {
        return $this->oem->queryButch([
            \GuayaquilLib\Oem::listCatalogs(),
            \GuayaquilLib\Oem::getCatalogInfo($code),
        ]);
    }
}
