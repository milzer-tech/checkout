<?php

declare(strict_types=1);

namespace Nezasa\Checkout\Actions\Operation;

use Nezasa\Checkout\Enums\Section;
use Nezasa\Checkout\Models\Checkout;

final class SaveSectionStatusAction
{
    public function run(Checkout $checkout, Section $section, bool $isCompleted, ?bool $isExpanded = null): void
    {
        $data = [
            'status.'.$section->value.'.isCompleted' => $isCompleted,
        ];

        if ($isExpanded !== null) {
            $data['status.'.$section->value.'.isExpanded'] = $isExpanded;
        }

        $checkout->updateData($data);
    }
}
