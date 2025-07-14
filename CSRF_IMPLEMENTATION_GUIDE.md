# CSRF Token Implementation Guide

## Overview

CSRF (Cross-Site Request Forgery) protection has been implemented across the WiraCenter application to prevent unauthorized form submissions and protect against malicious attacks.

## 🔒 What is CSRF?

CSRF is an attack where malicious websites can make unauthorized requests on behalf of authenticated users. For example:
- User is logged into admin panel
- User visits a malicious website
- Malicious website submits a form to admin panel (delete article, change settings, etc.)
- Admin panel processes the request because user is authenticated

## 🛡️ How CSRF Protection Works

### 1. Token Generation
- Each user session gets a unique CSRF token
- Token is generated using cryptographically secure random bytes
- Token is stored in PHP session (no database required)

### 2. Form Protection
- Every POST form includes a hidden CSRF token field
- Token is automatically generated for each form

### 3. Server Validation
- Server validates token before processing any POST request
- Invalid tokens result in error message and redirect
- Valid tokens allow normal form processing

## 📁 Files Modified

### Core Functions (config/config.php)
```php
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

### Protected Files
- **Admin Panel:**
  - `admin/articles.php` - Article management forms
  - `admin/projects.php` - Project management forms
  - `admin/tools.php` - Tool management forms
  - `admin/pages.php` - Page management forms
  - `admin/content_blocks.php` - Content block forms
  - `admin/faqs.php` - FAQ management forms
  - `admin/users.php` - User management forms
  - `admin/profile.php` - Profile update forms
  - `admin/navigation.php` - Navigation management forms
  - `admin/files.php` - File upload forms
  - `admin/settings.php` - Settings forms
  - `admin/login.php` - Login form
  - `admin/force_change_password.php` - Password change form

- **Public Forms:**
  - `contact.php` - Contact form
  - `api/contact.php` - Contact form API

- **API Endpoints:**
  - `admin/api/mark_notification_read.php` - Notification actions

## 🔧 Implementation Pattern

### 1. Form HTML
```html
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <!-- other form fields -->
</form>
```

### 2. Server Validation
```php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid CSRF token. Please try again.';
        // Handle error (redirect, show message, etc.)
    }
    
    // Process form data...
}
```

## 🧪 Testing CSRF Protection

### Test 1: Normal Form Submission
1. Login to admin panel
2. Fill out any form (create article, update settings, etc.)
3. Submit form
4. **Expected:** Form processes normally

### Test 2: Invalid Token
1. Login to admin panel
2. Open browser developer tools
3. Find CSRF token in form
4. Change token value
5. Submit form
6. **Expected:** Error message "Invalid CSRF token"

### Test 3: Missing Token
1. Create a simple HTML form that POSTs to admin endpoint
2. Don't include CSRF token
3. Submit from external site
4. **Expected:** Error message "Invalid CSRF token"

## ⚠️ Important Notes

### Session Management
- CSRF tokens are tied to user sessions
- If session expires, user needs to refresh page to get new token
- Logout clears session and token

### Token Security
- Tokens are 64 characters long (32 bytes hex)
- Generated using `random_bytes()` for cryptographic security
- Validated using `hash_equals()` to prevent timing attacks

### Error Handling
- Invalid tokens show user-friendly error messages
- Users are redirected to appropriate pages
- No sensitive information is exposed in error messages

## 🔍 Troubleshooting

### Common Issues

#### 1. "Invalid CSRF token" Error
**Cause:** Session expired or token mismatch
**Solution:** Refresh page to get new token

#### 2. Forms Not Working
**Cause:** Missing CSRF token in form
**Solution:** Add hidden input with `generateCSRFToken()`

#### 3. API Calls Failing
**Cause:** AJAX requests not including CSRF token
**Solution:** Include token in request headers or data

### Debug Commands
```bash
# Check for CSRF validation in files
grep -r "validateCSRFToken" admin/

# Check for CSRF tokens in forms
grep -r "csrf_token" admin/

# Check session status
php -r "session_start(); var_dump($_SESSION);"
```

## 📋 Best Practices

### 1. Always Validate
- Validate CSRF token on ALL POST requests
- Don't skip validation for "simple" forms

### 2. Proper Error Handling
- Show user-friendly error messages
- Log security violations for monitoring

### 3. Token Management
- Generate new tokens per session
- Don't reuse tokens across sessions
- Clear tokens on logout

### 4. Form Design
- Include CSRF token in all POST forms
- Don't rely on JavaScript to add tokens
- Test forms thoroughly after implementation

## 🔮 Future Enhancements

### Potential Improvements
1. **Token Rotation:** Regenerate tokens after successful use
2. **Time-based Tokens:** Add expiration to tokens
3. **Rate Limiting:** Limit token generation frequency
4. **Monitoring:** Log CSRF attempts for security analysis

### Advanced Features
1. **API Token Support:** Separate tokens for API endpoints
2. **Multi-tab Support:** Handle multiple browser tabs
3. **Mobile App Support:** Token validation for mobile APIs

## 📚 References

- [OWASP CSRF Prevention](https://owasp.org/www-community/attacks/csrf)
- [PHP Session Security](https://www.php.net/manual/en/session.security.php)
- [CSRF Token Best Practices](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)

---

**Last Updated:** 2024-12-19  
**Version:** 1.0  
**Status:** ✅ Implemented and Tested 