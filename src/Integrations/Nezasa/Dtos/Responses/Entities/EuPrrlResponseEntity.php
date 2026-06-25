<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Integrations\Nezasa\Dtos\Responses\Entities;

use Illuminate\Support\Collection;
use Nezasa\Checkout\Dtos\BaseDto;

class EuPrrlResponseEntity extends BaseDto
{
    /**
     * Create a new instance of the EuPrrlResponseEntity.
     *
     * @param  Collection<int, EuPrrlLinkResponseEntity>  $links
     */
    public function __construct(
        public bool $generalTermsConfirmationEnabled = false,
        public bool $itineraryContentValidationEnabled = false,
        public ?string $title = null,
        public ?string $intro = null,
        public ?string $checkboxText = null,
        public Collection $links = new Collection,
        public ?EuPrrlComplianceResponseEntity $compliance = null,
    ) {}

    /**
     * Get a stable key for the EU-PRRL acceptance based on displayed terms content.
     */
    public function getGeneralTermsKey(): string
    {
        return md5(json_encode([
            'title' => $this->title,
            'intro' => $this->intro,
            'checkboxText' => $this->checkboxText,
            'links' => $this->links
                ->map(fn (EuPrrlLinkResponseEntity $link): array => [
                    'url' => $link->url,
                    'linkText' => $link->linkText,
                ])
                ->values()
                ->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
