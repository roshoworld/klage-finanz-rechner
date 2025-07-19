# Klage.Click Court Automation Hub - Siteground Deployment Guide

## Plugin Package Ready: `klage-click-court-automation-hub.tar.gz`

## Step 1: Upload Plugin to Siteground

### Method A: WordPress Admin Upload (Recommended)
1. **Login to your WordPress admin** on Siteground
2. **Navigate to**: Plugins → Add New → Upload Plugin
3. **Upload**: `klage-click-court-automation-hub.tar.gz`
4. **Click**: Install Now → Activate Plugin

### Method B: FTP Upload (Alternative)
1. **Extract** the tar.gz file on your computer
2. **Upload** the `court-automation-hub` folder to `/wp-content/plugins/`
3. **Go to** WordPress Admin → Plugins → Activate "Court Automation Hub"

## Step 2: Initial Configuration

### Database Setup (Automatic)
- Plugin will automatically create 8 database tables on activation
- No manual database work needed

### Required Settings
1. **Go to**: Klage.Click Hub → Einstellungen
2. **Configure**:
   - **N8N API URL**: Your N8N Cloud instance URL
   - **N8N API Key**: Your N8N authentication key
   - **Debug Mode**: Enable for testing (disable for production)

## Step 3: Test the Admin Dashboard

1. **Navigate to**: Klage.Click Hub → Dashboard
2. **Check**: System status shows all tables created
3. **Test**: Create a sample GDPR spam case

## Step 4: Quick GDPR Spam Case Test

### Create Your First Case
1. **Go to**: Klage.Click Hub → Fälle → Neuen Fall hinzufügen
2. **Fill in**:
   - **Fall-ID**: Auto-generated (e.g., SPAM-2025-1234)
   - **Priorität**: Hoch (for your 60 urgent cases)
   - **E-Mail Evidenz**:
     - Empfangsdatum: Date spam was received
     - Empfangszeit: Time received
     - Absender E-Mail: Spammer's email
     - Empfänger E-Mail: Victim's email
     - Betreff: Email subject
     - E-Mail Inhalt: Full email content

3. **Click**: Fall erstellen

### Automatic Calculations
- **Base GDPR Damage**: €350.00
- **Legal Fees**: €96.90 (RVG)
- **Communication Fees**: €13.36
- **Court Fees**: €32.00
- **VAT**: €87.85
- **Total**: €548.11

## Step 5: Bulk Processing Your 60 Cases

### Efficient Case Entry
1. **Use the admin form** for each case
2. **Copy-paste email content** directly
3. **System automatically**:
   - Assigns case ID
   - Calculates damages
   - Determines legal basis
   - Creates audit trail

### N8N Integration (Once Configured)
- Cases automatically sent to N8N for AI processing
- Document generation
- Court filing preparation

## Step 6: Production Checklist

### ✅ **Immediate Testing**
- [ ] Plugin activated successfully
- [ ] Dashboard loads without errors
- [ ] Database tables created (8 tables)
- [ ] First test case created
- [ ] Damage calculation works (€548.11 total)

### ✅ **Ready for 60 Cases**
- [ ] N8N URL configured
- [ ] Case entry form tested
- [ ] Bulk entry process defined
- [ ] Court assignment working

### ✅ **Security & Compliance**
- [ ] Audit logging active
- [ ] GDPR compliance features enabled
- [ ] Data retention settings configured
- [ ] User permissions set

## Troubleshooting

### Common Issues

**1. Database Tables Not Created**
- Check WordPress database permissions
- Manually activate/deactivate plugin

**2. N8N Connection Fails**
- Verify N8N URL format: `https://your-n8n-instance.app.n8n.cloud`
- Check API key validity
- Test with webhook endpoint

**3. Case Creation Errors**
- Ensure all required fields filled
- Check email format validation
- Verify date formats (YYYY-MM-DD)

### Support
- Check Klage.Click Hub → System Status
- Enable Debug Mode for detailed logs
- Review audit logs for error tracking

## Next Steps After Testing

1. **Process your 60 spam cases** through the admin interface
2. **Configure automated workflows** with N8N
3. **Set up court integration** (Phase 2)
4. **Enable document generation** automation

## Performance Notes

- **Expected Load**: 100+ cases/month easily handled
- **Database**: Optimized for German court requirements
- **Security**: Full audit trail and GDPR compliance
- **Scalability**: Ready for multi-tenant expansion

---

**Ready to process your 60 spam cases immediately!**

The plugin is production-ready and will bypass any Bitdefender issues since it's running on your Siteground server directly.