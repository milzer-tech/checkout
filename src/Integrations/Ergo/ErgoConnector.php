<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo;

use Illuminate\Support\Facades\Config;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoLocalisationDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Enum\ErgoNamePrefixEnum;
use Nezasa\Checkout\Integrations\Ergo\Resources\ErgoInsurance;
use Nezasa\Checkout\Integrations\Foundation\Contracts\SoapConnector;
use Nezasa\Checkout\Integrations\Foundation\Traits\Connector\SoapConnectorTrait;
use Saloon\Http\Connector;
use Saloon\Traits\Makeable;
use Soap\Encoding\EncoderRegistry;

class ErgoConnector extends Connector implements SoapConnector
{
    use Makeable;
    use SoapConnectorTrait;

    public string $wsdlPath = 'wsdl/eSoap.wsdl';

    public function resolveBaseUrl(): string
    {
        return rtrim(Config::string('checkout.insurance.ergo.base_url'), '/').'/';
    }

    public function insurance(): ErgoInsurance
    {
        return new ErgoInsurance($this);
    }

    public function getSoapEncoder(): EncoderRegistry
    {
        return EncoderRegistry::default()
            ->addBackedEnum('http://www.erv.de/eSoap/2019/09/common', 'CustomerNameTypeNamePrefix', ErgoNamePrefixEnum::class)
            ->addBackedEnum('http://www.erv.de/eSoap/2019/09/common', 'PersonNameTypeNamePrefix', ErgoNamePrefixEnum::class);
    }

    public function prepareSoapPayload(mixed $payload): mixed
    {
        $requestor = new ErgoRequestorDto(
            CRS: Config::string('checkout.insurance.ergo.crs'),
            CRSAgency: Config::string('checkout.insurance.ergo.crs_agency'),
            Initiator: Config::string('checkout.insurance.ergo.initiator'),
            Agent: Config::string('checkout.insurance.ergo.agent'),
            Localisation: new ErgoLocalisationDto(
                Config::string('checkout.insurance.ergo.locale_country'),
                Config::string('checkout.insurance.ergo.locale_language'),
                Config::string('checkout.insurance.ergo.locale_currency'),
            )
        );

        return $payload->setRequestor($requestor);
    }
}
