// Function to get or create a unique user ID
function getUserId() {
    let userId = localStorage.getItem('userId');
    if (!userId) {
        // Generate a random user ID if one doesn't exist
        userId = 'user_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        localStorage.setItem('userId', userId);
    }
    return userId;
}

// Touch support detection
const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;

/* Sticky Notes JS */
document.querySelectorAll('.sticky-notes-container').forEach((container, index) => {
    const addBtn = container.parentElement.querySelector('.add-note-btn');
    const userId = getUserId(); // Get the unique user ID
    const pageKey = `stickyNotes_${userId}_page_${index}`; // Unique storage key per user and container
    let notesData = JSON.parse(localStorage.getItem(pageKey) || '[]');
    
    function saveNotes() {
        localStorage.setItem(pageKey, JSON.stringify(notesData));
    }
    
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
        
        noteHeader.appendChild(dragHandle);
        noteHeader.appendChild(deleteBtn);
        note.appendChild(noteHeader);
        note.appendChild(textarea);
        container.appendChild(note);
        
        textarea.addEventListener('input', () => {
            noteObj.text = textarea.value;
            saveNotes();
        });
        
        makeDraggable(note, noteObj);
    }
    
    function deleteNote(noteObj, noteElement) {
        noteElement.remove();
        const idx = notesData.indexOf(noteObj);
        if (idx > -1) {
            notesData.splice(idx, 1);
            saveNotes();
        }
    }
    
    function makeDraggable(el, noteObj) {
        let isDown = false, offset = [0, 0];
        
        // Mouse events for desktop
        el.querySelector('.drag-handle').addEventListener('mousedown', startDrag);
        
        // Touch events for mobile
        el.querySelector('.drag-handle').addEventListener('touchstart', function(e) {
            e.preventDefault();
            startDrag(e.touches[0]);
        });
        
        function startDrag(e) {
            isDown = true;
            offset = [el.offsetLeft - e.clientX, el.offsetTop - e.clientY];
            el.style.zIndex = 1000; // Bring to front while dragging
            
            // Add event listeners for move and end
            if (isTouchDevice) {
                document.addEventListener('touchmove', handleTouchMove);
                document.addEventListener('touchend', stopDrag);
            } else {
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', stopDrag);
            }
        }
        
        function handleMouseMove(e) {
            if (isDown) {
                moveElement(e.clientX, e.clientY);
            }
        }
        
        function handleTouchMove(e) {
            if (isDown) {
                moveElement(e.touches[0].clientX, e.touches[0].clientY);
            }
        }
        
        function moveElement(clientX, clientY) {
            const newLeft = clientX + offset[0];
            const newTop = clientY + offset[1];
            
            // Boundary checks to keep note within container
            const containerRect = container.getBoundingClientRect();
            const noteRect = el.getBoundingClientRect();
            
            const minLeft = 0;
            const maxLeft = containerRect.width - noteRect.width;
            const minTop = 0;
            const maxTop = containerRect.height - noteRect.height;
            
            el.style.left = Math.min(Math.max(newLeft, minLeft), maxLeft) + 'px';
            el.style.top = Math.min(Math.max(newTop, minTop), maxTop) + 'px';
            
            noteObj.left = parseFloat(el.style.left);
            noteObj.top = parseFloat(el.style.top);
            saveNotes();
        }
        
        function stopDrag() {
            isDown = false;
            el.style.zIndex = ''; // Reset z-index
            
            // Remove event listeners
            if (isTouchDevice) {
                document.removeEventListener('touchmove', handleTouchMove);
                document.removeEventListener('touchend', stopDrag);
            } else {
                document.removeEventListener('mousemove', handleMouseMove);
                document.removeEventListener('mouseup', stopDrag);
            }
        }
    }
    
    // Load existing notes
    notesData.forEach(noteObj => createNote(noteObj));
    
    // Add new note button
    addBtn.addEventListener('click', () => {
        const noteObj = {
            text: '',
            top: 80 + (notesData.length * 20),
            left: 20 + (notesData.length * 20)
        };
        notesData.push(noteObj);
        createNote(noteObj);
        saveNotes();
    });
});