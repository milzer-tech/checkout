<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Ergo\Soap;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Carbon;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoAddressDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCoveredTravelersDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoCustomerNameTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoDestinationTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerPreContractualInformationDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoInsuranceCustomerTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoPlanSearchInsuranceCustomerDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestorDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServicesTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoRequestServiceTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoSearchTravelersTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\CommonTypes\ErgoTripTypeDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoCreatePreContractualInformationRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsuranceBookRQDto;
use Nezasa\Checkout\Integrations\Ergo\Dtos\Requests\ErgoInsurancePlanSearchRQDto;

/**
 * SOAP 1.1 envelopes for ERV: {@code soapenv} (envelope), {@code ns} (http://www.erv.de/eSoap/2019/09), {@code com} (common types).
 */
final class ErgoSoapDocumentBuilder
{
    private const string XMLNS = 'http://www.w3.org/2000/xmlns/';

    private const string SOAP_ENV = 'http://schemas.xmlsoap.org/soap/envelope/';

    private const string NS_ERV = 'http://www.erv.de/eSoap/2019/09';

    private const string NS_COM = 'http://www.erv.de/eSoap/2019/09/common';

    public static function insurancePlanSearch(ErgoInsurancePlanSearchRQDto $dto): string
    {
        $doc = self::newDocument();
        $body = self::appendEnvelopeShell($doc);

        $rq = $doc->createElementNS(self::NS_ERV, 'ERV_InsurancePlanSearchRQ');
        self::planSearchRootAttributes($rq, $dto);
        $body->appendChild($rq);

        $requestor = $dto->Requestor ?? throw new \LogicException('Requestor must be set (ErgoConnector::prepareSoapPayload).');
        $rq->appendChild(self::elementRequestor($doc, $requestor));
        $rq->appendChild(self::elementCoveredTrip($doc, $dto->CoveredTrip));
        $rq->appendChild(self::elementSearchTravelers($doc, $dto->Travelers));
        if ($dto->InsuranceCustomer instanceof ErgoPlanSearchInsuranceCustomerDto) {
            $rq->appendChild(self::elementPlanSearchInsuranceCustomer($doc, $dto->InsuranceCustomer));
        }

        return self::normalizeSerializedXml($doc->saveXML() ?: '');
    }

    public static function createPreContractualInformation(ErgoCreatePreContractualInformationRQDto $dto): string
    {
        $doc = self::newDocument();
        $body = self::appendEnvelopeShell($doc);

        $rq = $doc->createElementNS(self::NS_ERV, 'ERV_InsuranceCreatePreContractualInformationRQ');
        self::payloadStdAttributes($rq, $dto->MsgId, $dto->TimeStamp, $dto->Target);
        $body->appendChild($rq);

        $requestor = $dto->Requestor ?? throw new \LogicException('Requestor must be set.');
        $rq->appendChild(self::elementRequestor($doc, $requestor));
        $rq->appendChild(self::elementCoveredTravelers($doc, $dto->CoveredTravelers));
        if ($dto->QuoteIDRef !== null && $dto->QuoteIDRef !== '') {
            $rq->appendChild(self::ervText($doc, 'QuoteIDRef', $dto->QuoteIDRef));
        }
        $rq->appendChild(self::elementCoveredTrip($doc, $dto->CoveredTrip));
        $rq->appendChild(self::elementInsuranceCustomerPreContractual($doc, $dto->InsuranceCustomerPreContractualInformation));
        $rq->appendChild(self::elementRequestServices($doc, 'PreContractualInformationServices', $dto->PreContractualInformationServices));
        if ($dto->EmailPreContractualInformation->AdditionalEmail !== []) {
            $em = $doc->createElementNS(self::NS_ERV, 'EmailPreContractualInformation');
            foreach ($dto->EmailPreContractualInformation->AdditionalEmail as $addr) {
                $em->appendChild(self::comText($doc, 'AdditionalEmail', (string) $addr));
            }
            $rq->appendChild($em);
        }

        return self::normalizeSerializedXml($doc->saveXML() ?: '');
    }

    public static function insuranceBook(ErgoInsuranceBookRQDto $dto): string
    {
        $doc = self::newDocument();
        $body = self::appendEnvelopeShell($doc);

        $rq = $doc->createElementNS(self::NS_ERV, 'ERV_InsuranceBookRQ');
        self::payloadStdAttributes($rq, $dto->MsgId, $dto->TimeStamp, $dto->Target);
        $body->appendChild($rq);

        $requestor = $dto->Requestor ?? throw new \LogicException('Requestor must be set.');
        $rq->appendChild(self::elementRequestor($doc, $requestor));
        $rq->appendChild(self::ervText($doc, 'PreContractualInformationID', $dto->PreContractualInformationID));
        $rq->appendChild(self::elementCoveredTravelers($doc, $dto->CoveredTravelers));
        if ($dto->QuoteIDRef !== null && $dto->QuoteIDRef !== '') {
            $rq->appendChild(self::ervText($doc, 'QuoteIDRef', $dto->QuoteIDRef));
        }
        $rq->appendChild(self::elementCoveredTrip($doc, $dto->CoveredTrip));
        $rq->appendChild(self::elementInsuranceCustomerFull($doc, $dto->InsuranceCustomer));
        $rq->appendChild(self::elementRequestServices($doc, 'BookServices', $dto->BookServices));
        if ($dto->EmailPolicy->AdditionalEmail !== []) {
            $em = $doc->createElementNS(self::NS_ERV, 'EmailPolicy');
            foreach ($dto->EmailPolicy->AdditionalEmail as $addr) {
                $em->appendChild(self::comText($doc, 'AdditionalEmail', (string) $addr));
            }
            $rq->appendChild($em);
        }

        return self::normalizeSerializedXml($doc->saveXML() ?: '');
    }

    /**
     * Libxml emits redundant default-namespace declarations on {@code com:} children; ERV expects prefixes only from the envelope.
     */
    private static function normalizeSerializedXml(string $xml): string
    {
        $needle = ' xmlns="'.self::NS_COM.'"';

        return str_replace($needle, '', $xml);
    }

    private static function newDocument(): DOMDocument
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = false;

        return $doc;
    }

    private static function appendEnvelopeShell(DOMDocument $doc): DOMElement
    {
        $env = $doc->createElementNS(self::SOAP_ENV, 'soapenv:Envelope');
        $doc->appendChild($env);
        $env->setAttributeNS(self::XMLNS, 'xmlns:soapenv', self::SOAP_ENV);
        $env->setAttributeNS(self::XMLNS, 'xmlns:ns', self::NS_ERV);
        $env->setAttributeNS(self::XMLNS, 'xmlns:com', self::NS_COM);
        $env->appendChild($doc->createElementNS(self::SOAP_ENV, 'soapenv:Header'));
        $body = $doc->createElementNS(self::SOAP_ENV, 'soapenv:Body');
        $env->appendChild($body);

        return $body;
    }

    private static function planSearchRootAttributes(DOMElement $rq, ErgoInsurancePlanSearchRQDto $dto): void
    {
        $rq->setAttribute('MsgId', $dto->MsgId);
        $rq->setAttribute('EchoToken', $dto->EchoToken);
        $rq->setAttribute('TransactionContext', $dto->TransactionContext);
        $rq->setAttribute('TimeStamp', self::formatTimestamp($dto->TimeStamp));
        $rq->setAttribute('Target', $dto->Target);
        $rq->setAttribute('AutoQuote', $dto->AutoQuote ? 'true' : 'false');
        $rq->setAttribute('ListType', $dto->ListType);
    }

    private static function payloadStdAttributes(DOMElement $rq, string $msgId, Carbon $timeStamp, string $target): void
    {
        $rq->setAttribute('MsgId', $msgId);
        $rq->setAttribute('TimeStamp', self::formatTimestamp($timeStamp));
        $rq->setAttribute('Target', $target);
    }

    private static function formatTimestamp(Carbon $t): string
    {
        return $t->copy()->utc()->format('Y-m-d\TH:i:sP');
    }

    private static function elementRequestor(DOMDocument $doc, ErgoRequestorDto $r): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'Requestor');
        $el->appendChild(self::comText($doc, 'CRS', $r->CRS));
        $el->appendChild(self::comText($doc, 'CRSAgency', $r->CRSAgency));
        $el->appendChild(self::comText($doc, 'Initiator', $r->Initiator));
        $el->appendChild(self::comText($doc, 'Agent', $r->Agent));
        $loc = $doc->createElementNS(self::NS_COM, 'Localisation');
        $loc->appendChild(self::comText($doc, 'Country', $r->Localisation->Country));
        $loc->appendChild(self::comText($doc, 'Language', $r->Localisation->Language));
        $loc->appendChild(self::comText($doc, 'Currency', $r->Localisation->Currency));
        $el->appendChild($loc);

        return $el;
    }

    private static function elementCoveredTrip(DOMDocument $doc, ErgoTripTypeDto $t): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'CoveredTrip');
        $el->appendChild(self::comText($doc, 'StartDate', $t->StartDate->format('Y-m-d')));
        $el->appendChild(self::comText($doc, 'EndDate', $t->EndDate->format('Y-m-d')));
        $el->appendChild(self::elementDestination($doc, $t->Destination));
        if ($t->BookingConfirmation instanceof Carbon) {
            $el->appendChild(self::comText($doc, 'BookingConfirmation', $t->BookingConfirmation->format('Y-m-d')));
        }
        $cost = $doc->createElementNS(self::NS_COM, 'TotalTripCost');
        $tc = $t->TotalTripCost;
        $cost->setAttribute('Amount', $tc->Amount);
        $cost->setAttribute('CurrencyCode', $tc->CurrencyCode);
        $el->appendChild($cost);

        return $el;
    }

    private static function elementDestination(DOMDocument $doc, ErgoDestinationTypeDto $d): DOMElement
    {
        $el = $doc->createElementNS(self::NS_COM, 'Destination');
        foreach ($d->Country as $code) {
            $el->appendChild(self::comText($doc, 'Country', (string) $code));
        }

        return $el;
    }

    private static function elementSearchTravelers(DOMDocument $doc, ErgoSearchTravelersTypeDto $travelers): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'Travelers');
        foreach ($travelers->Traveler as $row) {
            $tr = $doc->createElementNS(self::NS_COM, 'Traveler');
            $tr->setAttribute('ID', (string) $row->ID);
            $tr->appendChild(self::comText($doc, 'Birthdate', $row->Birthdate->format('Y-m-d')));
            $el->appendChild($tr);
        }

        return $el;
    }

    private static function elementPlanSearchInsuranceCustomer(DOMDocument $doc, ErgoPlanSearchInsuranceCustomerDto $c): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'InsuranceCustomer');
        $el->appendChild(self::comText($doc, 'ResidenceCountryCode', $c->ResidenceCountryCode));

        return $el;
    }

    private static function elementCoveredTravelers(DOMDocument $doc, ErgoCoveredTravelersDto $dto): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'CoveredTravelers');
        foreach ($dto->CoveredTraveler as $row) {
            $ct = $doc->createElementNS(self::NS_COM, 'CoveredTraveler');
            $ct->setAttribute('ID', (string) $row->ID);
            $cp = $doc->createElementNS(self::NS_COM, 'CoveredPerson');
            $pn = $doc->createElementNS(self::NS_COM, 'PersonName');
            $name = $row->CoveredPerson->PersonName;
            $pn->appendChild(self::comText($doc, 'NamePrefix', $name->NamePrefix->value));
            $pn->appendChild(self::comText($doc, 'GivenName', $name->GivenName));
            $pn->appendChild(self::comText($doc, 'Surname', $name->Surname));
            $cp->appendChild($pn);
            $cp->appendChild(self::comText($doc, 'Birthdate', $row->CoveredPerson->Birthdate->format('Y-m-d')));
            $ct->appendChild($cp);
            $el->appendChild($ct);
        }

        return $el;
    }

    private static function elementInsuranceCustomerPreContractual(DOMDocument $doc, ErgoInsuranceCustomerPreContractualInformationDto $c): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'InsuranceCustomerPreContractualInformation');
        $el->appendChild(self::elementCustomerName($doc, $c->PersonName));
        $el->appendChild(self::comText($doc, 'Email', $c->Email));
        $el->appendChild(self::elementAddress($doc, $c->Address));
        if ($c->Telephone !== null && $c->Telephone !== '') {
            $el->appendChild(self::comText($doc, 'Telephone', $c->Telephone));
        }
        if ($c->Mobile !== null && $c->Mobile !== '') {
            $el->appendChild(self::comText($doc, 'Mobile', $c->Mobile));
        }
        if ($c->Fax !== null && $c->Fax !== '') {
            $el->appendChild(self::comText($doc, 'Fax', $c->Fax));
        }

        return $el;
    }

    private static function elementInsuranceCustomerFull(DOMDocument $doc, ErgoInsuranceCustomerTypeDto $c): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, 'InsuranceCustomer');
        $el->appendChild(self::elementCustomerName($doc, $c->PersonName));
        $el->appendChild(self::comText($doc, 'Email', $c->Email));
        $el->appendChild(self::elementAddress($doc, $c->Address));

        return $el;
    }

    private static function elementCustomerName(DOMDocument $doc, ErgoCustomerNameTypeDto $n): DOMElement
    {
        $el = $doc->createElementNS(self::NS_COM, 'PersonName');
        $el->appendChild(self::comText($doc, 'NamePrefix', $n->NamePrefix->value));
        $el->appendChild(self::comText($doc, 'GivenName', $n->GivenName));
        $el->appendChild(self::comText($doc, 'Surname', $n->Surname));
        if ($n->NameTitle !== null && $n->NameTitle !== '') {
            $el->appendChild(self::comText($doc, 'NameTitle', $n->NameTitle));
        }

        return $el;
    }

    private static function elementAddress(DOMDocument $doc, ErgoAddressDto $a): DOMElement
    {
        $el = $doc->createElementNS(self::NS_COM, 'Address');
        $el->appendChild(self::comText($doc, 'StreetAndNr', $a->StreetAndNr));
        $el->appendChild(self::comText($doc, 'CityName', $a->CityName));
        $el->appendChild(self::comText($doc, 'PostalCode', $a->PostalCode));
        $el->appendChild(self::comText($doc, 'Country', $a->Country));

        return $el;
    }

    private static function elementRequestServices(DOMDocument $doc, string $containerLocalName, ErgoRequestServicesTypeDto $services): DOMElement
    {
        $wrap = $doc->createElementNS(self::NS_ERV, $containerLocalName);
        foreach ($services->Service as $svc) {
            $wrap->appendChild(self::elementRequestService($doc, $svc));
        }

        return $wrap;
    }

    private static function elementRequestService(DOMDocument $doc, ErgoRequestServiceTypeDto $svc): DOMElement
    {
        $el = $doc->createElementNS(self::NS_COM, 'Service');
        $el->setAttribute('ID', (string) $svc->ID);
        $quoted = $doc->createElementNS(self::NS_COM, 'QuotedTariff');
        $quoted->appendChild(self::comText($doc, 'TariffCode', $svc->QuotedTariff->TariffCode));
        $el->appendChild($quoted);
        $allocs = $doc->createElementNS(self::NS_COM, 'TravelerAllocations');
        foreach ($svc->TravelerAllocations->TravelerAllocation as $alloc) {
            $a = $doc->createElementNS(self::NS_COM, 'TravelerAllocation');
            $a->setAttribute('ID', (string) $alloc->ID);
            $a->setAttribute('TravelerIDRef', (string) $alloc->TravelerIDRef);
            $a->setAttribute('CoInsured', $alloc->CoInsured);
            $allocs->appendChild($a);
        }
        $el->appendChild($allocs);

        return $el;
    }

    private static function comText(DOMDocument $doc, string $local, string $text): DOMElement
    {
        $el = $doc->createElementNS(self::NS_COM, $local);
        $el->appendChild($doc->createTextNode($text));

        return $el;
    }

    private static function ervText(DOMDocument $doc, string $local, string $text): DOMElement
    {
        $el = $doc->createElementNS(self::NS_ERV, $local);
        $el->appendChild($doc->createTextNode($text));

        return $el;
    }
}
