<?php

namespace Tests\Integration\Support;

use App\Domain\Entity\Player;
use App\Domain\Repository\PlayerRepositoryInterface;
use App\Domain\ValueObject\PlayerId;

class InMemoryPlayerRepository implements PlayerRepositoryInterface
{
    /** @var array<string, Player> */
    private array $players = [];

    public function __construct(array $seed = [])
    {
        foreach ($seed as $player) {
            $this->save($player);
        }
    }

    public function findById(PlayerId $id): ?Player
    {
        return $this->players[$id->getValue()] ?? null;
    }

    public function findByNickname(string $nickname): ?Player
    {
        foreach ($this->players as $player) {
            if (strcasecmp($player->getNickname(), $nickname) === 0) {
                return $player;
            }
        }

        return null;
    }

    public function findTopByTrophies(int $limit, int $offset): array
    {
        $sorted = array_values($this->players);
        usort($sorted, fn(Player $a, Player $b) => $b->getTotalTrophies()->getValue() <=> $a->getTotalTrophies()->getValue());

        return array_slice($sorted, $offset, $limit);
    }

    public function countAll(): int
    {
        return count($this->players);
    }

    public function save(Player $player): void
    {
        $this->players[$player->getId()->getValue()] = $player;
    }

    public function delete(PlayerId $id): void
    {
        unset($this->players[$id->getValue()]);
    }

    public function searchByNickname(string $query, int $limit): array
    {
        $filtered = array_filter($this->players, function (Player $player) use ($query) {
            return stripos($player->getNickname(), $query) !== false;
        });

        return array_slice(array_values($filtered), 0, $limit);
    }
}
