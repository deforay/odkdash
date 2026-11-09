<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Application\I18n\Translator;

/**
 * The translate() helper the views call, ~2900 times.
 *
 * The translator behind it is Application\I18n\Translator.
 */
class Translate
{
    public function __construct(private readonly Translator $translator)
    {
    }

    public function __invoke(string $message, ?string $textDomain = null, ?string $locale = null): string
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }
}
