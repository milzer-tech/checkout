<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\HanseMerkur\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the types of documents in the HanseMerkur Checkout API.
 *
 * @link https://api-fbt.hmrv.de/rest/swagger-ui/index.html#/Offer/createOfferV1
 *
 * @method bool isAvb()
 * @method bool isIpid()
 * @method bool isVi()
 * @method bool isLeistb()
 * @method bool isVbed()
 * @method bool isOther()
 */
enum HanseMerkurDocumentTypeEnum: string
{
    use PowerEnum;

    case Avb = 'AVB';
    case Ipid = 'IPID';
    case Vi = 'VI';
    case Leistb = 'LEISTB';
    case Vbed = 'VBED';
    case Other = 'OTHER';

    public function mustBeDisplayed(): bool
    {
        return $this->isAvb() || $this->isIpid();
    }
}
