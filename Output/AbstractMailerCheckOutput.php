<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\Output;

use ChameleonSystem\SanityCheck\Outcome\CheckOutcome;
use ChameleonSystem\SanityCheck\Output\AbstractTranslatingCheckOutput;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AbstractMailerCheckOutput is a base class from which different mailers can be derived.
 * It provides mostly methods for output formatting that can be used or overwritten.
 */
abstract class AbstractMailerCheckOutput extends AbstractTranslatingCheckOutput
{
    /**
     * @var array
     */
    protected $mailerParameters;
    /**
     * @var string|null
     */
    protected $mailerServiceId;
    /**
     * @var int|null
     */
    protected $level;
    /**
     * @var array
     */
    protected $outputData = array();
    /**
     * @var bool
     */
    protected $doOutput = false;

    /**
     * @param array               $mailerParameters
     * @param TranslatorInterface $translator
     * @param string              $translationDomain
     * @param string|null         $mailerServiceId
     * @param int|null            $level
     */
    public function __construct(
        array $mailerParameters,
        TranslatorInterface $translator,
        $translationDomain = 'chameleon_system_sanitycheck',
        $mailerServiceId = null,
        $level = null
    ) {
        parent::__construct($translator, $translationDomain);
        $this->mailerParameters = $mailerParameters;
        $this->mailerServiceId = $mailerServiceId;
        $this->level = (null !== $level) ? $level : CheckOutcome::OK;
    }

    /**
     * {@inheritdoc}
     */
    public function gather(CheckOutcome $outcome)
    {
        $message = $this->getTranslatedMessage($outcome);
        $this->outputData[] = $message;

        if ($outcome->getLevel() >= $this->level) {
            $this->doOutput = true;
        }
    }

    /**
     * @return string
     */
    protected function getSubject()
    {
        return $this->translate('message.mailsubject');
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        $body = '';
        $body .= $this->getBodyIntroduction()."\n";
        $body .= $this->getBodyMessages()."\n";
        $body .= $this->getBodyFooter()."\n";

        return $body;
    }

    /**
     * @return string
     */
    protected function getBodyIntroduction()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getBodyMessages()
    {
        $messages = '';
        foreach ($this->outputData as $data) {
            $messages .= $data."\n";
        }

        return $messages;
    }

    /**
     * @return string
     */
    protected function getBodyFooter()
    {
        return '';
    }

    /**
     * @param string $message
     * @param int    $outcomeLevel
     *
     * @return string
     */
    protected function getTextDecoration($message, $outcomeLevel)
    {
        switch ($outcomeLevel) {
            case CheckOutcome::OK:
                return '<info>'.$message.'</info>';
            case CheckOutcome::NOTICE:
                return '<info>'.$message.'</info>';
            case CheckOutcome::WARNING:
                return '<comment>'.$message.'</comment>';
            case CheckOutcome::ERROR:
                return '<error>'.$message.'</error>';
            case CheckOutcome::EXCEPTION:
                return '<error>'.$message.'/<error>';
            default:
                return '<error>'.$message.'</error>';
        }
    }

    /**
     * @param string $mailerServiceId
     */
    public function setMailerServiceId($mailerServiceId)
    {
        $this->mailerServiceId = $mailerServiceId;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }
}
