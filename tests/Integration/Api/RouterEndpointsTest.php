<?php

namespace Tests\Integration\Api;

use App\Application\Service\CacheService;
use App\Application\Service\JWTService;
use App\Application\Service\PasswordService;
use App\Application\UseCase\Auth\LoginPlayerUseCase;
use App\Application\UseCase\Auth\RegisterPlayerUseCase;
use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Application\UseCase\Leaderboard\SearchPlayerUseCase;
use App\Application\UseCase\Player\GetPlayerProfileUseCase;
use App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase;
use App\Domain\Entity\Player;
use App\Domain\Entity\User;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;
use App\Framework\Container\DIContainer;
use App\Framework\HTTP\Request;
use App\Framework\Router\Router;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Support\FakeBrawlStarsService;
use Tests\Integration\Support\FakeRedis;
use Tests\Integration\Support\InMemoryPlayerRepository;
use Tests\Integration\Support\InMemoryUserRepository;

class RouterEndpointsTest extends TestCase
{
    public function testGlobalLeaderboardEndpointReturnsEntries(): void
    {
        [$router] = $this->createRouterWithFixtures();

        $request = $this->createRequest(query: ['limit' => 2]);
        $response = json_decode($router->dispatch('GET', '/leaderboards/global', $request), true);

        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']['entries']);
        $this->assertEquals(3, $response['data']['total']);
        $this->assertEquals(1, $response['data']['entries'][0]['rank']);
    }

    public function testLoginEndpointReturnsTokens(): void
    {
        [$router] = $this->createRouterWithFixtures();

        $request = $this->createRequest(body: ['email' => 'user@example.com', 'password' => 'secret']);
        $response = json_decode($router->dispatch('POST', '/auth/login', $request), true);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('accessToken', $response['data']);
        $this->assertSame('user@example.com', $response['data']['user']['email']);
    }

    public function testPlayerProfileEndpointReturnsPlayerData(): void
    {
        [$router] = $this->createRouterWithFixtures();

        $request = $this->createRequest();
        $response = json_decode($router->dispatch('GET', '/players/PLAYER-1', $request), true);

        $this->assertTrue($response['success']);
        $this->assertSame('PLAYER-1', $response['data']['player']['id']);
        $this->assertSame('Alice', $response['data']['player']['nickname']);
    }

    private function createRouterWithFixtures(): array
    {
        $container = new DIContainer();

        $players = [
            new Player(new PlayerId('PLAYER-1'), 'Alice', new Trophy(2500), 'EU'),
            new Player(new PlayerId('PLAYER-2'), 'Bob', new Trophy(1800), 'NA'),
            new Player(new PlayerId('PLAYER-3'), 'Charlie', new Trophy(900), 'EU'),
        ];

        $userId = new Uuid('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');
        $user = new User($userId, new Email('user@example.com'), password_hash('secret', PASSWORD_BCRYPT), 'User');

        $playerRepository = new InMemoryPlayerRepository($players);
        $userRepository = new InMemoryUserRepository([$user]);
        $redis = new FakeRedis();

        $container->set(PlayerRepositoryInterface::class, fn() => $playerRepository);
        $container->set(UserRepositoryInterface::class, fn() => $userRepository);
        $container->set(CacheService::class, fn() => new CacheService($redis));
        $container->set(PasswordService::class, fn() => new PasswordService());
        $container->set(JWTService::class, fn() => new JWTService('test-secret'));
        $container->set(FakeBrawlStarsService::class, fn() => new FakeBrawlStarsService([
            'PLAYER-NEW' => ['name' => 'Dave', 'trophies' => 400, 'club' => ['tag' => 'NA']],
        ]));

        $container->set(GetGlobalLeaderboardUseCase::class, fn($c) => new GetGlobalLeaderboardUseCase(
            $c->get(PlayerRepositoryInterface::class),
            $c->get(CacheService::class)
        ));

        $container->set(SearchPlayerUseCase::class, fn($c) => new SearchPlayerUseCase(
            $c->get(PlayerRepositoryInterface::class)
        ));

        $container->set(RegisterPlayerUseCase::class, fn($c) => new RegisterPlayerUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(PasswordService::class),
            $c->get(JWTService::class)
        ));

        $container->set(LoginPlayerUseCase::class, fn($c) => new LoginPlayerUseCase(
            $c->get(UserRepositoryInterface::class),
            $c->get(PasswordService::class),
            $c->get(JWTService::class)
        ));

        $container->set(GetPlayerProfileUseCase::class, fn($c) => new GetPlayerProfileUseCase(
            $c->get(PlayerRepositoryInterface::class)
        ));

        $container->set(LinkBrawlStarsPlayerUseCase::class, fn($c) => new LinkBrawlStarsPlayerUseCase(
            $c->get(PlayerRepositoryInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(FakeBrawlStarsService::class)
        ));

        $container->set(\App\Infrastructure\Controller\AuthController::class, fn($c) => new \App\Infrastructure\Controller\AuthController(
            $c->get(RegisterPlayerUseCase::class),
            $c->get(LoginPlayerUseCase::class)
        ));

        $container->set(\App\Infrastructure\Controller\LeaderboardController::class, fn($c) => new \App\Infrastructure\Controller\LeaderboardController(
            $c->get(GetGlobalLeaderboardUseCase::class),
            $c->get(SearchPlayerUseCase::class)
        ));

        $container->set(\App\Infrastructure\Controller\PlayerController::class, fn($c) => new \App\Infrastructure\Controller\PlayerController(
            $c->get(GetPlayerProfileUseCase::class),
            $c->get(LinkBrawlStarsPlayerUseCase::class)
        ));

        $router = new Router($container);
        require __DIR__ . '/../../../src/Framework/Router/routes.php';

        return [$router, $playerRepository, $userRepository];
    }

    private function createRequest(array $query = [], array $body = []): Request
    {
        $request = new Request();
        $ref = new \ReflectionClass($request);

        foreach (['query' => $query, 'body' => $body, 'attributes' => [], 'headers' => []] as $property => $value) {
            $prop = $ref->getProperty($property);
            $prop->setAccessible(true);
            $prop->setValue($request, $value);
        }

        return $request;
    }
}
