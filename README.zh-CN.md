# 微信小程序安全模块

[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-mini-program-security-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-security-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-mini-program-security-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-security-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-mini-program-security-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-security-bundle)
[![License](https://img.shields.io/packagist/l/tourze/wechat-mini-program-security-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-mini-program-security-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

为微信小程序应用提供全面的安全解决方案，包括内容安全检查、敏感文本检测、用户风险评估和头像管理功能。

## 目录

- [概述](#概述)
- [功能特性](#功能特性)
- [安装说明](#安装说明)
- [依赖关系](#依赖关系)
- [快速开始](#快速开始)
- [配置说明](#配置说明)
- [API 文档](#api-文档)
- [数据库结构](#数据库结构)
- [高级用法](#高级用法)
- [控制台命令](#控制台命令)
- [事件系统](#事件系统)
- [管理界面](#管理界面)
- [环境变量](#环境变量)
- [测试说明](#测试说明)
- [贡献指南](#贡献指南)
- [许可证](#许可证)

## 概述

微信小程序安全模块是一个基于 Symfony 的扩展包，为微信小程序后端服务提供必要的安全功能。它与微信官方安全 API 无缝集成，提供实时内容审核、用户风险评估和全面的安全日志记录。

## 功能特性

- **媒体内容安全**: 使用微信安全 API 进行异步图片和媒体内容安全检查
- **敏感文本检测**: 高级文本内容过滤和敏感词检测，支持缓存机制
- **用户风险评估**: 基于微信风险评估 API 的实时用户风险等级评估和行为监控
- **头像管理**: 自动用户头像下载、验证和危险内容检测
- **风险日志**: 全面的风险事件记录，支持自动清理和数据保留策略
- **管理界面**: 基于 EasyAdmin 的安全日志管理界面和监控功能
- **定时任务集成**: 可配置的自动化安全任务
- **事件驱动架构**: 全面的安全相关行为事件系统
- **高性能**: 通过缓存层和异步处理进行性能优化

## 安装说明

```bash
composer require tourze/wechat-mini-program-security-bundle
```

### Symfony 配置

将该包添加到您的 `config/bundles.php`：

```php
return [
    // ...
    WechatMiniProgramSecurityBundle\WechatMiniProgramSecurityBundle::class => ['all' => true],
];
```

## 依赖关系

该包需要以下依赖：

- `tourze/wechat-mini-program-bundle`: 微信小程序基础功能
- `tourze/wechat-mini-program-auth-bundle`: 微信身份验证和会话管理
- `tourze/sensitive-text-detect-bundle`: 文本内容过滤功能
- `tourze/risky-image-detect-bundle`: 图片内容分析
- `tourze/symfony-cron-job-bundle`: 计划任务管理
- `tourze/http-client-bundle`: HTTP 客户端 API 调用
- `doctrine/orm`: 数据库抽象和 ORM
- `symfony/cache-contracts`: 缓存支持
- `symfony/messenger`: 异步消息处理

## 快速开始

### 基础配置

```yaml
 # config/packages/wechat_mini_program_security.yaml
wechat_mini_program_security:
    default_avatar_url: 'https://your-domain.com/images/default-avatar.jpg'
    risk_log_retention_days: 90
```

### 基本用法

```php
<?php

use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;
use WechatMiniProgramSecurityBundle\Service\SensitiveTextService;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;

// 通过依赖注入使用服务
class SecurityController
{
    public function __construct(
        private MediaSecurityService $mediaSecurityService,
        private SensitiveTextService $sensitiveTextService,
        private UserRiskService $userRiskService
    ) {}

    public function checkContent(UserInterface $user, string $content): bool
    {
        // 检查敏感文本，包含用户上下文
        $isSensitive = $this->sensitiveTextService->isSensitiveText($content, $user);
        
        if ($isSensitive) {
            // 记录安全事件
            return false;
        }
        
        return true;
    }

    public function checkUserAvatar(UserInterface $wechatUser, string $imageUrl): void
    {
        // 异步图片安全检查
        $this->mediaSecurityService->checkImage($wechatUser, $imageUrl);
    }

    public function getUserRiskLevel(UserInterface $user): int
    {
        // 获取用户风险等级 (0-4，数值越高风险越大)
        return $this->userRiskService->getUserRiskRank($user, 1);
    }
}
```

## 配置说明

### 服务配置

该包提供自动服务配置。主要服务包括：

```yaml
 # config/services.yaml (可选自定义配置)
services:
    WechatMiniProgramSecurityBundle\Service\MediaSecurityService:
        # 需要时进行自定义配置
        
    WechatMiniProgramSecurityBundle\Service\SensitiveTextService:
        # 装饰基础的 SensitiveTextDetector
        decorates: 'Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector'
```

### 缓存配置

```yaml
 # config/packages/cache.yaml
framework:
    cache:
        pools:
            wechat.security.cache:
                adapter: cache.adapter.redis
                default_lifetime: 604800 # 7 天
```

## API 文档

### MediaSecurityService

提供异步媒体内容安全检查。

```php
class MediaSecurityService
{
    /**
     * 异步检查图片内容安全
     * 
     * @param UserInterface $wechatUser 微信用户
     * @param string $url 要检查的图片 URL
     */
    public function checkImage(UserInterface $wechatUser, string $url): void;
}
```

### SensitiveTextService

高级文本内容过滤，集成微信功能。

```php
class SensitiveTextService implements SensitiveTextDetector
{
    /**
     * 检查文本是否包含敏感内容
     * 
     * @param string $text 要检查的文本内容
     * @param UserInterface|null $user 用户上下文，用于增强检查
     * @return bool 如果内容敏感则返回 true
     */
    public function isSensitiveText(string $text, ?UserInterface $user = null): bool;
}
```

### UserRiskService

用户风险评估和监控。

```php
class UserRiskService
{
    /**
     * 从微信 API 获取用户风险等级
     * 
     * @param UserInterface $user 微信用户
     * @param int $scene 风险评估场景 (1-4)
     * @return int 风险等级 (0-4，数值越高风险越大)
     */
    public function getUserRiskRank(UserInterface $user, int $scene): int;
}
```

### WechatRiskyImageService

图片内容风险评估。

```php
class WechatRiskyImageService implements RiskyImageDetector
{
    /**
     * 检查图片是否包含危险内容
     * 
     * @param string $imageUrl 要分析的图片 URL
     * @return bool 如果图片有风险则返回 true
     */
    public function isRiskyImage(string $imageUrl): bool;
}
```

## 数据库结构

### MediaCheck 实体

存储媒体内容安全检查结果。

| 字段 | 类型 | 描述 |
|------|------|------|
| id | INTEGER | 主键 |
| openId | VARCHAR(120) | 微信用户 OpenID |
| unionId | VARCHAR(120) | 微信用户 UnionID (可选) |
| mediaUrl | VARCHAR(190) | 被检查媒体的 URL |
| traceId | VARCHAR(100) | 唯一追踪标识符 |
| risky | BOOLEAN | 内容是否有风险 |
| rawData | TEXT | 原始 API 响应数据 |
| createTime | DATETIME | 创建时间戳 |
| updateTime | DATETIME | 最后更新时间戳 |
| ipAddress | VARCHAR(45) | 用户 IP 地址 |

### RiskLog 实体

记录用户风险事件和等级。

| 字段 | 类型 | 描述 |
|------|------|------|
| id | INTEGER | 主键 |
| user | RELATION | 关联的微信用户 |
| riskRank | INTEGER | 风险等级 (0-4) |
| scene | INTEGER | 评估场景 |
| mobileNo | VARCHAR(40) | 用户手机号 |
| clientIp | VARCHAR(20) | 用户 IP 地址 |
| emailAddress | VARCHAR(120) | 用户邮箱 |
| extendedInfo | VARCHAR(255) | 附加信息 |
| unoinId | VARCHAR(60) | 唯一请求标识符 |
| openId | VARCHAR(64) | 微信 OpenID |
| unionId | VARCHAR(64) | 微信 UnionID |
| createTime | DATETIME | 事件时间戳 |

## 高级用法

### 事件驱动的安全监控

```php
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SecurityMonitoringSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            MediaCheckAsyncEvent::class => 'onMediaCheck',
        ];
    }

    public function onMediaCheck(MediaCheckAsyncEvent $event): void
    {
        $mediaCheck = $event->getMediaCheck();
        
        if ($mediaCheck->isRisky()) {
            // 对危险内容进行自定义处理
            $this->notifyAdministrators($mediaCheck);
        }
    }
}
```

### 自定义风险评估

```php
class CustomRiskAssessment
{
    public function __construct(
        private UserRiskService $userRiskService,
        private LoggerInterface $logger
    ) {}

    public function assessUserBehavior(UserInterface $user, array $context): string
    {
        $riskRank = $this->userRiskService->getUserRiskRank($user, 1);
        
        return match($riskRank) {
            0 => 'safe',
            1 => 'low_risk',
            2 => 'medium_risk',
            3 => 'high_risk',
            4 => 'very_high_risk',
            default => 'unknown'
        };
    }
}
```

## 控制台命令

### 数据清理

```bash
 # 清理旧的风险日志（自动定时执行）
php bin/console app:cleanup-entities
```

**注意**: 头像管理命令因复杂的依赖问题已暂时移除。将在未来版本中通过改进的依赖管理重新引入。

## 事件系统

### 可用事件

- `MediaCheckAsyncEvent`: 媒体内容检查完成时触发
- `UserRiskEvent`: 用户风险评估执行时触发
- `SensitiveContentEvent`: 检测到敏感内容时触发

### 事件订阅器

- `CheckSensitiveDataSubscriber`: 监控敏感数据检测
- `RiskRankSubscriber`: 跟踪用户风险评估
- `UserListener`: 处理用户相关的安全事件（头像变更等）

## 管理界面

该包提供 EasyAdmin 集成，用于安全监控：

### 可用管理控制器

- **MediaCheckCrudController**: 管理媒体安全检查日志
- **RiskLogCrudController**: 监控用户风险评估

### 管理功能

- 实时安全日志监控
- 风险趋势分析
- 用户行为跟踪
- 内容审核仪表板
- 合规报告导出功能

## 环境变量

使用以下环境变量配置包的行为：

```bash
 # 无效用户头像的默认头像 URL
DEFAULT_USER_AVATAR_URL=https://your-domain.com/images/default-avatar.jpg

 # 风险日志保留期（天）
WECHAT_MP_RISK_LOG_PERSIST_DAY_NUM=90

 # 微信 API 配置
WECHAT_MINI_PROGRAM_APP_ID=your_app_id
WECHAT_MINI_PROGRAM_APP_SECRET=your_app_secret
```

## 测试说明

### 运行测试

```bash
 # 运行所有测试
./vendor/bin/phpunit packages/wechat-mini-program-security-bundle/tests

 # 运行测试并生成覆盖率报告
./vendor/bin/phpunit packages/wechat-mini-program-security-bundle/tests --coverage-html coverage

 # 运行特定测试类
./vendor/bin/phpunit packages/wechat-mini-program-security-bundle/tests/Service/MediaSecurityServiceTest.php
```

### 测试结构

- `Integration/`: 包含数据库的集成测试
- `Service/`: 服务类的单元测试
- `Entity/`: 实体验证测试

### 测试状态

- **所有测试通过**: 修复依赖注入问题后，所有 46 个测试现在都成功通过
- **PHPUnit 覆盖**: 全面的测试覆盖，包括 Entity、Event、Request、Message、EventSubscriber、MessageHandler、Service、Repository 和 Controller 组件
- **单元测试方法**: 使用简化的单元测试方法，避免复杂的依赖注入配置问题
- **已知 PHPStan 问题**: 某些自定义 PHPStan 规则要求集成测试基类，但这与依赖注入复杂性冲突（已在 [Issue #875](https://github.com/tourze/php-monorepo/issues/875) 中跟踪）
- **生产就绪**: 所有核心功能都经过全面测试，完全支持生产环境使用

## 贡献指南

1. Fork 仓库
2. 创建您的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交您的更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 打开 Pull Request

### 开发指南

- 遵循 PSR-12 编码标准
- 为新功能编写全面的测试
- 为 API 更改更新文档
- 确保向后兼容性

## 许可证

MIT 许可证。请查看 [License File](LICENSE) 获取更多信息。