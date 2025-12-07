<?php
namespace Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Player;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;

class PlayerTest extends TestCase {
    public function testPlayerLevelCalculation(): void {
        $player = new Player(
            id: new PlayerId('player1'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1200),
            region: 'US'
        );

        $this->assertEquals(2, $player->getLevel());
    }

    public function testPlayerWithHighTrophies(): void {
        $player = new Player(
            id: new PlayerId('player2'),
            nickname: 'ProPlayer',
            totalTrophies: new Trophy(3500),
            region: 'EU'
        );

        $this->assertEquals(4, $player->getLevel());
    }

    public function testPlayerLevel1(): void {
        $player = new Player(
            id: new PlayerId('player3'),
            nickname: 'Beginner',
            totalTrophies: new Trophy(250),
            region: 'RU'
        );

        $this->assertEquals(1, $player->getLevel());
    }

    public function testPlayerLevel3(): void {
        $player = new Player(
            id: new PlayerId('player4'),
            nickname: 'Advanced',
            totalTrophies: new Trophy(2000),
            region: 'ASIA'
        );

        $this->assertEquals(3, $player->getLevel());
    }

    public function testInvalidNicknameMustThrow(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nickname cannot be empty');
        
        new Player(
            id: new PlayerId('player5'),
            nickname: '',
            totalTrophies: new Trophy(100),
            region: 'RU'
        );
    }

    public function testInvalidRegionMustThrow(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Region is required');
        
        new Player(
            id: new PlayerId('player6'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(100),
            region: ''
        );
    }

    public function testPlayerGetters(): void {
        $playerId = new PlayerId('player7');
        $nickname = 'TestPlayer';
        $trophies = new Trophy(1500);
        $region = 'US';
        
        $player = new Player(
            id: $playerId,
            nickname: $nickname,
            totalTrophies: $trophies,
            region: $region
        );

        $this->assertSame($playerId, $player->getId());
        $this->assertSame($nickname, $player->getNickname());
        $this->assertSame($trophies, $player->getTotalTrophies());
        $this->assertSame($region, $player->getRegion());
        $this->assertInstanceOf(\DateTime::class, $player->getLastSyncedAt());
    }

    public function testPlayerWithCustomLastSyncedAt(): void {
        $customTime = new \DateTime('2023-01-01 12:00:00');
        
        $player = new Player(
            id: new PlayerId('player8'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1000),
            region: 'EU',
            lastSyncedAt: $customTime
        );

        $this->assertEquals($customTime, $player->getLastSyncedAt());
    }

    public function testPlayerWithoutCustomLastSyncedAtUsesNow(): void {
        $before = new \DateTime();
        
        $player = new Player(
            id: new PlayerId('player9'),
            nickname: 'TestPlayer',
            totalTrophies: new Trophy(1000),
            region: 'EU'
        );

        $after = new \DateTime();
        
        $this->assertGreaterThanOrEqual($before, $player->getLastSyncedAt());
        $this->assertLessThanOrEqual($after, $player->getLastSyncedAt());
    }
}
