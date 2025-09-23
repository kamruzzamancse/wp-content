<div class="settings-management">
    <div class="sm-header">
        <h1>Settings</h1>
        <p>Manage system configuration, notifications, and policies</p>
    </div>

    <!-- Settings Navigation -->
    <div class="sm-navigation">
        <button class="sm-nav-item active" data-tab="system-config">
            <i class="fas fa-cog"></i>
            System Configuration
        </button>
        <button class="sm-nav-item" data-tab="notifications">
            <i class="fas fa-bell"></i>
            Notification Templates
        </button>
        <button class="sm-nav-item" data-tab="policies">
            <i class="fas fa-file-contract"></i>
            Terms & Privacy Policy
        </button>
    </div>

    <!-- System Configuration Tab -->
    <div class="sm-tab-content active" id="system-config">
        <div class="sm-card">
            <div class="sm-card-header">
                <h2>General Settings</h2>
            </div>
            <div class="sm-card-body">
                <form class="sm-form">
                    <div class="sm-form-row">
                        <div class="sm-form-group">
                            <label class="sm-form-label">Company Name</label>
                            <input type="text" class="sm-form-input" value="RealEstate Pro" placeholder="Enter company name">
                        </div>
                        <div class="sm-form-group">
                            <label class="sm-form-label">Default Timezone</label>
                            <select class="sm-form-select">
                                <option>(UTC-05:00) Eastern Time</option>
                                <option>(UTC-06:00) Central Time</option>
                                <option>(UTC-07:00) Mountain Time</option>
                                <option selected>(UTC-08:00) Pacific Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm-form-row">
                        <div class="sm-form-group">
                            <label class="sm-form-label">Date Format</label>
                            <select class="sm-form-select">
                                <option>MM/DD/YYYY</option>
                                <option>DD/MM/YYYY</option>
                                <option selected>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="sm-form-group">
                            <label class="sm-form-label">Currency</label>
                            <select class="sm-form-select">
                                <option selected>USD ($)</option>
                                <option>EUR (€)</option>
                                <option>GBP (£)</option>
                                <option>CAD (C$)</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label">Session Timeout</label>
                        <select class="sm-form-select">
                            <option>15 minutes</option>
                            <option>30 minutes</option>
                            <option selected>1 hour</option>
                            <option>2 hours</option>
                            <option>4 hours</option>
                        </select>
                        <div class="sm-form-help">Automatically log out users after period of inactivity</div>
                    </div>

                    <div class="sm-form-actions">
                        <button type="button" class="sm-btn sm-btn-secondary">Reset to Defaults</button>
                        <button type="submit" class="sm-btn sm-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Security Settings</h2>
            </div>
            <div class="sm-card-body">
                <form class="sm-form">
                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox" checked>
                            <span class="sm-checkbox-checkmark"></span>
                            Require strong passwords
                        </label>
                        <div class="sm-form-help">Passwords must contain uppercase, lowercase, numbers, and special characters</div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox" checked>
                            <span class="sm-checkbox-checkmark"></span>
                            Enable two-factor authentication
                        </label>
                        <div class="sm-form-help">Users will be required to set up 2FA on their next login</div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox">
                            <span class="sm-checkbox-checkmark"></span>
                            Enable login attempt limiting
                        </label>
                        <div class="sm-form-help">Lock accounts after 5 failed login attempts</div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label">Password Expiration</label>
                        <select class="sm-form-select">
                            <option>Never</option>
                            <option>30 days</option>
                            <option selected>90 days</option>
                            <option>180 days</option>
                        </select>
                        <div class="sm-form-help">How often users must update their passwords</div>
                    </div>

                    <div class="sm-form-actions">
                        <button type="button" class="sm-btn sm-btn-secondary">Reset to Defaults</button>
                        <button type="submit" class="sm-btn sm-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Backup Settings</h2>
            </div>
            <div class="sm-card-body">
                <form class="sm-form">
                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox" checked>
                            <span class="sm-checkbox-checkmark"></span>
                            Enable automatic backups
                        </label>
                    </div>

                    <div class="sm-form-row">
                        <div class="sm-form-group">
                            <label class="sm-form-label">Backup Frequency</label>
                            <select class="sm-form-select">
                                <option>Daily</option>
                                <option selected>Weekly</option>
                                <option>Monthly</option>
                            </select>
                        </div>
                        <div class="sm-form-group">
                            <label class="sm-form-label">Retention Period</label>
                            <select class="sm-form-select">
                                <option>7 days</option>
                                <option selected>30 days</option>
                                <option>90 days</option>
                                <option>1 year</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label">Last Backup</label>
                        <div class="sm-backup-status">
                            <i class="fas fa-check-circle sm-status-success"></i>
                            <span>Completed: June 15, 2023 02:30 AM (2.4 GB)</span>
                        </div>
                    </div>

                    <div class="sm-form-actions">
                        <button type="button" class="sm-btn sm-btn-secondary">
                            <i class="fas fa-download"></i>
                            Download Latest Backup
                        </button>
                        <button type="button" class="sm-btn sm-btn-primary">
                            <i class="fas fa-plus"></i>
                            Create Backup Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Templates Tab -->
    <div class="sm-tab-content" id="notifications">
        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Email Templates</h2>
                <button class="sm-btn sm-btn-primary">
                    <i class="fas fa-plus"></i>
                    New Template
                </button>
            </div>
            <div class="sm-card-body">
                <div class="sm-templates-list">
                    <div class="sm-template-item">
                        <div class="sm-template-info">
                            <h3>Welcome Email</h3>
                            <p>Sent to new users when they create an account</p>
                            <div class="sm-template-meta">
                                <span class="sm-template-status active">Active</span>
                                <span class="sm-template-date">Last modified: Jun 10, 2023</span>
                            </div>
                        </div>
                        <div class="sm-template-actions">
                            <button class="sm-btn sm-btn-icon" title="Preview">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sm-template-item">
                        <div class="sm-template-info">
                            <h3>Property Assignment Notification</h3>
                            <p>Sent to clients when assigned to a property</p>
                            <div class="sm-template-meta">
                                <span class="sm-template-status active">Active</span>
                                <span class="sm-template-date">Last modified: May 28, 2023</span>
                            </div>
                        </div>
                        <div class="sm-template-actions">
                            <button class="sm-btn sm-btn-icon" title="Preview">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sm-template-item">
                        <div class="sm-template-info">
                            <h3>Task Assignment Alert</h3>
                            <p>Sent to users when assigned a new task</p>
                            <div class="sm-template-meta">
                                <span class="sm-template-status inactive">Inactive</span>
                                <span class="sm-template-date">Last modified: Apr 15, 2023</span>
                            </div>
                        </div>
                        <div class="sm-template-actions">
                            <button class="sm-btn sm-btn-icon" title="Preview">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sm-template-item">
                        <div class="sm-template-info">
                            <h3>Document Upload Notification</h3>
                            <p>Sent to clients when new documents are uploaded</p>
                            <div class="sm-template-meta">
                                <span class="sm-template-status active">Active</span>
                                <span class="sm-template-date">Last modified: Jun 5, 2023</span>
                            </div>
                        </div>
                        <div class="sm-template-actions">
                            <button class="sm-btn sm-btn-icon" title="Preview">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="sm-btn sm-btn-icon" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Notification Preferences</h2>
            </div>
            <div class="sm-card-body">
                <form class="sm-form">
                    <div class="sm-form-group">
                        <label class="sm-form-label">Default Notification Channel</label>
                        <select class="sm-form-select">
                            <option selected>Email</option>
                            <option>In-app notification</option>
                            <option>SMS</option>
                            <option>Email and in-app</option>
                        </select>
                        <div class="sm-form-help">Default channel for all system notifications</div>
                    </div>

                    <div class="sm-form-group">
                        <h3 class="sm-form-subheader">Notification Types</h3>
                        
                        <div class="sm-notification-setting">
                            <div class="sm-notification-info">
                                <h4>New Message Alerts</h4>
                                <p>Notify users when they receive new messages</p>
                            </div>
                            <label class="sm-switch">
                                <input type="checkbox" checked>
                                <span class="sm-slider"></span>
                            </label>
                        </div>

                        <div class="sm-notification-setting">
                            <div class="sm-notification-info">
                                <h4>Task Reminders</h4>
                                <p>Send reminders for upcoming task deadlines</p>
                            </div>
                            <label class="sm-switch">
                                <input type="checkbox" checked>
                                <span class="sm-slider"></span>
                            </label>
                        </div>

                        <div class="sm-notification-setting">
                            <div class="sm-notification-info">
                                <h4>Property Updates</h4>
                                <p>Notify clients about changes to their properties</p>
                            </div>
                            <label class="sm-switch">
                                <input type="checkbox" checked>
                                <span class="sm-slider"></span>
                            </label>
                        </div>

                        <div class="sm-notification-setting">
                            <div class="sm-notification-info">
                                <h4>System Maintenance Alerts</h4>
                                <p>Notify about scheduled maintenance windows</p>
                            </div>
                            <label class="sm-switch">
                                <input type="checkbox">
                                <span class="sm-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="sm-form-actions">
                        <button type="submit" class="sm-btn sm-btn-primary">Save Preferences</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Terms & Privacy Policy Tab -->
    <div class="sm-tab-content" id="policies">
        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Terms of Service</h2>
                <div class="sm-card-actions">
                    <button class="sm-btn sm-btn-secondary">
                        <i class="fas fa-history"></i>
                        Version History
                    </button>
                    <button class="sm-btn sm-btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Terms
                    </button>
                </div>
            </div>
            <div class="sm-card-body">
                <div class="sm-policy-content">
                    <h3>1. Agreement to Terms</h3>
                    <p>By accessing our website and using our services, you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.</p>

                    <h3>2. Use License</h3>
                    <p>Permission is granted to temporarily use the materials on RealEstate Pro's website for personal or commercial use. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul>
                        <li>Modify or copy the materials;</li>
                        <li>Use the materials for any commercial purpose or for any public display;</li>
                        <li>Attempt to reverse engineer any software contained on RealEstate Pro's website;</li>
                        <li>Remove any copyright or other proprietary notations from the materials; or</li>
                        <li>Transfer the materials to another person or "mirror" the materials on any other server.</li>
                    </ul>

                    <h3>3. User Accounts</h3>
                    <p>When you create an account with us, you must provide accurate and complete information. You are solely responsible for the activity that occurs on your account and for keeping your password secure.</p>

                    <div class="sm-policy-meta">
                        <div class="sm-policy-version">
                            <strong>Current Version:</strong> v2.3
                        </div>
                        <div class="sm-policy-date">
                            <strong>Last Updated:</strong> June 1, 2023
                        </div>
                        <div class="sm-policy-status">
                            <strong>Status:</strong> <span class="sm-status-active">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Privacy Policy</h2>
                <div class="sm-card-actions">
                    <button class="sm-btn sm-btn-secondary">
                        <i class="fas fa-history"></i>
                        Version History
                    </button>
                    <button class="sm-btn sm-btn-primary">
                        <i class="fas fa-edit"></i>
                        Edit Policy
                    </button>
                </div>
            </div>
            <div class="sm-card-body">
                <div class="sm-policy-content">
                    <h3>1. Information We Collect</h3>
                    <p>We collect information you provide directly to us, such as when you create an account, update your profile, use our services, or communicate with us. The types of information we may collect include your name, email address, postal address, phone number, and any other information you choose to provide.</p>

                    <h3>2. How We Use Your Information</h3>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services;</li>
                        <li>Send you technical notices, updates, security alerts, and support messages;</li>
                        <li>Respond to your comments, questions, and requests;</li>
                        <li>Monitor and analyze trends, usage, and activities in connection with our services;</li>
                        <li>Detect, investigate, and prevent fraudulent transactions and other illegal activities;</li>
                        <li>Personalize and improve our services.</li>
                    </ul>

                    <h3>3. Information Sharing</h3>
                    <p>We may share personal information about you as follows:</p>
                    <ul>
                        <li>With vendors, consultants, and other service providers who need access to such information to carry out work on our behalf;</li>
                        <li>In response to a request for information if we believe disclosure is in accordance with any applicable law, regulation, or legal process;</li>
                        <li>If we believe your actions are inconsistent with our user agreements or policies, or to protect the rights, property, and safety of RealEstate Pro or others;</li>
                        <li>With your consent or at your direction.</li>
                    </ul>

                    <div class="sm-policy-meta">
                        <div class="sm-policy-version">
                            <strong>Current Version:</strong> v1.8
                        </div>
                        <div class="sm-policy-date">
                            <strong>Last Updated:</strong> May 15, 2023
                        </div>
                        <div class="sm-policy-status">
                            <strong>Status:</strong> <span class="sm-status-active">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm-card">
            <div class="sm-card-header">
                <h2>Consent Management</h2>
            </div>
            <div class="sm-card-body">
                <form class="sm-form">
                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox" checked>
                            <span class="sm-checkbox-checkmark"></span>
                            Require users to accept Terms of Service
                        </label>
                        <div class="sm-form-help">Users must accept Terms of Service during registration</div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label sm-checkbox-label">
                            <input type="checkbox" checked>
                            <span class="sm-checkbox-checkmark"></span>
                            Require users to accept Privacy Policy
                        </label>
                        <div class="sm-form-help">Users must accept Privacy Policy during registration</div>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-form-label">Consent Duration</label>
                        <select class="sm-form-select">
                            <option>6 months</option>
                            <option selected>12 months</option>
                            <option>24 months</option>
                            <option>Indefinite</option>
                        </select>
                        <div class="sm-form-help">How often users need to re-consent to updated policies</div>
                    </div>

                    <div class="sm-form-actions">
                        <button type="submit" class="sm-btn sm-btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.settings-management {
    background-color: #f5f7f9;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.sm-header {
    margin-bottom: 24px;
}

.sm-header h1 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 28px;
}

.sm-header p {
    color: #7f8c8d;
    margin: 0;
    font-size: 16px;
}

/* Navigation */
.sm-navigation {
    display: flex;
    border-bottom: 1px solid #dcdee0;
    margin-bottom: 24px;
}

.sm-nav-item {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.sm-nav-item:hover {
    color: #3498db;
}

.sm-nav-item.active {
    color: #3498db;
    border-bottom-color: #3498db;
}

/* Tab Content */
.sm-tab-content {
    display: none;
}

.sm-tab-content.active {
    display: block;
}

/* Card Styles */
.sm-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 24px;
    overflow: hidden;
}

.sm-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sm-card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
    font-weight: 600;
}

.sm-card-body {
    padding: 24px;
}

/* Form Styles */
.sm-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sm-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.sm-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sm-form-label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.sm-form-input, .sm-form-select, .sm-form-textarea {
    padding: 10px 12px;
    border: 1px solid #dcdee0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}

.sm-form-input:focus, .sm-form-select:focus, .sm-form-textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.sm-form-help {
    font-size: 13px;
    color: #7f8c8d;
    margin-top: 4px;
}

.sm-form-subheader {
    font-size: 16px;
    color: #2c3e50;
    margin: 0 0 12px 0;
    font-weight: 600;
}

/* Checkbox Styles */
.sm-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: normal;
}

.sm-checkbox-label input {
    display: none;
}

.sm-checkbox-checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #bdc3c7;
    border-radius: 4px;
    display: inline-block;
    position: relative;
    transition: all 0.2s;
}

.sm-checkbox-label input:checked + .sm-checkbox-checkmark {
    background: #3498db;
    border-color: #3498db;
}

.sm-checkbox-label input:checked + .sm-checkbox-checkmark::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Switch Toggle */
.sm-notification-setting {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.sm-notification-info h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #2c3e50;
}

.sm-notification-info p {
    margin: 0;
    font-size: 13px;
    color: #7f8c8d;
}

.sm-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
}

.sm-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.sm-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.sm-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .sm-slider {
    background-color: #3498db;
}

input:checked + .sm-slider:before {
    transform: translateX(24px);
}

/* Button Styles */
.sm-btn {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.sm-btn-primary {
    background: #3498db;
    color: white;
}

.sm-btn-primary:hover {
    background: #2980b9;
}

.sm-btn-secondary {
    background: #ecf0f1;
    color: #7f8c8d;
}

.sm-btn-secondary:hover {
    background: #dde4e6;
}

.sm-btn-icon {
    padding: 8px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
}

.sm-btn-icon:hover {
    background: #f0f0f0;
    color: #3498db;
}

.sm-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #ecf0f1;
}

/* Backup Status */
.sm-backup-status {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 14px;
}

.sm-status-success {
    color: #27ae60;
}

/* Templates List */
.sm-templates-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sm-template-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
}

.sm-template-info h3 {
    margin: 0 0 6px 0;
    font-size: 15px;
    color: #2c3e50;
}

.sm-template-info p {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #7f8c8d;
}

.sm-template-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #95a5a6;
}

.sm-template-status {
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}

.sm-template-status.active {
    background: #e7f6ef;
    color: #27ae60;
}

.sm-template-status.inactive {
    background: #fef5e7;
    color: #f39c12;
}

.sm-template-actions {
    display: flex;
    gap: 4px;
}

/* Policy Content */
.sm-policy-content {
    line-height: 1.6;
}

.sm-policy-content h3 {
    color: #2c3e50;
    margin: 24px 0 12px 0;
    font-size: 16px;
}

.sm-policy-content h3:first-child {
    margin-top: 0;
}

.sm-policy-content p {
    color: #34495e;
    margin: 0 0 16px 0;
}

.sm-policy-content ul {
    color: #34495e;
    margin: 0 0 16px 0;
    padding-left: 20px;
}

.sm-policy-content li {
    margin-bottom: 6px;
}

.sm-policy-meta {
    display: flex;
    gap: 24px;
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #ecf0f1;
    font-size: 14px;
}

.sm-policy-meta div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sm-policy-meta strong {
    color: #2c3e50;
}

.sm-status-active {
    color: #27ae60;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sm-navigation {
        flex-direction: column;
        border-bottom: none;
        gap: 4px;
    }
    
    .sm-nav-item {
        border-bottom: none;
        border-left: 3px solid transparent;
        justify-content: flex-start;
    }
    
    .sm-nav-item.active {
        border-bottom-color: transparent;
        border-left-color: #3498db;
    }
    
    .sm-form-row {
        grid-template-columns: 1fr;
    }
    
    .sm-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .sm-template-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .sm-template-actions {
        align-self: flex-end;
    }
    
    .sm-policy-meta {
        flex-direction: column;
        gap: 12px;
    }
    
    .sm-notification-setting {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation functionality
    const navItems = document.querySelectorAll('.sm-nav-item');
    const tabContents = document.querySelectorAll('.sm-tab-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active navigation item
            navItems.forEach(navItem => navItem.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected tab content
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Form submission handlers
    const forms = document.querySelectorAll('.sm-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Settings saved successfully!');
        });
    });
    
    // Backup button functionality
    const backupButton = document.querySelector('.sm-btn-primary .fa-plus');
    if (backupButton) {
        backupButton.closest('button').addEventListener('click', function() {
            alert('Starting backup process...');
        });
    }
    
    // Template action buttons
    const templateActions = document.querySelectorAll('.sm-template-actions button');
    
    templateActions.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('title');
            const templateName = this.closest('.sm-template-item').querySelector('h3').textContent;
            alert(`${action}: ${templateName}`);
        });
    });
    
    // Policy edit buttons
    const policyEditButtons = document.querySelectorAll('.sm-card-actions .sm-btn-primary');
    
    policyEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const policyType = this.closest('.sm-card').querySelector('h2').textContent;
            alert(`Editing: ${policyType}`);
        });
    });
});
</script>