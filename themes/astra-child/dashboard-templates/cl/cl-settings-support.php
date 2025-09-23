<div class="cl-back-link">
    <a href="?tab=settings" class="cl-back-link">
        <span class="cl-header-arrow">←</span>
        <h1 class="header-title">Settings</h1>
    </a>
</div>

<div class="ss-contact-form-container">
    <h2 class="ss-contact-form-title">Send Us a Message</h2>
    
    <form class="ss-contact-form">
        <div class="ss-form-row">
            <div class="ss-form-group">
                <label class="ss-form-label">Name</label>
                <input type="text" placeholder="Enter name" class="ss-form-input">
            </div>
            <div class="ss-form-group">
                <label class="ss-form-label">Email</label>
                <input type="email" placeholder="Enter Email" class="ss-form-input">
            </div>
        </div>
        
        <div class="ss-form-group">
            <label class="ss-form-label">Phone</label>
            <input type="tel" placeholder="Enter phone" class="ss-form-input">
        </div>
        
        <div class="ss-form-group">
            <label class="ss-form-label">Message</label>
            <textarea placeholder="Message" class="ss-form-textarea"></textarea>
        </div>
        
        <button type="submit" class="ss-submit-button">Send Us</button>
    </form>
</div>

<style>
.ss-contact-form-container {
    max-width: 700px;
    padding: 25px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    font-family: Arial, sans-serif;
}

.ss-contact-form-title {
    font-size: 1.375rem!important;
    font-weight: bold;
    margin-bottom: 25px;
    color: #333;
    text-align: center;
}

.ss-contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ss-form-row {
    display: flex;
    gap: 20px;
}

.ss-form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.ss-form-label {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    color: #555;
}

.ss-form-input {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}

.ss-form-textarea {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    min-height: 120px;
    resize: vertical;
    width: 100%;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

.ss-submit-button {
    background-color: #3498db;
    color: #FFF!important;
    border: none;
    padding: 12px 25px;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    align-self: flex-end;
}

.ss-submit-button:hover {
    background-color: #2980b9;
}

button[type="submit"] {
    background-color: #3498db;
    color: #FFF!important;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .ss-form-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .ss-contact-form-container {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .ss-submit-button {
        background-color: #3498db;
        color: #FFF!important;
    }
}
</style>