<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatMiniProgramSecurityBundle\Request\MsgSecurityCheckRequest;

/**
 * @internal
 */
#[CoversClass(MsgSecurityCheckRequest::class)]
final class MsgSecurityCheckRequestTest extends RequestTestCase
{
    public function testRequestInstantiation(): void
    {
        $request = new MsgSecurityCheckRequest();

        $content = 'test message content';
        $openId = 'test_open_id';
        $scene = 2;
        $nickname = 'test_user';
        $title = 'test title';

        $request->setContent($content);
        $request->setOpenId($openId);
        $request->setScene($scene);
        $request->setNickname($nickname);
        $request->setTitle($title);
        $request->setVersion(2);

        $this->assertInstanceOf(MsgSecurityCheckRequest::class, $request);
        $this->assertSame($content, $request->getContent());
        $this->assertSame($openId, $request->getOpenId());
        $this->assertSame($scene, $request->getScene());
        $this->assertSame($nickname, $request->getNickname());
        $this->assertSame($title, $request->getTitle());
        $this->assertSame(2, $request->getVersion());
        $this->assertSame(3, $request->getMaxRetries());
    }
}
