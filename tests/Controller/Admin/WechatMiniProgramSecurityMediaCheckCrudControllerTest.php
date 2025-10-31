<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramSecurityBundle\Controller\Admin\WechatMiniProgramSecurityMediaCheckCrudController;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramSecurityMediaCheckCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramSecurityMediaCheckCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getAdminFqcn(): string
    {
        return 'App\Entity\Admin';
    }

    protected function getControllerService(): WechatMiniProgramSecurityMediaCheckCrudController
    {
        return new WechatMiniProgramSecurityMediaCheckCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        return [
            'open_id' => ['openId'],
            'union_id' => ['unionId'],
            'media_url' => ['mediaUrl'],
            'trace_id' => ['traceId'],
            'risky' => ['risky'],
            'raw_data' => ['rawData'],
        ];
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
        $controller = new WechatMiniProgramSecurityMediaCheckCrudController();
        $this->assertEquals(MediaCheck::class, $controller::getEntityFqcn());

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

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(MediaCheck::class, WechatMiniProgramSecurityMediaCheckCrudController::getEntityFqcn());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'id' => ['ID'],
            'open_id' => ['Open ID'],
            'union_id' => ['Union ID'],
            'media_url' => ['Media URL'],
            'trace_id' => ['Trace ID'],
            'risky' => ['Is Risky'],
            'created_at' => ['Created At'],
            'updated_at' => ['Updated At'],
            'created_from_ip' => ['Created From IP'],
            'updated_from_ip' => ['Updated From IP'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        return [
            'open_id' => ['openId'],
            'union_id' => ['unionId'],
            'media_url' => ['mediaUrl'],
            'trace_id' => ['traceId'],
            'risky' => ['risky'],
            'raw_data' => ['rawData'],
        ];
    }
}
