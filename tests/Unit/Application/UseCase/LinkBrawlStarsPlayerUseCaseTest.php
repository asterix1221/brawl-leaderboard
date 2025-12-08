<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\Service\BrawlStarsService;
use App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase;
use App\Domain\Entity\Player;
use App\Domain\Entity\User;
use App\Domain\Exception\PlayerNotFoundException;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkBrawlStarsPlayerUseCaseTest extends TestCase
{
    private PlayerRepositoryInterface|MockObject $playerRepository;
    private UserRepositoryInterface|MockObject $userRepository;
    private BrawlStarsService|MockObject $brawlStarsService;
    private LinkBrawlStarsPlayerUseCase $useCase;
    private Uuid $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerRepository = $this->createMock(PlayerRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->brawlStarsService = $this->createMock(BrawlStarsService::class);
        $this->userId = new Uuid('33333333-3333-3333-3333-333333333333');

        $this->useCase = new LinkBrawlStarsPlayerUseCase(
            $this->playerRepository,
            $this->userRepository,
            $this->brawlStarsService
        );
    }

    public function testCreatesNewPlayerWhenNotExists(): void
    {
        $user = new User($this->userId, new Email('user@example.com'), 'hash', 'User');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->userId)
            ->willReturn($user);

        $apiData = ['name' => 'Brawler', 'trophies' => 500, 'club' => ['tag' => 'RU']];
        $this->brawlStarsService
            ->expects($this->once())
            ->method('getPlayerById')
            ->with('PLAYER123')
            ->willReturn($apiData);

        $this->playerRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new PlayerId('PLAYER123'))
            ->willReturn(null);

        $this->playerRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Player $player) {
                return $player->getId()->getValue() === 'PLAYER123'
                    && $player->getNickname() === 'Brawler'
                    && $player->getTotalTrophies()->getValue() === 500
                    && $player->getRegion() === 'RU';
            }));

        $result = $this->useCase->execute($this->userId, 'PLAYER123');

        $this->assertSame('PLAYER123', $result->getId()->getValue());
        $this->assertSame('Brawler', $result->getNickname());
        $this->assertSame(500, $result->getTotalTrophies()->getValue());
    }

    public function testUpdatesExistingPlayerWithLatestData(): void
    {
        $user = new User($this->userId, new Email('user@example.com'), 'hash', 'User');
        $existing = new Player(new PlayerId('PLAYER999'), 'OldName', new Trophy(10), 'OLD');

        $this->userRepository->method('findById')->willReturn($user);

        $apiData = ['name' => 'Updated', 'trophies' => 1000, 'club' => ['tag' => 'EU']];
        $this->brawlStarsService->method('getPlayerById')->willReturn($apiData);

        $this->playerRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($existing);

        $this->playerRepository
            ->expects($this->once())
            ->method('save')
            ->with($existing);

        $result = $this->useCase->execute($this->userId, 'PLAYER999');

        $this->assertSame('Updated', $result->getNickname());
        $this->assertSame(1000, $result->getTotalTrophies()->getValue());
        $this->assertSame('EU', $result->getRegion());
    }

    public function testThrowsWhenPlayerNotFoundInApi(): void
    {
        $user = new User($this->userId, new Email('user@example.com'), 'hash', 'User');
        $this->userRepository->method('findById')->willReturn($user);

        $this->brawlStarsService
            ->expects($this->once())
            ->method('getPlayerById')
            ->with('UNKNOWN')
            ->willReturn(null);

        $this->expectException(PlayerNotFoundException::class);
        $this->useCase->execute($this->userId, 'UNKNOWN');
    }
}
