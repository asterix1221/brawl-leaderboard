import React from 'react';
import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import LeaderboardTable from '../src/components/presentational/Leaderboard/LeaderboardTable';

const mockPlayers = [
  {
    rank: 1,
    playerId: 'player1',
    nickname: 'ProPlayer',
    totalTrophies: 15000,
    region: 'RU',
    level: 50
  }
];

describe('LeaderboardTable', () => {
  it('renders table with players', () => {
    render(<LeaderboardTable players={mockPlayers} />);
    
    expect(screen.getByText('ProPlayer')).toBeTruthy();
    expect(screen.getByText('RU')).toBeTruthy();
    expect(screen.getByText('50')).toBeTruthy();
  });

  it('shows loading state', () => {
    render(<LeaderboardTable players={[]} loading={true} />);
    
    // В состоянии загрузки нет текста "Global Leaderboard", есть только скелетоны
    const skeletonElements = document.querySelectorAll('.animate-pulse');
    expect(skeletonElements.length).toBeGreaterThan(0);
  });

  it('shows empty state', () => {
    render(<LeaderboardTable players={[]} />);
    
    expect(screen.getByText('No players found')).toBeTruthy();
  });

  it('shows error state', () => {
    render(<LeaderboardTable players={[]} error="Network error" />);
    
    expect(screen.getByText('Error loading leaderboard')).toBeTruthy();
    expect(screen.getByText('Network error')).toBeTruthy();
  });
});
