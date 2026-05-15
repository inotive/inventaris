#!/usr/bin/env bash
set -euo pipefail

# fix_storage_link.sh
# Creates a symlink public/storage -> storage/app/public if possible.
# If symlink creation fails (common on restricted shared hosts), it copies
# the files as a fallback so the app can serve them from public/storage.
# Usage: ./scripts/fix_storage_link.sh [project_root]

ROOT="${1:-$(pwd)}"
TARGET="$ROOT/storage/app/public"
LINK="$ROOT/public/storage"

echo "Project root: $ROOT"
echo "Target: $TARGET"
echo "Link: $LINK"

if [ -e "$LINK" ] || [ -L "$LINK" ]; then
    echo "Link or folder already exists at $LINK"
    ls -ld "$LINK"
    exit 0
fi

echo "Attempting to create symlink..."
if ln -s "$TARGET" "$LINK" 2>/dev/null; then
    echo "Symlink created: $LINK -> $TARGET"
else
    echo "Symlink failed, falling back to copying files..."
    mkdir -p "$LINK"
    # Use rsync if available for better copying; otherwise use cp
    if command -v rsync >/dev/null 2>&1; then
        rsync -a "$TARGET/" "$LINK/"
    else
        cp -a "$TARGET/." "$LINK/" || true
    fi
    echo "Files copied to $LINK"
fi

echo "Fixing permissions (attempt)..."
chmod -R 775 "$ROOT/storage" "$ROOT/bootstrap/cache" "$ROOT/public/storage" 2>/dev/null || true

echo "Done. If you still see missing files, please ensure the webserver user has read access to these paths or run this script as the deployment user."
