<!-- page/settings.php -->
<?php
session_start();
require_once '../inc/configuration.php'; // Ensure configuration constants are available

// Check if the user is logged in and has the appropriate authority
if (!isset($_SESSION['authority']) || $_SESSION['authority'] < 1) {
    echo '<div class="alert alert-danger">You must be logged in to access this page.</div>';
    exit;
}
?>
<div class="container my-4">
    <h2>User Settings</h2>
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="user-settings-tab" data-bs-toggle="tab" href="#user-settings" role="tab" aria-controls="user-settings" aria-selected="true">User Settings</a>
        </li>
        <?php if ($_SESSION['authority'] >= 5): ?>
            <li class="nav-item">
                <a class="nav-link" id="site-settings-tab" data-bs-toggle="tab" href="#site-settings" role="tab" aria-controls="site-settings" aria-selected="false">Site Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="game-settings-tab" data-bs-toggle="tab" href="#game-settings" role="tab" aria-controls="game-settings" aria-selected="false">Game Settings</a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="settingsTabsContent">
        <!-- User Settings Tab -->
        <div class="tab-pane fade show active" id="user-settings" role="tabpanel" aria-labelledby="user-settings-tab">
            <!-- User Settings Form -->
            <form id="userSettingsForm">
                <!-- Change Password Fields -->
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
            <div id="userSettingsMessage" class="mt-3"></div>
        </div>

        <?php if ($_SESSION['authority'] >= 5): ?>
            <!-- Site Settings Tab -->
            <div class="tab-pane fade" id="site-settings" role="tabpanel" aria-labelledby="site-settings-tab">
                <!-- Site Settings Form -->
                <!-- Include your existing site settings form here -->
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
            </div>

            <!-- Game Settings Tab -->
            <div class="tab-pane fade" id="game-settings" role="tabpanel" aria-labelledby="game-settings-tab">
                <!-- Game Settings Form -->
                <div class="card p-4">
                    <form id="gameSettingsForm">
                        <div class="form-section">
                            <div class="form-section-title">Game Settings</div>

                            <!-- Add Item to Item Mall -->
                            <div class="mb-3">
                                <h5>Add Item to Item Mall</h5>
                                <div class="mb-3">
                                    <label for="item_id" class="form-label">Item ID</label>
                                    <input type="number" class="form-control" id="item_id" name="item_id" required>
                                </div>
                                <div class="mb-3">
                                    <label for="item_group" class="form-label">Item Group</label>
                                    <select name="item_group" id="item_group" class="form-select" required>
                                        <option value="1">Hot Items</option>
                                        <option value="2">Costumes</option>
                                        <option value="3">Eidolon</option>
                                        <option value="4">Traveler's Item</option>
                                        <option value="5">Gear Improvement</option>
                                        <option value="6">Consumables</option>
                                        <option value="7">Housing</option>
                                        <option value="8">Fusion Scrolls</option>
                                        <option value="47">Daily Limit</option>
                                        <option value="48">Special Offers</option>
                                        <option value="49">Limited Items</option>
                                        <option value="51">Direct Ruby</option>
                                        <option value="52">Direct Rainbow</option>
                                        <option value="99">99 1 483</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="detail_type" class="form-label">Detail Type</label>
                                    <select name="detail_type" id="detail_type" class="form-select" required>
                                        <!-- Options will be populated via JavaScript based on item_group -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="item_num" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="item_num" name="item_num" required>
                                </div>
                                <div class="mb-3">
                                    <label for="money_unit" class="form-label">Money Unit</label>
                                    <select name="money_unit" id="money_unit" class="form-select" required>
                                        <option value="1">AP</option>
                                        <option value="2">Bonus</option>
                                        <option value="3">LP</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="point" class="form-label">Price</label>
                                    <input type="number" class="form-control" id="point" name="point" min="9" max="99999" required>
                                </div>
                                <div class="mb-3">
                                    <label for="special_price" class="form-label">Special Price (Discount)</label>
                                    <input type="number" class="form-control" id="special_price" name="special_price" min="9" max="99999">
                                </div>
                                <div class="mb-3">
                                    <label for="num_limit" class="form-label">Purchase Limit</label>
                                    <input type="number" class="form-control" id="num_limit" name="num_limit" min="1">
                                </div>
                                <div class="mb-3">
                                    <label for="flags" class="form-label">Flags</label>
                                    <select name="flags" id="flags" class="form-select">
                                        <option value="0">Normal</option>
                                        <option value="1">New</option>
                                        <option value="2">Hot</option>
                                        <option value="4">Sale</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="limit_level" class="form-label">Level Requirement</label>
                                    <input type="number" class="form-control" id="limit_level" name="limit_level" min="0">
                                </div>
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select name="gender" id="gender" class="form-select">
                                        <option value="-1">All</option>
                                        <option value="1">Female</option>
                                        <option value="2">Male</option>
                                        <option value="4">Chibi</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="not_sell_date" class="form-label">Not Sell Date (YYYYMMDDHHMM)</label>
                                    <input type="text" class="form-control" id="not_sell_date" name="not_sell_date">
                                </div>
                                <div class="mb-3">
                                    <label for="note" class="form-label">Note</label>
                                    <input type="text" class="form-control" id="note" name="note" maxlength="20">
                                </div>
                                <button type="submit" class="btn btn-primary">Add Item</button>
                                <div id="addItemMessage" class="mt-3"></div>
                            </div>

                            <!-- Search Item Mall Items -->
                            <div class="mb-3">
                                <h5>Search and Manage Item Mall Items</h5>
                                <form id="searchItemMallForm">
                                    <div class="mb-3">
                                        <label for="search_item_id" class="form-label">Item ID</label>
                                        <input type="number" class="form-control" id="search_item_id" name="search_item_id" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                                <div id="searchItemMallResults" class="mt-3"></div>
                            </div>

                            <!-- Edit Paragon -->
                            <div class="mb-3">
                                <h5>Edit Paragon</h5>
                                <form id="searchParagonForm">
                                    <div class="mb-3">
                                        <label for="paragon_category" class="form-label">Paragon Category</label>
                                        <input type="number" class="form-control" id="paragon_category" name="paragon_category" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                                <div id="paragonResults" class="mt-3"></div>
                            </div>

                        </div>
                    </form>
                    <div id="gameSettingsMessage" class="mt-3"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// JavaScript for dynamic detail_type options
document.addEventListener('DOMContentLoaded', function() {
    const itemGroupSelect = document.getElementById('item_group');
    const detailTypeSelect = document.getElementById('detail_type');

    const detailTypeOptions = {
        1: {1: 'Ruby Coins', 2: 'Hot Items', 3: 'New Players'},
        2: {1: 'Head', 2: 'Face', 3: 'Body', 4: 'Back', 5: 'Weapon', 6: 'Enchantment Cards', 7: 'Special', 8: 'Other'},
        3: {1: 'Eidolon', 2: 'Eidolon Costume', 3: 'Other'},
        4: {1: 'Backpacks', 2: 'Mounts', 3: 'Pets'},
        5: {1: 'Fortification', 2: 'Reshuffle', 3: 'Other'},
        6: {1: 'Boost Items', 2: 'Character Items', 3: 'Other'},
        7: {1: 'Princess Furniture', 2: 'Furniture', 3: 'Statues'},
        8: {1: 'Hat Costumes', 2: 'Face Costumes', 3: 'Body Costumes', 4: 'Back Costumes', 5: 'Weapon Costumes', 6: 'Mounts', 7: 'Eidolons', 8: 'Other'},
        47: {1: 'Daily Limited Items', 2: 'Daily New Item 2', 3: 'Daily New Item 3'},
        48: {1: 'Crazy Deals', 2: 'New Items', 3: 'Unknown'},
        49: {1: 'Weekly Offers', 2: '9 AP SHOP', 3: 'Crazy Deals'},
        51: {1: 'Ruby 40281'},
        52: {1: 'Rainbow 40688'},
        99: {1: '40001'},
    };

    function updateDetailTypeOptions() {
        const selectedGroup = itemGroupSelect.value;
        const options = detailTypeOptions[selectedGroup] || {};
        detailTypeSelect.innerHTML = '';
        for (const [value, text] of Object.entries(options)) {
            const optionElement = document.createElement('option');
            optionElement.value = value;
            optionElement.textContent = text;
            detailTypeSelect.appendChild(optionElement);
        }
    }

    itemGroupSelect.addEventListener('change', updateDetailTypeOptions);
    updateDetailTypeOptions();
});
</script>