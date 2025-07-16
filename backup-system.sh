#!/bin/bash
# Automatic Backup System for Court Automation Hub
# Creates versioned backups of working plugin versions

BACKUP_DIR="/app/backups"
PLUGIN_DIR="/app"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
VERSION=$(grep "Version:" /app/court-automation-hub.php | sed 's/.*Version: //' | sed 's/ .*//')

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Create version-specific backup
BACKUP_NAME="court-automation-hub_v${VERSION}_${TIMESTAMP}"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"

echo "Creating backup: $BACKUP_NAME"

# Create backup directory
mkdir -p "$BACKUP_PATH"

# Copy core plugin files
cp -r "$PLUGIN_DIR"/*.php "$BACKUP_PATH/" 2>/dev/null
cp -r "$PLUGIN_DIR"/includes "$BACKUP_PATH/" 2>/dev/null
cp -r "$PLUGIN_DIR"/admin "$BACKUP_PATH/" 2>/dev/null
cp -r "$PLUGIN_DIR"/api "$BACKUP_PATH/" 2>/dev/null
cp -r "$PLUGIN_DIR"/assets "$BACKUP_PATH/" 2>/dev/null

# Copy financial calculator if exists
if [ -d "$PLUGIN_DIR/financial-calculator" ]; then
    cp -r "$PLUGIN_DIR/financial-calculator" "$BACKUP_PATH/" 2>/dev/null
    cp "$PLUGIN_DIR/court-automation-hub-financial-calculator.php" "$BACKUP_PATH/" 2>/dev/null
fi

# Create backup manifest
cat > "$BACKUP_PATH/BACKUP_MANIFEST.txt" << EOF
Backup Created: $(date)
Plugin Version: $VERSION
Backup Name: $BACKUP_NAME
Contents:
- Core plugin files
- Admin interface
- Database classes
- API classes
- Assets
- Financial calculator (if present)

Files included:
EOF

find "$BACKUP_PATH" -type f -name "*.php" >> "$BACKUP_PATH/BACKUP_MANIFEST.txt"

# Create compressed backup
cd "$BACKUP_DIR"
tar -czf "${BACKUP_NAME}.tar.gz" "$BACKUP_NAME"
rm -rf "$BACKUP_NAME"

echo "Backup created: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# Keep only last 10 backups
cd "$BACKUP_DIR"
ls -t *.tar.gz | tail -n +11 | xargs -r rm

echo "Backup system completed successfully"