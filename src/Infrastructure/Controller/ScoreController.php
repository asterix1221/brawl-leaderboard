<?php
namespace App\Infrastructure\Controller;

use App\Application\UseCase\Score\UpsertPlayerScoreUseCase;
use App\Framework\HTTP\ErrorResponse;
use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;

class ScoreController {
    public function __construct(private UpsertPlayerScoreUseCase $upsertPlayerScoreUseCase) {}

    public function upsert(Request $request): JsonResponse|ErrorResponse {
        try {
            $body = $request->getBody();

            $playerId = $body['playerId'] ?? null;
            $seasonId = $body['seasonId'] ?? ($body['season'] ?? null);
            $totalScore = isset($body['totalScore']) ? (int)$body['totalScore'] : null;
            $wins = (int)($body['wins'] ?? 0);
            $losses = (int)($body['losses'] ?? 0);

            if ($playerId === null || $totalScore === null) {
                return new ErrorResponse('playerId and totalScore are required', 400);
            }

            $result = $this->upsertPlayerScoreUseCase->execute(
                $playerId,
                $seasonId,
                $totalScore,
                $wins,
                $losses
            );

            return new JsonResponse([
                'success' => true,
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 400);
        }
    }
}
