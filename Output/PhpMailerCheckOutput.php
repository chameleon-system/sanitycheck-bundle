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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PhpMailerCheckOutput is used to write a CheckOutcome to an email address.
 */
class PhpMailerCheckOutput extends AbstractMailerCheckOutput
{
    /**
     * using container injection because of the phpmailer service.
     * It may not be a shared service instance as it needs to be re-created on every use.
     * If someone manages to replace this with a synchronized service (http://symfony.com/doc/current/cookbook/service_container/scopes.html#a-using-a-synchronized-service),
     * this would be a better solution. Unfortunately this doesn't seem to work with service sharing.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * PhpMailerCheckOutput constructor.
     *
     * @param ContainerInterface  $container
     * @param array               $mailerParameters
     * @param TranslatorInterface $translator
     * @param string              $translationDomain
     * @param string|null         $mailerServiceId
     * @param int|null            $level
     */
    public function __construct(
        ContainerInterface $container,
        array $mailerParameters,
        TranslatorInterface $translator,
        $translationDomain = 'chameleon_system_sanitycheck',
        $mailerServiceId = null,
        $level = null
    ) {
        parent::__construct($mailerParameters, $translator, $translationDomain, $mailerServiceId, $level);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if (empty($this->outputData)) {
            return;
        }
        if (!$this->doOutput) {
            $this->outputData = array();

            return;
        }

        /**
         * @var \PHPMailer\PHPMailer\PHPMailer|PHPMailer $mailer
         */
        if (null !== $this->mailerServiceId) {
            $mailer = $this->container->get($this->mailerServiceId);
        } else {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mailer = new \PHPMailer\PHPMailer\PHPMailer();
            } else {
                $mailer = new \PHPMailer();
            }
        }

        $mailer->addAddress($this->mailerParameters['to']);
        $mailer->setFrom($this->mailerParameters['from']);

        $mailer->Subject = $this->getSubject();
        $mailer->Body = $this->getBody();

        $mailer->send();

        $this->outputData = array();
    }
}
