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
        error_log('[LAXIMO] listCatalogs() start');
        $res = $this->oem->listCatalogs();
        error_log('[LAXIMO] listCatalogs() done');
        return $res;
    }

    public function getCatalogInfo(string $code): array
    {
        error_log("[LAXIMO] getCatalogInfo({$code}) start");
        $res = $this->oem->getCatalogInfo($code);
        error_log("[LAXIMO] getCatalogInfo({$code}) done");
        return $res;
    }

    public function catalogsWithInfo(string $code): array
    {
        error_log("[LAXIMO] catalogsWithInfo({$code}) start");
        $res = $this->oem->queryButch([
            \GuayaquilLib\Oem::listCatalogs(),
            \GuayaquilLib\Oem::getCatalogInfo($code),
        ]);
        error_log("[LAXIMO] catalogsWithInfo({$code}) done");
        return $res;
    }

    /** Поиск авто по VIN + попытка показать конечную SOAP-точку */
    public function findVehicleByVin(string $vin): array
    {
        error_log("[LAXIMO] findVehicleByVin({$vin}) start");

        // 1) основной вызов
        $res = $this->oem->findVehicleByVin($vin);

        // 2) попробуем «подглядеть» URL у SoapClient через рефлексию
        try {
            $ref = new \ReflectionObject($this->oem);
            foreach (['soap', 'client', 'soapClient'] as $propName) {
                if ($ref->hasProperty($propName)) {
                    $prop = $ref->getProperty($propName);
                    $prop->setAccessible(true);
                    $soap = $prop->getValue($this->oem);
                    if ($soap instanceof \SoapClient) {
                        // __getLastRequestHeaders может содержать Host/Action/Endpoint
                        $headers = @$soap->__getLastRequestHeaders() ?: '';
                        $lastReq = @$soap->__getLastRequest() ?: '';
                        $lastRes = @$soap->__getLastResponse() ?: '';
                        // В заголовках обычно виден реальный URL/Host
                        error_log("[LAXIMO][SOAP headers]\n" . substr($headers, 0, 1000));
                        // По желанию можно логировать и тело запроса/ответа (осторожно с объёмом/секретами!)
                        // error_log("[LAXIMO][SOAP request]\n" . substr($lastReq, 0, 2000));
                        // error_log("[LAXIMO][SOAP response]\n" . substr($lastRes, 0, 2000));
                        break;
                    }
                }
            }
        } catch (\Throwable $t) {
            error_log('[LAXIMO][TRACE-ERR] ' . $t->getMessage());
        }

        error_log("[LAXIMO] findVehicleByVin({$vin}) done");
        return $res;
    }
}
