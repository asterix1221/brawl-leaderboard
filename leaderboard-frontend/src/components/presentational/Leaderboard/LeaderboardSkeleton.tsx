import React from 'react';
import { cn } from '../../../utils/cn';

interface LeaderboardSkeletonProps {
  rows?: number;
  className?: string;
}

const LeaderboardSkeleton: React.FC<LeaderboardSkeletonProps> = ({
  rows = 10,
  className
}) => {
  const SkeletonRow = () => (
    <tr className="animate-pulse">
      {/* Rank Skeleton */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <div className="h-6 w-12 bg-gray-200 rounded-full"></div>
        </div>
      </td>

      {/* Player Skeleton */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <div className="h-10 w-10 bg-gray-200 rounded-full"></div>
          <div className="ml-4 space-y-2">
            <div className="h-4 w-24 bg-gray-200 rounded"></div>
            <div className="h-3 w-16 bg-gray-200 rounded"></div>
          </div>
        </div>
      </td>

      {/* Region Skeleton */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="h-6 w-16 bg-gray-200 rounded-full"></div>
      </td>

      {/* Level Skeleton */}
      <td className="px-6 py-4 whitespace-nowrap">
        <div className="flex items-center">
          <div className="h-2 w-2 bg-gray-200 rounded-full mr-2"></div>
          <div className="h-4 w-16 bg-gray-200 rounded"></div>
        </div>
      </td>

      {/* Trophies Skeleton */}
      <td className="px-6 py-4 whitespace-nowrap text-right">
        <div className="flex items-center justify-end">
          <div className="h-5 w-5 bg-gray-200 rounded mr-2"></div>
          <div className="h-5 w-16 bg-gray-200 rounded"></div>
        </div>
      </td>
    </tr>
  );

  const SkeletonTable = () => (
    <div className={cn('w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow', className)}>
      {/* Table Header Skeleton */}
      <div className="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="h-6 w-48 bg-gray-200 rounded animate-pulse"></div>
          <div className="h-4 w-24 bg-gray-200 rounded animate-pulse"></div>
        </div>
      </div>

      {/* Table Body Skeleton */}
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div className="h-4 w-8 bg-gray-200 rounded animate-pulse"></div>
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div className="h-4 w-16 bg-gray-200 rounded animate-pulse"></div>
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div className="h-4 w-12 bg-gray-200 rounded animate-pulse"></div>
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div className="h-4 w-10 bg-gray-200 rounded animate-pulse"></div>
              </th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div className="h-4 w-16 bg-gray-200 rounded animate-pulse"></div>
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {[...Array(rows)].map((_, index) => (
              <SkeletonRow key={index} />
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const SkeletonList = () => (
    <div className={cn('w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow', className)}>
      {/* Header Skeleton */}
      <div className="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div className="h-6 w-48 bg-gray-200 rounded animate-pulse"></div>
      </div>

      {/* List Skeleton */}
      <div className="divide-y divide-gray-200">
        {[...Array(rows)].map((_, index) => (
          <div key={index} className="px-6 py-4 animate-pulse">
            <div className="flex items-center space-x-4">
              <div className="h-8 w-8 bg-gray-200 rounded-full"></div>
              <div className="flex-1 space-y-2">
                <div className="h-4 bg-gray-200 rounded w-1/4"></div>
                <div className="h-3 bg-gray-200 rounded w-1/6"></div>
              </div>
              <div className="text-right">
                <div className="h-4 bg-gray-200 rounded w-16 ml-auto"></div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );

  // Default to table skeleton, but could be made configurable
  return <SkeletonTable />;
};

export default LeaderboardSkeleton;