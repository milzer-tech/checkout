<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Foundation\Traits\Connector;

use ReflectionClass;
use Soap\Encoding\Driver;
use Soap\Encoding\EncoderRegistry;
use Soap\Wsdl\Loader\StreamWrapperLoader;
use Soap\WsdlReader\Wsdl1Reader;

/**
 * Connectors using this trait must declare a public `wsdlPath` string relative to the connector class file.
 *
 * @property string $wsdlPath
 */
trait SoapConnectorTrait
{
    private Driver $soapDriver;

    public function soapDriver(): Driver
    {
        if (! isset($this->soapDriver)) {
            $ref = new ReflectionClass($this);
            $fileName = $ref->getFileName();
            if ($fileName === false) {
                throw new \LogicException('Cannot resolve WSDL path for '.static::class);
            }
            $dir = dirname($fileName);
            $wsdlFile = $dir.'/'.ltrim($this->wsdlPath, '/');

            $wsdl = (new Wsdl1Reader(new StreamWrapperLoader))($wsdlFile);
            $this->soapDriver = Driver::createFromWsdl1($wsdl, null, $this->getSoapEncoder());
        }

        return $this->soapDriver;
    }

    public function getSoapEncoder(): EncoderRegistry
    {
        return EncoderRegistry::default();
    }
}
