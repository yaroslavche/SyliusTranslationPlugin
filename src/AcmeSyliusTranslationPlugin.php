<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AcmeSyliusTranslationPlugin extends Bundle
{
    use SyliusPluginTrait;
}
