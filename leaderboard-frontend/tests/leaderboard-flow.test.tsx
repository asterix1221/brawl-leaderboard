import React from 'react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import LeaderboardTable from '../src/components/presentational/Leaderboard/LeaderboardTable';

vi.mock('../src/services/api/leaderboardService', () => ({
  default: {
    getGlobalLeaderboard: vi.fn()
  }
}));

vi.mock('../src/store/slices/leaderboardSlice', () => ({
  useLeaderboardStore: () => ({
    players: [],
    total: 0,
    page: 1,
    region: 'global',
    setPlayers: vi.fn(),
    setPage: vi.fn(),
    setRegion: vi.fn()
  })
}));

describe('Leaderboard Flow', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders empty leaderboard', () => {
    render(<LeaderboardTable players={[]} />);
    
    expect(screen.getByText('No players found')).toBeTruthy();
    expect(screen.getByText('There are no players in the leaderboard yet.')).toBeTruthy();
  });

  it('renders with players', () => {
    const mockPlayers = [
      {
        rank: 1,
        playerId: 'player1',
        nickname: 'ProPlayer',
        totalTrophies: 15000,
        region: 'RU',
        level: 50
      },
      {
        rank: 2,
        playerId: 'player2',
        nickname: 'NoobMaster',
        totalTrophies: 12000,
        region: 'EU',
        level: 45
      }
    ];

    render(<LeaderboardTable players={mockPlayers} />);
    
    expect(screen.getByText('ProPlayer')).toBeTruthy();
    expect(screen.getByText('NoobMaster')).toBeTruthy();
    expect(screen.getByText('RU')).toBeTruthy();
    expect(screen.getByText('EU')).toBeTruthy();
    expect(screen.getByText('50')).toBeTruthy();
    expect(screen.getByText('45')).toBeTruthy();
    expect(screen.getByText('15 000')).toBeTruthy();
    expect(screen.getByText('12 000')).toBeTruthy();
  });

  it('shows loading state', () => {
    render(<LeaderboardTable players={[]} loading={true} />);
    
    // В состоянии загрузки есть только скелетоны, нет заголовка
    const skeletonElements = document.querySelectorAll('.animate-pulse');
    expect(skeletonElements.length).toBeGreaterThan(0);
    expect(screen.queryByText('No players found')).toBeNull();
  });

  it('shows error state', () => {
    render(<LeaderboardTable players={[]} error="Network error" />);
    
    expect(screen.getByText('Error loading leaderboard')).toBeTruthy();
    expect(screen.getByText('Network error')).toBeTruthy();
  });

  it('highlights top players', () => {
    const mockPlayers = [
      {
        rank: 1,
        playerId: 'player1',
        nickname: 'Champion',
        totalTrophies: 15000,
        region: 'RU',
        level: 50
      }
    ];

    render(<LeaderboardTable players={mockPlayers} />);
    
    const firstPlaceRow = screen.getByText('Champion').closest('tr');
    expect(firstPlaceRow?.className).toContain('bg-gradient-to-r');
  });

  it('displays correct table headers', () => {
    const mockPlayers = [
      {
        rank: 1,
        playerId: 'player1',
        nickname: 'TestPlayer',
        totalTrophies: 1000,
        region: 'RU',
        level: 10
      }
    ];

    render(<LeaderboardTable players={mockPlayers} />);
    
    expect(screen.getByText('Rank')).toBeTruthy();
    expect(screen.getByText('Player')).toBeTruthy();
    expect(screen.getByText('Region')).toBeTruthy();
    expect(screen.getByText('Level')).toBeTruthy();
    expect(screen.getByText('Trophies')).toBeTruthy();
  });

  it('formats trophy numbers correctly', () => {
    const mockPlayers = [
      {
        rank: 1,
        playerId: 'player1',
        nickname: 'RichPlayer',
        totalTrophies: 15000,
        region: 'RU',
        level: 50
      }
    ];

    render(<LeaderboardTable players={mockPlayers} />);
    
    // toLocaleString() форматирует 15000 как "15 000" (с пробелом)
    expect(screen.getByText('15 000')).toBeTruthy();
  });

  it('shows player count in header', () => {
    const mockPlayers = [
      { rank: 1, playerId: 'player1', nickname: 'Player1', totalTrophies: 1000, region: 'RU', level: 10 },
      { rank: 2, playerId: 'player2', nickname: 'Player2', totalTrophies: 900, region: 'EU', level: 9 }
    ];

    render(<LeaderboardTable players={mockPlayers} />);
    
    expect(screen.getByText('2 players')).toBeTruthy();
  });
});
