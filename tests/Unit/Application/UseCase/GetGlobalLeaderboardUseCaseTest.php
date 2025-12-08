<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\Service\CacheService;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetGlobalLeaderboardUseCaseTest extends TestCase
{
    private PlayerRepositoryInterface|MockObject $playerRepository;
    private CacheService|MockObject $cacheService;
    private GetGlobalLeaderboardUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerRepository = $this->createMock(PlayerRepositoryInterface::class);
        $this->cacheService = $this->createMock(CacheService::class);

        $this->useCase = new GetGlobalLeaderboardUseCase(
            $this->playerRepository,
            $this->cacheService
        );
    }

    public function testReturnsCachedLeaderboardWhenAvailable(): void
    {
        $cached = [
            'entries' => [
                ['rank' => 1, 'playerId' => '123', 'nickname' => 'Test', 'totalTrophies' => 999, 'region' => 'GLOBAL', 'level' => 4]
            ],
            'total' => 1,
            'hasMore' => false,
            'page' => 1
        ];

        $this->cacheService
            ->expects($this->once())
            ->method('get')
            ->with('leaderboard:global:0:100')
            ->willReturn(json_encode($cached));

        $this->playerRepository->expects($this->never())->method('findTopByTrophies');

        $result = $this->useCase->execute();

        $this->assertSame($cached, $result);
    }

    public function testFetchesFromRepositoryAndCachesWhenCacheMiss(): void
    {
        $playerOne = new Player(new PlayerId('AAA'), 'PlayerOne', new Trophy(1200), 'EU');
        $playerTwo = new Player(new PlayerId('BBB'), 'PlayerTwo', new Trophy(3000), 'NA');

        $this->cacheService
            ->expects($this->once())
            ->method('get')
            ->with('leaderboard:global:0:2')
            ->willReturn(null);

        $this->playerRepository
            ->expects($this->once())
            ->method('findTopByTrophies')
            ->with(2, 0)
            ->willReturn([$playerOne, $playerTwo]);

        $this->playerRepository
            ->expects($this->once())
            ->method('countAll')
            ->willReturn(2);

        $this->cacheService
            ->expects($this->once())
            ->method('set')
            ->with(
                'leaderboard:global:0:2',
                $this->callback(function ($payload) {
                    $data = json_decode($payload, true);
                    return $data['total'] === 2 && count($data['entries']) === 2;
                }),
                300
            );

        $result = $this->useCase->execute(limit: 2, offset: 0);

        $this->assertCount(2, $result['entries']);
        $this->assertEquals(2, $result['total']);
        $this->assertFalse($result['hasMore']);
        $this->assertEquals(1, $result['page']);

        $firstEntry = $result['entries'][0];
        $this->assertSame('AAA', $firstEntry['playerId']);
        $this->assertSame('PlayerOne', $firstEntry['nickname']);
        $this->assertSame(1200, $firstEntry['totalTrophies']);
        $this->assertSame(2, $firstEntry['level']);
    }
}
