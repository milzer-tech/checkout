<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Enums;

use AzimKordpour\PowerEnum\Traits\PowerEnum;

/**
 * Enum representing the status of transactions in Nezasa.
 *
 * @see https://support.nezasa.com/hc/en-gb/articles/20496375532177-Payment-API
 *
 * @method bool isOpen()
 * @method bool isInProgress()
 * @method bool isPreauthCaptureInProgress()
 * @method bool isPending()
 * @method bool isPreauth()
 * @method bool isClosed()
 * @method bool isFailed()
 * @method bool isDeleted()
 * @method bool isUnknown()
 */
enum NezasaTransactionStatusEnum: string
{
    use PowerEnum;

    case Open = 'Open';
    case InProgress = 'InProgress';
    case PreauthCaptureInProgress = 'PreauthCaptureInProgress';
    case Pending = 'Pending';
    case Preauth = 'Preauth';
    case Closed = 'Closed';
    case Failed = 'Failed';
    case Deleted = 'Deleted';
    case Unknown = 'Unknown';

}
