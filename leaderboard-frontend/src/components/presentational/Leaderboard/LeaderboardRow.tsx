import React from 'react';
import type { LeaderboardEntry } from '../../../types/domain.types';
import { cn } from '../../../utils/cn';

interface LeaderboardRowProps {
  player: LeaderboardEntry;
  rank: number;
  className?: string;
}

const LeaderboardRow: React.FC<LeaderboardRowProps> = ({
  player,
  rank,
  className
}) => {
  const getRankBadgeClass = (rank: number) => {
    if (rank === 1) return 'bg-yellow-100 text-yellow-800 border-yellow-300';
    if (rank <= 3) return 'bg-orange-100 text-orange-800 border-orange-300';
    if (rank <= 10) return 'bg-purple-100 text-purple-800 border-purple-300';
    return 'bg-gray-100 text-gray-800 border-gray-300';
  };

  const getRankIcon = (rank: number) => {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';
    return null;
  };

  const getRowStyle = (rank: number) => {
    if (rank === 1) {
      return 'bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400';
    }
    if (rank <= 3) {
      return 'bg-gradient-to-r from-orange-50 to-red-50 border-l-4 border-orange-400';
    }
    if (rank <= 10) {
      return 'bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-400';
    }
    return 'hover:bg-gray-50';
  };

  return (
    <tr 
      className={cn(
        'transition-all duration-200',
        getRowStyle(rank),
        className
      )}
    >
      {/* Rank */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <span className={cn(
            'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border',
            getRankBadgeClass(rank)
          )}>
            {getRankIcon(rank) && (
              <span className="mr-1">{getRankIcon(rank)}</span>
            )}
            #{rank}
          </span>
        </div>
      </td>

      {/* Player */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <div className="h-10 w-10 flex-shrink-0">
            <div className="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
              <span className="text-white font-medium">
                {player.nickname.charAt(0).toUpperCase()}
              </span>
            </div>
          </div>
          <div className="ml-4">
            <div className="text-sm font-semibold text-gray-900">
              {player.nickname}
            </div>
            <div className="text-xs text-gray-500">
              ID: {player.playerId.substring(0, 8)}...
            </div>
          </div>
        </div>
      </td>

      {/* Region */}
      <td className="px-6 py-4 whitespace-nowrap">
        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          {player.region}
        </span>
      </td>

      {/* Level */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <div className="flex items-center">
            <div className="h-2 w-2 bg-green-400 rounded-full mr-2"></div>
            <span className="text-sm font-medium text-gray-900">
              Level {player.level}
            </span>
          </div>
        </div>
      </td>

      {/* Trophies */}
      <td className="px-6 py-4 whitespace-nowrap text-right">
        <div className="flex items-center justify-end">
          <svg className="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          <span className="text-lg font-bold text-gray-900">
            {player.totalTrophies.toLocaleString()}
          </span>
        </div>
      </td>
    </tr>
  );
};

export default LeaderboardRow;