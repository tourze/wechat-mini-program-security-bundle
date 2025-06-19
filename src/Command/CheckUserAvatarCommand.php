<?php

namespace WechatMiniProgramSecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Service\SmartHttpClient;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\FileNameGenerator\RandomNameGenerator;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[AsCronTask('36 */8 * * *')]
#[AsCommand(name: 'wechat-mini-program:check-user-avatar', description: '检查用户头像并保存')]
class CheckUserAvatarCommand extends LockableCommand
{
    private const NAME = 'wechat-mini-program:check-user-avatar';
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SmartHttpClient $httpClient,
        private readonly RandomNameGenerator $randomNameGenerator,
        private readonly FilesystemOperator $filesystem,
        private readonly LoggerInterface $logger,
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
        $users = $qb->where("u.avatarUrl <> '' and u.avatarUrl is not null")
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
                    $key = $this->randomNameGenerator->generateDateFileName('png', 'wechat-mp-user');
                    $this->filesystem->write($key, $content);
                    $url = $this->filesystem->publicUrl($key);
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
            } catch (\Throwable $exception) {
                $output->writeln($exception->getMessage());
                continue;
            }
        }

        return Command::SUCCESS;
    }
}
