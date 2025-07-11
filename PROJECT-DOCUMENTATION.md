# Klage.Click Project Documentation

## Project Overview
Multi-purpose legal automation platform for German courts with AI-powered processing.

## Development Rules

### Version Management
- **Semantic Versioning**: Major.Minor.Patch (e.g., 1.0.4)
- **WordPress Headers**: All version numbers must match
- **Changelog Required**: Document all changes in readme.txt

### Code Standards
- **WordPress Coding Standards**: Follow WP best practices
- **German Language**: All UI text in German
- **Security First**: Sanitize inputs, escape outputs
- **Database**: Use WordPress WPDB, no direct MySQL
- **File Structure**: Maintain plugin organization

### Development Workflow
1. **Issue Identification**: Document what needs to be built
2. **Version Planning**: Increment version numbers
3. **Development**: Build and test locally
4. **GitHub Update**: Save to GitHub with clear commit messages
5. **Testing**: User tests on production
6. **Iteration**: Fix issues, repeat cycle

### Feature Priorities
1. **Core Functionality**: Case management, database operations
2. **Automation**: N8N integration for AI processing
3. **Court Integration**: EGVP submission (Phase 3)
4. **Frontend**: User-facing interfaces
5. **Analytics**: Reporting and insights

## Current Status: v1.0.4
- ✅ Database tables created
- ✅ Case creation working
- ✅ Admin dashboard functional
- 🔄 Case management in progress
- ⏳ N8N integration planned
- ⏳ Frontend interfaces planned

## Next Milestones
- [ ] Case details view and editing
- [ ] Case status workflow
- [ ] Debtor management
- [ ] N8N automation workflows
- [ ] Frontend shortcodes
- [ ] Court document generation

## Technical Architecture
- **Backend**: WordPress plugin
- **Database**: MySQL with WordPress tables
- **Automation**: N8N workflows
- **AI Processing**: External APIs via N8N
- **Court Integration**: EGVP (future)
- **Frontend**: WordPress shortcodes

## Business Requirements
- **Legal Compliance**: GDPR, German court standards
- **Audit Trail**: Complete logging for compliance
- **Multi-Tenant**: Support multiple clients (future)
- **Scalability**: Handle high case volumes
- **Security**: Protect sensitive legal data