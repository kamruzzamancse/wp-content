// --------------------------
// Pass logged-in WordPress user ID from PHP
// --------------------------
// Add this in header.php or via wp_localize_script in functions.php
// Example:
// <script>window.wpUserId = <?php echo get_current_user_id() ?: 0; ?>;</script>

const userId = window.wpUserId || 0; // fallback 0 for guests
const pageKey = `stickyNotes_user_${userId}`; // unique key per user

// Detect touch devices
const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;

// Sticky Notes container and button
const container = document.querySelector('.sticky-notes-container');
const addBtn = document.querySelector('.add-note-btn');

// Load existing notes from localStorage
let notesData = JSON.parse(localStorage.getItem(pageKey) || '[]');

// Save notes to localStorage
function saveNotes() {
    localStorage.setItem(pageKey, JSON.stringify(notesData));
}

// Create a single note
function createNote(noteObj) {
    const note = document.createElement('div');
    note.className = 'sticky-note';
    note.style.top = noteObj.top + 'px';
    note.style.left = noteObj.left + 'px';

    const noteHeader = document.createElement('div');
    noteHeader.className = 'note-header';

    const dragHandle = document.createElement('div');
    dragHandle.className = 'drag-handle';
    dragHandle.innerHTML = isTouchDevice ? '☰ Drag me' : '☰';

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'delete-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.addEventListener('click', e => {
        e.stopPropagation();
        deleteNote(noteObj, note);
    });

    const textarea = document.createElement('textarea');
    textarea.value = noteObj.text;
    textarea.placeholder = "Write your note here...";
    textarea.addEventListener('input', () => {
        noteObj.text = textarea.value;
        saveNotes();
    });

    noteHeader.appendChild(dragHandle);
    noteHeader.appendChild(deleteBtn);
    note.appendChild(noteHeader);
    note.appendChild(textarea);
    container.appendChild(note);

    makeDraggable(note, noteObj);
}

// Delete a note
function deleteNote(noteObj, noteElement) {
    noteElement.remove();
    const idx = notesData.indexOf(noteObj);
    if (idx > -1) notesData.splice(idx, 1);
    saveNotes();
}

// Make note draggable
function makeDraggable(el, noteObj) {
    let isDown = false, offset = [0, 0];

    el.querySelector('.drag-handle').addEventListener('mousedown', startDrag);
    el.querySelector('.drag-handle').addEventListener('touchstart', e => startDrag(e.touches[0]));

    function startDrag(e) {
        isDown = true;
        offset = [el.offsetLeft - e.clientX, el.offsetTop - e.clientY];
        el.style.zIndex = 1000;

        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchmove', handleTouchMove);
        document.addEventListener('touchend', stopDrag);
    }

    function handleMouseMove(e) { if (isDown) moveElement(e.clientX, e.clientY); }
    function handleTouchMove(e) { if (isDown) moveElement(e.touches[0].clientX, e.touches[0].clientY); }

    function moveElement(clientX, clientY) {
        const newLeft = clientX + offset[0];
        const newTop = clientY + offset[1];

        const containerRect = container.getBoundingClientRect();
        const noteRect = el.getBoundingClientRect();
        const minLeft = 0, maxLeft = containerRect.width - noteRect.width;
        const minTop = 0, maxTop = containerRect.height - noteRect.height;

        el.style.left = Math.min(Math.max(newLeft, minLeft), maxLeft) + 'px';
        el.style.top = Math.min(Math.max(newTop, minTop), maxTop) + 'px';

        noteObj.left = parseFloat(el.style.left);
        noteObj.top = parseFloat(el.style.top);
        saveNotes();
    }

    function stopDrag() {
        isDown = false;
        el.style.zIndex = '';
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchmove', handleTouchMove);
        document.removeEventListener('touchend', stopDrag);
    }
}

// Load existing notes
notesData.forEach(noteObj => createNote(noteObj));

// Add new note
addBtn.addEventListener('click', () => {
    const noteObj = { text: '', top: 80 + notesData.length*20, left: 20 + notesData.length*20 };
    notesData.push(noteObj);
    createNote(noteObj);
    saveNotes();
});
