#!/bin/bash

# Watch script pre automatický build pri zmenách
# Použitie: ./watch.sh

echo "🔄 Starting watch mode..."
echo "📦 Building initial version..."

# Initial build
npm run build

echo "👀 Watching for changes..."
echo "Press Ctrl+C to stop"

# Watch mode
npm run watch

