<?php
namespace App\Infrastructure\Controller;

use App\Application\UseCase\Leaderboard\GetGlobalLeaderboardUseCase;
use App\Application\UseCase\Leaderboard\SearchPlayerUseCase;
use App\Framework\HTTP\JsonResponse;
use App\Framework\HTTP\Request;
use App\Framework\HTTP\ErrorResponse;

class LeaderboardController {
    public function __construct(
        private GetGlobalLeaderboardUseCase $getGlobalLeaderboardUseCase,
        private SearchPlayerUseCase $searchPlayerUseCase
    ) {}

    public function getGlobal(Request $request): JsonResponse {
        try {
            $limit = min((int)$request->getQuery('limit', 100), 500);
            $offset = max(0, (int)$request->getQuery('offset', 0));
            $region = $request->getQuery('region');
            $season = $request->getQuery('seasonId', $request->getQuery('season'));

            $result = $this->getGlobalLeaderboardUseCase->execute($limit, $offset, $region, $season);

            return new JsonResponse([
                'success' => true,
                'data' => $result,
                'timestamp' => date('c')
            ]);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 500);
        }
    }

    public function search(Request $request): JsonResponse|ErrorResponse {
        try {
            $query = $request->getQuery('q');
            
            if (!$query || strlen($query) < 2) {
                return new ErrorResponse('Search query must be at least 2 characters', 400);
            }

            $result = $this->searchPlayerUseCase->execute($query, 20);

            return new JsonResponse([
                'success' => true,
                'data' => ['players' => $result]
            ]);
        } catch (\Exception $e) {
            return new ErrorResponse($e->getMessage(), 500);
        }
    }
}

