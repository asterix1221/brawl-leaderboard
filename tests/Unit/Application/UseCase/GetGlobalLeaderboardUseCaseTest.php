<?php

namespace Tests\Unit\Application\UseCase;

use App\Application\DTO\LeaderboardEntryDTO;
use App\Application\Service\CacheService;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Domain\Entity\Player;
use App\Domain\Entity\Score;
use App\Domain\Entity\Season;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\ScoreRepositoryInterface;
use App\Domain\Repository\SeasonRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetGlobalLeaderboardUseCaseTest extends TestCase
{
    private PlayerRepositoryInterface|MockObject $playerRepository;
    private ScoreRepositoryInterface|MockObject $scoreRepository;
    private SeasonRepositoryInterface|MockObject $seasonRepository;
    private CacheService|MockObject $cacheService;
    private GetGlobalLeaderboardUseCase $useCase;
    private Season $activeSeason;

    protected function setUp(): void
    {
        parent::setUp();

        $this->playerRepository = $this->createMock(PlayerRepositoryInterface::class);
        $this->scoreRepository = $this->createMock(ScoreRepositoryInterface::class);
        $this->seasonRepository = $this->createMock(SeasonRepositoryInterface::class);
        $this->cacheService = $this->createMock(CacheService::class);

        $this->activeSeason = new Season(
            new Uuid('11111111-1111-1111-1111-111111111111'),
            'Season 1',
            new \DateTime('-1 day'),
            new \DateTime('+1 day'),
            true
        );

        $this->useCase = new GetGlobalLeaderboardUseCase(
            $this->playerRepository,
            $this->scoreRepository,
            $this->seasonRepository,
            $this->cacheService
        );
    }

    public function testReturnsCachedLeaderboardWhenAvailable(): void
    {
        $cached = [
            'entries' => [
                (new LeaderboardEntryDTO(1, '123', 'Test', 999, 5000, 'GLOBAL', 4))->toArray()
            ],
            'total' => 1,
            'hasMore' => false,
            'page' => 1,
            'seasonId' => $this->activeSeason->getId()->getValue()
        ];

        $this->seasonRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn($this->activeSeason);

        $cacheKey = 'leaderboard:global:' . $this->activeSeason->getId()->getValue() . ':0:100';

        $this->cacheService
            ->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(json_encode($cached));

        $this->scoreRepository->expects($this->never())->method('findTopByRegionAndSeason');
        $this->scoreRepository->expects($this->never())->method('countBySeason');

        $result = $this->useCase->execute();

        $this->assertSame($cached, $result);
    }

    public function testFetchesFromRepositoryAndCachesWhenCacheMiss(): void
    {
        $seasonId = $this->activeSeason->getId();

        $this->seasonRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn($this->activeSeason);

        $cacheKey = 'leaderboard:global:' . $seasonId->getValue() . ':0:2';

        $this->cacheService
            ->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturn(null);

        $scoreOne = new Score(new Uuid('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'), new PlayerId('AAA'), $seasonId, 1000);
        $scoreTwo = new Score(new Uuid('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'), new PlayerId('BBB'), $seasonId, 800);

        $this->scoreRepository
            ->expects($this->once())
            ->method('findTopByRegionAndSeason')
            ->with(null, $seasonId, 2, 0)
            ->willReturn([$scoreOne, $scoreTwo]);

        $this->scoreRepository
            ->expects($this->once())
            ->method('countBySeason')
            ->with($seasonId, null)
            ->willReturn(2);

        $playerOne = new Player(new PlayerId('AAA'), 'PlayerOne', new Trophy(1200), 'EU');
        $playerTwo = new Player(new PlayerId('BBB'), 'PlayerTwo', new Trophy(3000), 'NA');

        $this->playerRepository
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnOnConsecutiveCalls($playerOne, $playerTwo);

        $this->cacheService
            ->expects($this->once())
            ->method('set')
            ->with(
                $cacheKey,
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
        $this->assertSame($seasonId->getValue(), $result['seasonId']);

        $firstEntry = $result['entries'][0];
        $this->assertSame('AAA', $firstEntry['playerId']);
        $this->assertSame('PlayerOne', $firstEntry['nickname']);
        $this->assertSame(1200, $firstEntry['totalTrophies']);
        $this->assertSame(2, $firstEntry['level']);
    }
}
