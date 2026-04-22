<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Foundation\Request;

use Saloon\Http\Request;

/**
 * Base for SOAP requests (host app may extend for logging/branding).
 */
abstract class ParentSaloonRequest extends Request {}
