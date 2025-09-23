<!-- Create Document Modal -->
<div id="cl-create-document-modal" class="clup-modal-overlay">
  <div class="clup-box">
    <!-- Close Button -->
    <button class="clup-close-btn">&times;</button>
    <!-- Modal Header -->
    <h1 class="clup-title">Create Document</h1>
    <!-- Form -->
    <form class="clup-form">
      <div class="clup-row">
        <div class="clup-field">
          <label>Document Title</label>
          <input type="text" placeholder="Enter title" />
        </div>
        <div class="clup-field">
          <label>Submission Date</label>
          <input type="date" />
        </div>
      </div>
      <div class="clup-row">
        <div class="clup-field">
          <label>Document Type</label>
          <input type="text" placeholder="e.g. Agreement" />
        </div>
        <div class="clup-field">
          <label>Add Realtor</label>
          <select>
            <option>Select Realtor</option>
            <option>Realtor 1</option>
            <option>Realtor 2</option>
          </select>
        </div>
      </div>
      <div class="clup-row-single">
        <div class="clup-field">
          <label>Document Content</label>
          <textarea rows="6" placeholder="Enter document content here..."></textarea>
        </div>
      </div>
      <div class="clup-row-single">
        <div class="clup-field">
          <label>Notes</label>
          <textarea rows="3" placeholder="Additional notes (optional)"></textarea>
        </div>
      </div>
      <!-- Actions -->
      <div class="clup-actions">
        <button type="button" class="clup-btn clup-cancel">Cancel</button>
        <button type="submit" class="clup-btn clup-create">Create Document</button>
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
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
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
  transition: color 0.2s;
}
.clup-close-btn:hover {
  color: #2f64e2;
}
/* =========================
   Title
========================= */
.clup-title {
  font-size: 1.375rem!important;
  font-weight: bold;
  text-align: left;
  color: #222;
  margin-bottom: 20px!important;
}
/* =========================
   Form Layout
========================= */
.clup-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.clup-row {
  display: flex;
  gap: 20px;
}
.clup-row .clup-field {
  flex: 1;
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
.clup-field select,
.clup-field textarea {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  box-sizing: border-box;
}
.clup-field textarea {
  resize: vertical;
  min-height: 100px;
}
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
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s ease;
}
.clup-cancel {
  background: #ddd;
  color: #333;
}
.clup-cancel:hover {
  background: #ccc;
}
.clup-create {
  background: #2f64e2;
  color: #FFF;
}
.clup-create:hover {
  background: #2a5acaff;
}
/* =========================
   Responsive
========================= */
@media (max-width: 600px) {
  .clup-row {
    flex-direction: column;
    gap: 15px;
  }
  .clup-box {
    padding: 20px;
  }
  .clup-field textarea {
    min-height: 80px;
  }
}
</style>