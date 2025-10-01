#!/bin/bash

echo "🚀 ReHome v2 - Starting Development Environment"
echo "============================================="

# Start Laravel server in background
echo "📡 Starting Laravel API server..."
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

# Wait for Laravel to start
sleep 3

# Start Vite development server
echo "⚛️  Starting Vite React development server..."
npm run dev &
VITE_PID=$!

echo ""
echo "✅ Development servers started!"
echo ""
echo "🔗 URLs:"
echo "   • Laravel API: http://localhost:8000"
echo "   • Filament Admin: http://localhost:8000/system"
echo "   • React SPA: http://localhost:8000/app"
echo "   • Vite Dev Server: http://localhost:5173"
echo ""
echo "👥 Test Users (password: 'password'):"
echo "   • Admin: alice@admin.com"
echo "   • Team: bob@team.com"
echo "   • Consultant: john@consulting.com"
echo "   • Client: jane@client.com"
echo ""
echo "Press Ctrl+C to stop all servers"

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Stopping development servers..."
    kill $LARAVEL_PID 2>/dev/null
    kill $VITE_PID 2>/dev/null
    exit 0
}

# Set trap to cleanup on exit
trap cleanup SIGINT SIGTERM

# Wait for user to stop
wait