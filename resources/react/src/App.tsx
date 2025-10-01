import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './contexts/AuthContext'
import { Login } from './pages/Login'
import { Dashboard } from './pages/Dashboard'
import LoadingSpinner from './components/LoadingSpinner'

function App() {
  console.log('App component rendering...')
  
  // Simple test to see if React is working
  return (
    <div style={{ padding: '20px', backgroundColor: 'red', color: 'white', minHeight: '100vh' }}>
      <h1>React App is Working!</h1>
      <p>If you can see this, React is rendering correctly.</p>
    </div>
  )
  
  // Commented out the original logic for debugging
  /*
  const { user, isLoading } = useAuth()

  console.log('Auth state:', { user, isLoading })

  if (isLoading) {
    console.log('Loading state - showing spinner')
    return <LoadingSpinner />
  }

  if (!user) {
    console.log('No user - showing login')
    return <Login />
  }

  console.log('User authenticated - showing dashboard')
  return (
    <div className="min-h-screen bg-background">
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </div>
  )
  */
}

export default App