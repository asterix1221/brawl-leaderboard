<?php
namespace App\Application\UseCase\Player;

use App\Application\DTO\PlayerDTO;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\ValueObject\PlayerId;
use App\Domain\Exception\PlayerNotFoundException;

class GetPlayerProfileUseCase {
    public function __construct(
        private PlayerRepositoryInterface $playerRepository
    ) {}

    public function execute(string $playerId): array {
        $id = new PlayerId($playerId);
        $player = $this->playerRepository->findById($id);

        if ($player === null) {
            throw new PlayerNotFoundException('Player not found');
        }

        $playerDTO = new PlayerDTO(
            id: $player->getId()->getValue(),
            nickname: $player->getNickname(),
            totalTrophies: $player->getTotalTrophies()->getValue(),
            region: $player->getRegion(),
            level: $player->getLevel()
        );

        return [
            'player' => $playerDTO->toArray(),
            'lastSyncedAt' => $player->getLastSyncedAt()->format('c')
        ];
    }
}

