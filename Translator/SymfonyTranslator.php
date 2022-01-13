<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\Translator;

use ChameleonSystem\SanityCheck\Translator\CheckTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SymfonyTranslator implements CheckTranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * SymfonyTranslator constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
