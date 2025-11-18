<?php

namespace WechatMiniProgramSecurityBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramSecurityBundle\Controller\Admin\WechatMiniProgramSecurityRiskLogCrudController;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramSecurityRiskLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramSecurityRiskLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getAdminFqcn(): string
    {
        return 'App\Entity\Admin';
    }

    protected function getControllerService(): WechatMiniProgramSecurityRiskLogCrudController
    {
        return new WechatMiniProgramSecurityRiskLogCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield 'Create Time' => ['Create Time'];
        yield 'Risk Rank' => ['Risk Rank'];
        yield 'Scene' => ['Scene'];
        yield 'Mobile No' => ['Mobile No'];
        yield 'Client IP' => ['Client IP'];
        yield 'Email Address' => ['Email Address'];
        yield 'Extended Info' => ['Extended Info'];
        yield 'Unoin ID' => ['Unoin ID'];
        yield 'Open ID' => ['Open ID'];
        yield 'Union ID' => ['Union ID'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // Return actual fields even though NEW is disabled - tests will be skipped
        yield 'id' => ['id'];
        yield 'createTime' => ['createTime'];
        yield 'riskRank' => ['riskRank'];
        yield 'scene' => ['scene'];
        yield 'mobileNo' => ['mobileNo'];
        yield 'clientIp' => ['clientIp'];
        yield 'emailAddress' => ['emailAddress'];
        yield 'extendedInfo' => ['extendedInfo'];
        yield 'unoinId' => ['unoinId'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // Return actual fields even though EDIT is disabled - tests will be skipped
        yield 'id' => ['id'];
        yield 'createTime' => ['createTime'];
        yield 'riskRank' => ['riskRank'];
        yield 'scene' => ['scene'];
        yield 'mobileNo' => ['mobileNo'];
        yield 'clientIp' => ['clientIp'];
        yield 'emailAddress' => ['emailAddress'];
        yield 'extendedInfo' => ['extendedInfo'];
        yield 'unoinId' => ['unoinId'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
    }

    public function testUnauthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(AccessDeniedException::class);
        $url = $this->generateAdminUrl('index');
        $client->request('GET', $url);
    }

    public function testAuthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'password123');
        $this->loginAsAdmin($client, 'admin@example.com', 'password123');
        self::getClient($client); // 正确设置客户端

        // 测试控制器的实体类型是否正确设置
        $controller = new WechatMiniProgramSecurityRiskLogCrudController();
        $this->assertEquals(RiskLog::class, $controller::getEntityFqcn());

        // 测试控制器配置方法是否正常工作
        $fields = $controller->configureFields('index');
        $this->assertNotEmpty(iterator_to_array($fields));

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters);

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud);

        // 测试实际页面访问
        $url = $this->generateAdminUrl('index');
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }
}
