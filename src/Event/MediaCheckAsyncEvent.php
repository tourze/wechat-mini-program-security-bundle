<?php

namespace WechatMiniProgramSecurityBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

class MediaCheckAsyncEvent extends Event
{
    public MediaCheck $mediaCheckLog;

    public function getMediaCheckLog(): MediaCheck
    {
        return $this->mediaCheckLog;
    }

    public function setMediaCheckLog(MediaCheck $mediaCheckLog): void
    {
        $this->mediaCheckLog = $mediaCheckLog;
    }
}
