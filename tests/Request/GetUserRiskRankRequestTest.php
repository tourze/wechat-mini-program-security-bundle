<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use WechatMiniProgramSecurityBundle\Request\GetUserRiskRankRequest;

/**
 * @internal
 */
#[CoversClass(GetUserRiskRankRequest::class)]
final class GetUserRiskRankRequestTest extends RequestTestCase
{
    public function testRequestInstantiation(): void
    {
        $request = new GetUserRiskRankRequest();

        $openId = 'test_open_id';
        $scene = 1;
        $clientIp = '192.168.1.1';
        $mobileNumber = '13812345678';
        $emailAddress = 'test@example.com';
        $extendedInfo = 'extra info';

        $request->setOpenId($openId);
        $request->setScene($scene);
        $request->setClientIp($clientIp);
        $request->setMobileNumber($mobileNumber);
        $request->setEmailAddress($emailAddress);
        $request->setExtendedInfo($extendedInfo);
        $request->setIsTest(true);

        $this->assertInstanceOf(GetUserRiskRankRequest::class, $request);
        $this->assertSame($openId, $request->getOpenId());
        $this->assertSame($scene, $request->getScene());
        $this->assertSame($clientIp, $request->getClientIp());
        $this->assertSame($mobileNumber, $request->getMobileNumber());
        $this->assertSame($emailAddress, $request->getEmailAddress());
        $this->assertSame($extendedInfo, $request->getExtendedInfo());
        $this->assertTrue($request->isTest());
    }
}
