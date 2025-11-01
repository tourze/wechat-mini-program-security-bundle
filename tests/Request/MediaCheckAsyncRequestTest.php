<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;

/**
 * @internal
 */
#[CoversClass(MediaCheckAsyncRequest::class)]
final class MediaCheckAsyncRequestTest extends RequestTestCase
{
    public function testRequestInstantiation(): void
    {
        $request = new MediaCheckAsyncRequest();

        $mediaType = 2;
        $mediaUrl = 'https://example.com/image.jpg';
        $openId = 'test_open_id';
        $scene = 1;

        $request->setMediaType($mediaType);
        $request->setMediaUrl($mediaUrl);
        $request->setOpenId($openId);
        $request->setScene($scene);
        $request->setVersion(2);

        $this->assertInstanceOf(MediaCheckAsyncRequest::class, $request);
        $this->assertSame($mediaType, $request->getMediaType());
        $this->assertSame($mediaUrl, $request->getMediaUrl());
        $this->assertSame($openId, $request->getOpenId());
        $this->assertSame($scene, $request->getScene());
        $this->assertSame(2, $request->getVersion());
    }
}
