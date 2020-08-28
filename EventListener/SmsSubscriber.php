<?php

namespace MauticPlugin\MauticAdvancedTemplatesBundle\EventListener;
use Mautic\CampaignBundle\Entity\Lead;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\SmsBundle\SmsEvents;
use Mautic\SmsBundle\Event as Events;
use Mautic\EmailBundle\Helper\PlainTextHelper;
use Mautic\CoreBundle\Exception as MauticException;
use MauticPlugin\MauticAdvancedTemplatesBundle\Helper\TemplateProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class EmailSubscriber.
 */
class SmsSubscriber extends CommonSubscriber
{
    /**
     * @var TokenHelper $tokenHelper ;
     */
    protected $templateProcessor;


    /**
     * EmailSubscriber constructor.
     *
     * @param TokenHelper $tokenHelper
     */
    public function __construct(TemplateProcessor $templateProcessor)
    {
        $this->templateProcessor = $templateProcessor;
    }
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SmsEvents::SMS_ON_SEND => ['onSmsGenerate', 300],
            SmsEvents::SMS_ON_DISPLAY => ['onSmsGenerate', 0],
        ];
    }

    /**
     * Search and replace tokens with content
     *
     * @param Events\SmsSendEvent $event
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function onSmsGenerate(Events\SmsSendEvent $event)
    {
        $this->logger->info('onSmsGenerate MauticAdvancedTemplatesBundle\SmsSubscriber');

        if ($event->getSms()) {
            $content = $event->getSms()->getMessage();
        }else{
            $content = $event->getMessage();
        }

        $content = $this->templateProcessor->processTemplate($content,  $event->getLead());
        $event->setContent($content);


        if ( empty( trim($event->getPlainText()) ) ) {
            $event->setPlainText( (new PlainTextHelper($content))->getText() );
        }
    }
}