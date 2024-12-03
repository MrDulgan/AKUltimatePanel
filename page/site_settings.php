<!-- page/site_settings.php -->
<?php
require_once '../inc/configuration.php';
?>

<div class="card p-4">
    <form id="siteSettingsForm">
        <div class="form-section">
            <div class="form-section-title">Site Settings</div>

            <!-- Site Title -->
            <div class="mb-3">
                <label for="siteTitle" class="form-label">Site Title</label>
                <input type="text" class="form-control" id="siteTitle" name="site_title" value="<?php echo htmlspecialchars(SITE_TITLE); ?>" required>
                <div class="form-text">The name of your website, displayed in the browser title and headers.</div>
            </div>

            <!-- Meta Description -->
            <div class="mb-3">
                <label for="metaDescription" class="form-label">Meta Description</label>
                <textarea class="form-control" id="metaDescription" name="meta_description" rows="3" required><?php echo htmlspecialchars(META_DESCRIPTION); ?></textarea>
                <div class="form-text">A brief description of your website for search engines.</div>
            </div>

            <!-- Default PValues -->
            <div class="mb-3">
                <label for="defaultPValues" class="form-label">Default PValues</label>
                <input type="number" class="form-control" id="defaultPValues" name="default_pvalues" value="<?php echo htmlspecialchars(DEFAULT_PVALUES); ?>" required>
                <div class="form-text">The default amount of PValues (points) new users receive upon registration.</div>
            </div>

            <!-- Allow Registration -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="allowRegistration" name="allow_registration" <?php echo ALLOW_REGISTRATION ? 'checked' : ''; ?>>
                <label class="form-check-label" for="allowRegistration">
                    Allow User Registrations
                </label>
                <div class="form-text">Enable or disable user registrations on your site.</div>
            </div>

            <!-- Enforce Strong Passwords -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="enforceStrongPasswords" name="enforce_strong_passwords" <?php echo ENFORCE_STRONG_PASSWORDS ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enforceStrongPasswords">
                    Enforce Strong Passwords
                </label>
                <div class="form-text">Require users to create passwords with a minimum level of complexity.</div>
            </div>

            <!-- Site URL -->
            <div class="mb-3">
                <label for="siteURL" class="form-label">Site URL</label>
                <input type="url" class="form-control" id="siteURL" name="site_url" value="<?php echo htmlspecialchars(SITE_URL); ?>" required>
                <div class="form-text">The base URL of your website, e.g., 'https://your-domain.com'</div>
            </div>

            <!-- Version -->
            <div class="mb-3">
                <label for="version" class="form-label">Version</label>
                <input type="text" class="form-control" id="version" name="version" value="<?php echo htmlspecialchars(VERSION); ?>" required>
                <div class="form-text">The current version of your client.</div>
            </div>

            <!-- Size -->
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="text" class="form-control" id="size" name="size" value="<?php echo htmlspecialchars(SIZE); ?>" required>
                <div class="form-text">The total size of your client.</div>
            </div>
        </div>

        <!-- Download Links Section -->
        <div class="form-section">
            <div class="form-section-title">Download Links</div>

            <!-- Google Drive Link -->
            <div class="mb-3">
                <label for="googleDriveLink" class="form-label">Google Drive Link</label>
                <input type="url" class="form-control" id="googleDriveLink" name="google_drive_link" value="<?php echo htmlspecialchars(GOOGLE_DRIVE_LINK); ?>">
                <div class="form-text">Link to your client on Google Drive.</div>
            </div>

            <!-- MediaFire Link -->
            <div class="mb-3">
                <label for="mediafireLink" class="form-label">MediaFire Link</label>
                <input type="url" class="form-control" id="mediafireLink" name="mediafire_link" value="<?php echo htmlspecialchars(MEDIAFIRE_LINK); ?>">
                <div class="form-text">Link to your client on MediaFire.</div>
            </div>

            <!-- Mega.nz Link -->
            <div class="mb-3">
                <label for="megaLink" class="form-label">Mega.nz Link</label>
                <input type="url" class="form-control" id="megaLink" name="mega_link" value="<?php echo htmlspecialchars(MEGA_LINK); ?>">
                <div class="form-text">Link to your client on Mega.nz.</div>
            </div>

            <!-- GoFile Link -->
            <div class="mb-3">
                <label for="gofileLink" class="form-label">GoFile Link</label>
                <input type="url" class="form-control" id="gofileLink" name="gofile_link" value="<?php echo htmlspecialchars(GOFILE_LINK); ?>">
                <div class="form-text">Link to your client on GoFile.</div>
            </div>
        </div>

        <!-- Donation Settings Section -->
        <div class="form-section">
            <div class="form-section-title">Donation Settings</div>

            <!-- Enable Donations -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="donationEnabled" name="donation_enabled" <?php echo DONATION_ENABLED ? 'checked' : ''; ?>>
                <label class="form-check-label" for="donationEnabled">
                    Enable Donations
                </label>
                <div class="form-text">Enable or disable the donation system.</div>
            </div>

            <!-- PayPal Currency -->
            <div class="mb-3">
                <label for="paypalCurrency" class="form-label">PayPal Currency</label>
                <input type="text" class="form-control" id="paypalCurrency" name="paypal_currency" value="<?php echo htmlspecialchars(PAYPAL_CURRENCY); ?>">
                <div class="form-text">Select the default currency for PayPal transactions.</div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary w-100">Update Site Settings</button>
        </div>
    </form>
    <div id="siteSettingsMessage" class="mt-3"></div>
</div>