<?php
namespace App\Application\UseCase\Player;

use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Application\Service\BrawlStarsService;
use App\Domain\ValueObject\PlayerId;
use App\Domain\ValueObject\Trophy;
use App\Domain\ValueObject\Uuid;
use App\Domain\Exception\PlayerNotFoundException;

class LinkBrawlStarsPlayerUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private UserRepositoryInterface $userRepository,
        private BrawlStarsService $brawlStarsService
    ) {}

    public function execute(Uuid $userId, string $brawlStarsPlayerId): Player {
        // Verify user exists
        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            throw new \InvalidArgumentException('User not found');
        }

        // Fetch player data from Brawl Stars API
        $apiData = $this->brawlStarsService->getPlayerById($brawlStarsPlayerId);
        if ($apiData === null) {
            throw new PlayerNotFoundException('Player not found in Brawl Stars API');
        }

        // Create or update player entity
        $playerId = new PlayerId($brawlStarsPlayerId);
        $existingPlayer = $this->playerRepository->findById($playerId);

        if ($existingPlayer === null) {
            $player = new Player(
                $playerId,
                $apiData['name'] ?? 'Unknown',
                new Trophy($apiData['trophies'] ?? 0),
                isset($apiData['club']['tag']) ? $apiData['club']['tag'] : 'GLOBAL'
            );
        } else {
            $existingPlayer->updateFromSync(
                $apiData['trophies'] ?? 0,
                isset($apiData['club']['tag']) ? $apiData['club']['tag'] : 'GLOBAL'
            );
            $player = $existingPlayer;
        }

        $this->playerRepository->save($player);

        return $player;
    }
}

