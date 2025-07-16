# GitHub Deployment Guide - Clean Plugin Separation

## Overview
Your plugins have been properly separated into two clean directories:
- **Core Plugin**: `/app/final-github-upload/1-UPLOAD-TO-klage-click-court-automation/`
- **Financial Calculator Plugin**: `/app/final-github-upload/2-UPLOAD-TO-klage-finance/`

## Step 1: Deploy Core Plugin to klage-click-court-automation

### Repository: https://github.com/roshoworld/klage-click-court-automation

1. **Navigate to the folder**: `/app/final-github-upload/1-UPLOAD-TO-klage-click-court-automation/`
2. **Select ALL files** in this folder (NOT the folder itself, but all files inside)
3. **Go to GitHub**: https://github.com/roshoworld/klage-click-court-automation
4. **Clean the repository**: 
   - Delete ALL existing files (each file → Delete → Commit)
   - OR use GitHub's "Upload files" and select "replace files"
5. **Upload**: Click "Add file" → "Upload files"
6. **Drag and drop** all selected files from step 2
7. **Commit message**: "Clean Cut Implementation v1.4.8 - Core Plugin Only"
8. **Click "Commit changes"**

### What you're uploading:
- `court-automation-hub.php` (main plugin file)
- `includes/` folder (all core classes)
- `admin/` folder (admin dashboard)
- `api/` folder (REST API)
- `assets/` folder (CSS/JS)
- `README.md`, `INSTALLATION.md`, etc.

## Step 2: Deploy Financial Calculator to klage-finance

### Repository: https://github.com/roshoworld/klage-finance

1. **Navigate to the folder**: `/app/final-github-upload/2-UPLOAD-TO-klage-finance/`
2. **Select ALL files** in this folder (NOT the folder itself, but all files inside)
3. **Go to GitHub**: https://github.com/roshoworld/klage-finance
4. **Clean the repository**: 
   - Delete ALL existing files (each file → Delete → Commit)
   - OR use GitHub's "Upload files" and select "replace files"
5. **Upload**: Click "Add file" → "Upload files"
6. **Drag and drop** all selected files from step 2
7. **Commit message**: "Financial Calculator v1.0.0 - Separated Plugin"
8. **Click "Commit changes"**

### What you're uploading:
- `court-automation-hub-financial-calculator.php` (main financial plugin file)
- `financial-calculator/` folder (all financial classes)
- `README.md`

## Verification

After both uploads:

1. **Core Plugin Repository** should contain:
   - All core Court Automation Hub files
   - NO financial calculator files
   - Version 1.4.8

2. **Financial Plugin Repository** should contain:
   - Only financial calculator files
   - Version 1.0.0
   - Requires the core plugin to function

## Important Notes

- The plugins are now completely separate
- The financial calculator plugin requires the core plugin to be installed first
- Both plugins communicate through WordPress hooks (`cah_case_created`, `cah_case_updated`, `cah_case_deleted`)
- No mixed files or clutter anymore!

## Next Steps

Once uploaded, you can:
1. Install the core plugin first
2. Install the financial calculator plugin second
3. Both will work together seamlessly