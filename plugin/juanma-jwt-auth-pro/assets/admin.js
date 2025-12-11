/**
 * WordPress REST Auth JWT - Admin JavaScript
 * Simple admin interface for JWT authentication management
 */

jQuery(document).ready(function($) {
    'use strict';

    const wpRestAuthJWT = window.wpRestAuthJWT || {};

    /**
     * Initialize admin functionality
     */
    function init() {
        bindEvents();
        initTooltips();
        validateSettings();
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Settings form validation
        $('form').on('submit', validateForm);

        // Real-time validation for JWT settings fields
        $('input[name="jwt_auth_pro_settings[secret_key]"]').on('blur', validateSecretKey);
        $('input[name="jwt_auth_pro_settings[access_token_expiry]"]').on('blur', validateTokenExpiry);
        $('input[name="jwt_auth_pro_settings[refresh_token_expiry]"]').on('blur', validateTokenExpiry);

        // Generate JWT secret button
        $('#generate_jwt_secret').on('click', generateJWTSecret);

        // Toggle show/hide JWT secret
        $('#toggle_jwt_secret').on('click', toggleJWTSecret);

        // Copy to clipboard functionality
        $('.copy-to-clipboard').on('click', copyToClipboard);
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $('.wp-rest-auth-jwt-tooltip').tooltip({
            position: { my: 'left+10 center', at: 'right center' },
            tooltipClass: 'wp-rest-auth-jwt-tooltip-content'
        });
    }

    /**
     * Validate settings form
     */
    function validateForm(e) {
        let isValid = true;

        if (!validateSecretKey()) isValid = false;
        if (!validateTokenExpiry()) isValid = false;

        if (!isValid) {
            e.preventDefault();
            showNotice('Please fix the validation errors before saving.', 'error');
        }
    }

    /**
     * Validate secret key
     */
    function validateSecretKey() {
        const $input = $('input[name="jwt_auth_pro_settings[secret_key]"]');
        if (!$input.length || $input.prop('readonly')) {
            return true; // Field doesn't exist or is readonly (defined in wp-config.php)
        }

        const value = $input.val().trim();

        if (!value) {
            showFieldError($input, 'Secret key is required');
            return false;
        }

        if (value.length < 32) {
            showFieldError($input, 'Secret key must be at least 32 characters');
            return false;
        }

        clearFieldError($input);
        return true;
    }

    /**
     * Validate token expiry
     */
    function validateTokenExpiry() {
        // Check both access token and refresh token fields
        const $accessInput = $('input[name="jwt_auth_pro_settings[access_token_expiry]"]');
        const $refreshInput = $('input[name="jwt_auth_pro_settings[refresh_token_expiry]"]');

        let isValid = true;

        // Validate access token expiry
        if ($accessInput.length && !$accessInput.prop('readonly')) {
            const value = parseInt($accessInput.val());
            if (isNaN(value) || value < 300) {
                showFieldError($accessInput, 'Access token expiry must be at least 300 seconds');
                isValid = false;
            } else if (value > 86400) {
                showFieldError($accessInput, 'Access token expiry should not exceed 86400 seconds');
                isValid = false;
            } else {
                clearFieldError($accessInput);
            }
        }

        // Validate refresh token expiry
        if ($refreshInput.length && !$refreshInput.prop('readonly')) {
            const value = parseInt($refreshInput.val());
            if (isNaN(value) || value < 3600) {
                showFieldError($refreshInput, 'Refresh token expiry must be at least 3600 seconds');
                isValid = false;
            } else if (value > 31536000) {
                showFieldError($refreshInput, 'Refresh token expiry should not exceed 31536000 seconds');
                isValid = false;
            } else {
                clearFieldError($refreshInput);
            }
        }

        return isValid;
    }

    /**
     * Generate new secret key (legacy function, kept for compatibility)
     */
    function generateSecretKey(e) {
        e.preventDefault();

        if (!confirm('Generate a new secret key? This will invalidate all existing tokens.')) {
            return;
        }

        const secretKey = generateRandomString(64);
        $('input[name="jwt_auth_pro_settings[secret_key]"]').val(secretKey);
        showNotice('New secret key generated. Remember to save your settings.', 'info');
    }

    /**
     * Generate JWT secret for the specific JWT secret field
     */
    function generateJWTSecret(e) {
        if (e) e.preventDefault();

        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let secret = '';
        for (let i = 0; i < 64; i++) {
            secret += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        $('#jwt_secret_key').val(secret);
    }

    /**
     * Toggle show/hide for JWT secret field
     */
    function toggleJWTSecret(e) {
        if (e) e.preventDefault();

        const field = $('#jwt_secret_key');
        field.attr('type', field.attr('type') === 'password' ? 'text' : 'password');
    }

    /**
     * Test JWT generation
     */
    function testJWTGeneration(e) {
        e.preventDefault();

        const $button = $(this);
        const originalText = $button.text();

        $button.prop('disabled', true).text('Testing...');

        $.ajax({
            url: wpRestAuthJWT.restUrl + 'wp-rest-auth-jwt/v1/test',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpRestAuthJWT.nonce);
            }
        }).done(function(response) {
            if (response.success) {
                showNotice('JWT generation test successful!', 'success');
                $('#test-results').html(`
                    <h4>Test Results:</h4>
                    <p><strong>Token Type:</strong> ${response.data.token_type}</p>
                    <p><strong>Expires In:</strong> ${response.data.expires_in} seconds</p>
                    <p><strong>Token Length:</strong> ${response.data.token.length} characters</p>
                `);
            } else {
                showNotice('JWT generation test failed: ' + response.message, 'error');
            }
        }).fail(function(xhr) {
            showNotice('JWT generation test failed: ' + xhr.responseText, 'error');
        }).always(function() {
            $button.prop('disabled', false).text(originalText);
        });
    }

    /**
     * Copy to clipboard
     */
    function copyToClipboard(e) {
        e.preventDefault();

        const $button = $(this);
        const targetSelector = $button.data('target');
        const $target = $(targetSelector);

        if ($target.length) {
            $target.select();
            document.execCommand('copy');

            const originalText = $button.text();
            $button.text('Copied!').addClass('copied');

            setTimeout(() => {
                $button.text(originalText).removeClass('copied');
            }, 2000);
        }
    }

    /**
     * Toggle advanced settings
     */
    function toggleAdvancedSettings(e) {
        e.preventDefault();

        const $advanced = $('.wp-rest-auth-jwt-advanced-settings');
        const $button = $(this);

        if ($advanced.is(':visible')) {
            $advanced.slideUp();
            $button.text('Show Advanced Settings');
        } else {
            $advanced.slideDown();
            $button.text('Hide Advanced Settings');
        }
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        clearFieldError($field);
        $field.addClass('error').after(`<div class="field-error">${message}</div>`);
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        $field.removeClass('error').next('.field-error').remove();
    }

    /**
     * Show admin notice
     */
    function showNotice(message, type = 'info') {
        const $notice = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

        $('.wp-header-end').after($notice);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $notice.fadeOut(() => $notice.remove());
        }, 5000);
    }

    /**
     * Generate random string
     */
    function generateRandomString(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    /**
     * Validate all settings on page load
     */
    function validateSettings() {
        setTimeout(() => {
            validateSecretKey();
            validateTokenExpiry();
        }, 100);
    }

    // Initialize when document is ready
    init();
});