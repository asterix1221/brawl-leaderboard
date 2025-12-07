<?php
namespace App\Application\UseCase\Leaderboard;

use App\Application\DTO\PlayerDTO;
use App\Domain\Repository\PlayerRepositoryInterface;

class SearchPlayerUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository
    ) {}

    public function execute(string $query, int $limit = 20): array {
        if (strlen($query) < 2) {
            throw new \InvalidArgumentException('Search query must be at least 2 characters');
        }

        $players = $this->playerRepository->searchByNickname($query, $limit);

        $results = [];
        foreach ($players as $player) {
            $results[] = new PlayerDTO(
                id: $player->getId()->getValue(),
                nickname: $player->getNickname(),
                totalTrophies: $player->getTotalTrophies()->getValue(),
                region: $player->getRegion(),
                level: $player->getLevel()
            );
        }

        return array_map(fn($p) => $p->toArray(), $results);
    }
}

