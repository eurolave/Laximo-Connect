<?php
declare(strict_types=1);

namespace App;

final class GuayaquilClient
{
    private \GuayaquilLib\ServiceOem $oem;

    public function __construct(string $login, string $password)
    {
        $this->oem = new \GuayaquilLib\ServiceOem($login, $password);

        // Жестко переопределяем хост, если задан в окружении
        $host = getenv('LAXIMO_HOST') ?: 'https://ws.laximo.ru';
        $this->forceHost($host);
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

    /** Пример батча */
    public function catalogsWithInfo(string $code): array
    {
        return $this->oem->queryButch([
            \GuayaquilLib\Oem::listCatalogs(),
            \GuayaquilLib\Oem::getCatalogInfo($code),
        ]);
    }

    /** Поиск авто по VIN */
    public function findVehicleByVin(string $vin): array
    {
        return $this->oem->findVehicleByVin($vin);
    }

    /**
     * Насильно направляем SDK на нужный endpoint.
     * Пытаемся: setHost()/setEndpoint()/setUrl(), свойство host/url/endpoint/baseUrl,
     * и, если доступен SoapClient, дергаем __setLocation().
     */
    private function forceHost(string $host): void
    {
        try {
            $oemRef = new \ReflectionObject($this->oem);

            // 1) Попробуем явные сеттеры на объекте ServiceOem
            foreach (['setHost','setEndpoint','setUrl'] as $m) {
                if ($oemRef->hasMethod($m)) {
                    $oemRef->getMethod($m)->invoke($this->oem, $host);
                    return;
                }
            }

            // 2) Полезем во внутренности (обертка SOAP)
            $soapWrapper = null;
            foreach (['soap','client','soapClient','wrapper'] as $propName) {
                if ($oemRef->hasProperty($propName)) {
                    $p = $oemRef->getProperty($propName);
                    $p->setAccessible(true);
                    $soapWrapper = $p->getValue($this->oem);
                    if ($soapWrapper) break;
                }
            }

            if ($soapWrapper) {
                $wRef = new \ReflectionObject($soapWrapper);

                // 2a) Сеттеры в обертке
                foreach (['setHost','setEndpoint','setUrl'] as $m) {
                    if ($wRef->hasMethod($m)) {
                        $wRef->getMethod($m)->invoke($soapWrapper, $host);
                    }
                }
                // 2b) Популярные поля-хранилища URL
                foreach (['host','url','endpoint','baseUrl'] as $pn) {
                    if ($wRef->hasProperty($pn)) {
                        $pp = $wRef->getProperty($pn);
                        $pp->setAccessible(true);
                        $pp->setValue($soapWrapper, $host);
                    }
                }

                // 2c) Достанем сам SoapClient и выставим location
                foreach (['soap','client','soapClient'] as $pn) {
                    if ($wRef->hasProperty($pn)) {
                        $pp = $wRef->getProperty($pn);
                        $pp->setAccessible(true);
                        $sc = $pp->getValue($soapWrapper);
                        if ($sc instanceof \SoapClient) {
                            // Попытка насильно сменить endpoint
                            try { @$sc->__setLocation($host); } catch (\Throwable $e) {}
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Молча игнорируем — если что, просто останется дефолтный хост библиотеки
            error_log('[LAXIMO][forceHost] ' . $e->getMessage());
        }
    }
}
