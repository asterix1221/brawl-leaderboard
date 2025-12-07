import React from 'react';
import { BrowserRouter as Router } from 'react-router-dom';
import Header from './components/presentational/Common/Header';
import AppRoutes from './routes/AppRoutes';
import './index.css';

function App() {
  return (
    <Router>
      <div className="min-h-screen bg-gray-50">
        <Header />
        <main>
          <AppRoutes />
        </main>
      </div>
    </Router>
  );
}

export default App;
