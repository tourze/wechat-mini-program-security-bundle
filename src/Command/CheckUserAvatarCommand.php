<?php

namespace WechatMiniProgramSecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use FileSystemBundle\Service\MountManager;
use HttpClientBundle\Service\SmartHttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

#[AsCronTask('36 */8 * * *')]
#[AsCommand(name: 'wechat-mini-program:check-user-avatar', description: '检查用户头像并保存')]
class CheckUserAvatarCommand extends LockableCommand
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SmartHttpClient $httpClient,
        private readonly MountManager $mountManager,
        private readonly LoggerInterface $logger,
        private readonly MediaSecurityService $mediaSecurityService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = 200;
        $qb = $this->userRepository->createQueryBuilder('u')
            ->setMaxResults($limit);

        $like1 = $qb->expr()->like('u.avatarUrl', $qb->expr()->literal('https://thirdwx.qlogo.cn/%'));
        $like2 = $qb->expr()->like('u.avatarUrl', $qb->expr()->literal('https://wx.qlogo.cn/mmopen%'));
        $users = $qb->where("u.avatarUrl != '' and u.avatarUrl is not null")
            ->andWhere($like1)
            ->orWhere($like2)
            ->getQuery()
            ->toIterable();

        foreach ($users as $user) {
            /** @var User $user */
            if (empty($user->getAvatarUrl())) {
                continue;
            }

            try {
                $response = $this->httpClient->request('GET', $user->getAvatarUrl());
                $header = $response->getHeaders();
                if (!isset($header['x-errno']) && 'notexist:-6101' !== $header['x-info'][0]) {
                    $content = $response->getContent();
                    $key = $this->mountManager->saveContent($content, 'png', 'wechat-mp-user');
                    $url = $this->mountManager->getImageUrl($key);
                } else {
                    $url = $_ENV['DEFAULT_USER_AVATAR_URL'];
                }

                $this->logger->info('保存微信小程序用户头像', [
                    'user' => $user,
                    'new' => $url,
                ]);
                $user->setAvatarUrl($url);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                if ($_ENV['DEFAULT_USER_AVATAR_URL'] !== $url) {
                    // 进行多一次内容安全检测
                    try {
                        $this->mediaSecurityService->checkImage($user, $url);
                    } catch (\Throwable $exception) {
                        $this->logger->error('图片内容安全检测报错', [
                            'url' => $url,
                            'exception' => $exception,
                        ]);
                    }
                }
            } catch (\Throwable $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }
        }

        return Command::SUCCESS;
    }
}
