<?php
namespace App\Infrastructure\Controller;

use App\Application\UseCase\Player\GetPlayerProfileUseCase;
use App\Application\UseCase\Player\LinkBrawlStarsPlayerUseCase;
use App\Domain\ValueObject\Uuid;
use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class PlayerController {
    public function __construct(
        private GetPlayerProfileUseCase $getPlayerProfileUseCase,
        private LinkBrawlStarsPlayerUseCase $linkBrawlStarsPlayerUseCase
    ) {}

    public function getProfile(Request $request, string $playerId): JsonResponse|ErrorResponse {
        try {
            $result = $this->getPlayerProfileUseCase->execute($playerId);

            return new JsonResponse([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 404);
        }
    }

    public function link(Request $request): JsonResponse|ErrorResponse {
        try {
            $user = $request->getAttribute('user');
            if (!$user || !isset($user['userId'])) {
                return new ErrorResponse('Unauthorized', 401);
            }

            $body = $request->getBody();
            $brawlStarsPlayerId = $body['brawlStarsPlayerId'] ?? '';

            if (empty($brawlStarsPlayerId)) {
                return new ErrorResponse('Brawl Stars Player ID is required', 400);
            }

            $userId = new Uuid($user['userId']);
            $player = $this->linkBrawlStarsPlayerUseCase->execute($userId, $brawlStarsPlayerId);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'playerId' => $player->getId()->getValue(),
                    'nickname' => $player->getNickname(),
                    'totalTrophies' => $player->getTotalTrophies()->getValue(),
                    'region' => $player->getRegion()
                ]
            ]);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}

