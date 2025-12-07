// Domain Types for Brawl Stars Leaderboard

export interface Player {
  id: string;
  nickname: string;
  totalTrophies: number;
  region: string;
  level: number;
  lastUpdated: string;
  createdAt: string;
}

export interface Score {
  id: string;
  playerId: string;
  seasonId: string;
  totalScore: number;
  wins: number;
  losses: number;
  createdAt: string;
  updatedAt: string;
}

export interface Season {
  id: string;
  name: string;
  startDate: string;
  endDate: string;
  isActive: boolean;
  createdAt: string;
}

export interface User {
  id: string;
  email: string;
  nickname: string;
  createdAt: string;
  updatedAt: string;
}

export interface LeaderboardEntry {
  rank: number;
  playerId: string;
  nickname: string;
  totalTrophies: number;
  region: string;
  level: number;
}

export interface ScoreHistory {
  id: string;
  scoreId: string;
  playerId: string;
  oldScore: number | null;
  newScore: number | null;
  changeReason: string;
  createdAt: string;
}

export interface PlayerStats {
  playerId: string;
  totalTrophies: number;
  wins: number;
  losses: number;
  winRate: number;
  rank: number;
  region: string;
  level: number;
}