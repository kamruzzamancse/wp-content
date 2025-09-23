<!-- Upload Document Modal -->
<div id="cl-upload-document-modal" class="clup-modal-overlay">
  <div class="clup-box">
    <!-- Close Button -->
    <button class="clup-close-btn">&times;</button>

    <!-- Modal Header -->
    <h1 class="clup-title">Upload Document</h1>

    <!-- Form -->
    <form class="clup-form">
      <!-- Document Title -->
      <div class="clup-row-single">
        <div class="clup-field">
          <label>Document Title</label>
          <input type="text" placeholder="Enter title" />
        </div>
      </div>

      <!-- Document Type -->
      <div class="clup-row-single">
        <div class="clup-field">
          <label>Document Type</label>
          <select>
            <option value="">Select Document Type</option>
            <option value="business-cards">Business Cards</option>
            <option value="seller-checklist">Seller Checklist</option>
            <option value="buyer-checklist">Buyer Checklist</option>
          </select>
        </div>
      </div>

      <!-- File Upload -->
      <div class="clup-upload-box">
        <div class="clup-upload-content">
          <div class="clup-upload-icon">⬆</div>
          <p>Upload File</p>
          <span>Format: .jpeg, .png, .pdf & Max file size: 25 MB</span>

          <!-- Browse Button -->
          <button type="button" class="clup-browse">Browse</button>

          <!-- Hidden File Input -->
          <input type="file" id="clup-file-input" class="clup-file-input" accept=".jpeg,.jpg,.png,.pdf" style="display:none;">
        </div>
      </div>

      <!-- Actions -->
      <div class="clup-actions">
        <button type="submit" class="clup-btn clup-upload">Upload</button>
      </div>
    </form>
  </div>
</div>

<style>
/* =========================
   Modal Overlay
========================= */
.clup-modal-overlay {
  display: none; /* Hidden by default */
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  justify-content: center;
  align-items: center;
  padding: 20px;
}

/* Show modal */
.clup-modal-overlay.show {
  display: flex !important;
}

/* =========================
   Modal Box
========================= */
.clup-box {
  background: #fff;
  width: 100%;
  max-width: 650px;
  border-radius: 10px;
  padding: 25px 30px;
  position: relative;
  animation: fadeInUp 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Animation */
@keyframes fadeInUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* =========================
   Close Button
========================= */
.clup-close-btn {
  position: absolute;
  right: 15px;
  top: 15px;
  border: none;
  background: transparent;
  font-size: 26px;
  font-weight: bold;
  cursor: pointer;
  color: #333;
}
.clup-close-btn:hover { color: #e63946; }

/* =========================
   Title
========================= */
.clup-title {
  font-size: 1.375rem!important;
  font-weight: bold;
  text-align: left;
  color: #222;
  margin-bottom: 20px;
}

/* =========================
   Form Layout
========================= */
.clup-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding-top: 30px;
}

.clup-row-single {
  display: flex;
}
.clup-row-single .clup-field {
  flex: 1;
}

.clup-field label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
  color: #444;
}
.clup-field input,
.clup-field select {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
}

/* =========================
   File Upload
========================= */
.clup-upload-box {
  border: 2px dashed #999;
  border-radius: 8px;
  padding: 25px;
  text-align: center;
  background: #fafafa;
}
.clup-upload-icon {
  font-size: 30px;
  margin-bottom: 10px;
}
.clup-upload-content p {
  font-size: 15px;
  margin: 0 0 5px;
  font-weight: 600;
}
.clup-upload-content span {
  font-size: 13px;
  color: #777;
}
.clup-browse {
  margin-top: 12px;
  background: #444;
  color: #fff;
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
.clup-browse:hover { background: #222; }

/* =========================
   Actions
========================= */
.clup-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 15px;
}
.clup-btn {
  padding: 10px 20px!important;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}
.clup-cancel { background: #ddd; color: #333; }
.clup-cancel:hover { background: #ccc; }
.clup-upload { background: #2f64e2!important; color: #FFF!important; }
.clup-upload:hover { background: #2a5acaff!important; }

/* =========================
   Responsive
========================= */
@media (max-width: 600px) {
  .clup-box { padding: 20px; }
}
</style>