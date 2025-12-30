<div class="back-link">
    <a href="?tab=settings" class="pd-back-link">
        <span class="pd-back-link__arrow">←</span>
        <h1 class="header-title">⚙️ Settings</h1>
    </a>
</div>

<div class="note-header-container">
    <!-- ===== NOTE HEADER BOX ===== -->
    <div class="cld-box cld-note-header-box">
        <div class="cld-box-header">
            <span>📝 Note Header</span>
            <button type="button" class="cld-send-btn" id="add-note-header-btn">
                Add Note Header
            </button>
        </div>
        <div class="cld-box-body">
            <ul id="note-header-list">
                <li class="empty">No note headers found</li>
            </ul>
        </div>
    </div>

    <!-- ===== NOTE HEADER MODAL ===== -->
    <div id="noteHeaderModal" class="note-modal">
        <div class="note-modal-content">
            <span class="note-modal-close" data-modal="noteHeaderModal">&times;</span>
            <h3 id="note-header-modal-title">Add Note Header</h3>
            <input type="hidden" id="note_id">
            <label for="note_header_input">Note Header</label>
            <input type="text" id="note_header_input" placeholder="Note Header">
            <button type="button" id="save_note_header" class="cld-send-btn">Save</button>
        </div>
    </div>
</div>

<style>
.note-header-container {
    max-width: 700px;
    padding: 25px;
    background-color: #fff;
    border-radius: 8px;
    margin-left: 0;
    margin-right: auto;    
}

/* ===============================
   NOTE HEADER BOX
   =============================== */
.cld-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cld-box-header {
    background: #3578c6;
    color: #fff;
    padding: 12px 16px;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cld-box-body {
    padding: 20px;
}

.cld-send-btn {
    padding: 8px 14px;
    background: #4e6ef2;
    color: #fff !important;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
}

.cld-send-btn:hover {
    background: #3b54c1;
}

/* ===============================
   NOTE HEADER LIST
   =============================== */
#note-header-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

#note-header-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    font-size: 15px;
}

.note-header-text {
    flex: 1;
    font-weight: 500;
}

.edit-header,
.delete-header {
    margin-left: 10px;
    cursor: pointer;
    font-size: 14px;
}

.edit-header:hover { color: #3578c6; }
.delete-header:hover { color: #e74c3c; }

/* ===============================
   MODALS
   =============================== */
.note-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.note-modal.show {
    display: flex;
}

.note-modal-content {
    background: #fff;
    padding: 20px;
    width: 400px;
    border-radius: 10px;
    position: relative;
}

.note-modal-content h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.note-modal-content label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

.note-modal-content input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.note-modal-close {
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 22px;
    cursor: pointer;
}
</style>