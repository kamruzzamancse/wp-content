<?php
    $upload_dir = wp_upload_dir();
    $image_url = $upload_dir['baseurl'];
?>
<div id="cldoc-modal-1" class="cldoc-modal">
    <div class="cldoc-modal-overlay"></div>
    <div class="cldoc-modal-box">
        <button class="cldoc-close">&times;</button>

        <div class="cldoc-details-container">
            <!-- Realtor Section -->
            <div class="cldoc-realtor-section">
                <h2 class="cldoc-section-title">Realtor Details</h2>
                
                <div class="cldoc-realtor-header">
                    <img src="<?php echo esc_url( $image_url . '/2025/08/client-photo.jpg' ); ?>" alt="Realtor" class="cldoc-realtor-img">
                    <div>
                        <h3 class="cldoc-realtor-name">Donnelly</h3>
                        <p class="cldoc-realtor-title">Realtor</p>
                    </div>
                </div>

                <div class="cldoc-contact-info">
                    <div class="cldoc-contact-row">
                        <label>Email</label>
                        <span>Support.info@gmail.com</span>
                    </div>
                    <div class="cldoc-contact-row">
                        <label>Phone Number</label>
                        <span>000-998-23287</span>
                    </div>
                    <div class="cldoc-contact-row">
                        <label>Address</label>
                        <span>Le Marais, Paris</span>
                    </div>
                </div>
            </div>
            
            <!-- Property Section -->
            <div class="cldoc-property-section">
                <h2 class="cldoc-section-title">Property Details</h2>
                
                <div class="cldoc-property-info">
                    <div class="cldoc-property-row">
                        <label>Property Name</label>
                        <span>1234 Elm Street, NY 10001</span>
                    </div>
                    <div class="cldoc-property-row">
                        <label>Document Title</label>
                        <span>Final Inspection Report</span>
                    </div>
                    <div class="cldoc-property-row">
                        <label>Document Type</label>
                        <span>Inspection Report</span>
                    </div>
                    <div class="cldoc-property-row">
                        <label>Due Date</label>
                        <span>11 Aug 2025</span>
                    </div>
                    <div class="cldoc-property-row cldoc-notes-row">
                        <label>Notes</label>
                        <span class="cldoc-notes">
                            Just a quick reminder to review the listings I sent and let me know which properties you'd like to visit. Also, please have your pre-approval letter ready if you're planning to make an offer soon!
                        </span>
                    </div>
                </div>
                
                <div class="cldoc-pdf-section">
                    <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" alt="PDF" class="cldoc-pdf-icon">
                    <span>Pdf File</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Open modal
    document.querySelectorAll(".cld-action").forEach(btn => {
        btn.addEventListener("click", function () {
            const modalId = this.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if(modal) modal.classList.add("show");
        });
    });

    // Close modal (button)
    document.querySelectorAll(".cldoc-close").forEach(btn => {
        btn.addEventListener("click", function () {
            const modal = this.closest(".cldoc-modal");
            if(modal) modal.classList.remove("show");
        });
    });

    // Close modal (overlay)
    document.querySelectorAll(".cldoc-modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function () {
            const modal = this.closest(".cldoc-modal");
            if(modal) modal.classList.remove("show");
        });
    });
});
</script>

<style>
/* Modal Base */
.cldoc-modal {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    font-family: "Arial", sans-serif;
}
.cldoc-modal.show { display: flex; }

/* Overlay */
.cldoc-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.6);
    cursor: pointer;
    z-index: 1000;
}

/* Modal Box */
.cldoc-modal-box {
    position: relative;
    background: #fff;
    max-width: 500px;
    width: 95%;
    max-height: 95vh;
    overflow-y: auto;
    padding: 20px;
    border-radius: 12px;
    z-index: 1001;
    animation: fadeIn 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Close Button */
.cldoc-close {
    position: absolute;
    top: 12px;
    right: 14px;
    font-size: 26px;
    font-weight: bold;
    cursor: pointer;
    color: #333;
    border: none;
    background: none;
}

/* Section Titles */
.cldoc-section-title {
    font-size: 22px!important;
    font-weight: bold;
    margin: 10px 0 18px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 6px;
}

/* Realtor Header */
.cldoc-realtor-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.cldoc-realtor-img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}
.cldoc-realtor-name {
    margin-top: 25px;
    font-size: 16px!important;
    font-weight: bold;
}
.cldoc-realtor-title {
    color: #007bff;
    font-size: 14px;
}
.entry-content h3 {
    margin-bottom: 0px!important;
}

/* Info Rows */
.cldoc-contact-info,
.cldoc-property-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cldoc-contact-row,
.cldoc-property-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}
.cldoc-contact-row label,
.cldoc-property-row label {
    font-size: 14px;
    font-weight: bold;
    color: #444;
    min-width: 130px;
}
.cldoc-contact-row span,
.cldoc-property-row span,
.cldoc-notes {
    font-size: 14px;
    color: #222;
    flex: 1;
}

/* Notes */
.cldoc-notes {
    line-height: 1.4;
    display: block;
}

/* PDF Section */
.cldoc-pdf-section {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-top: 20px;
    gap: 8px;
}
.cldoc-pdf-icon {
    width: 40px;
    height: 40px;
}
.cldoc-pdf-section span {
    font-size: 14px;
    font-weight: 500;
}

/* Animation */
@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.95);}
    to {opacity: 1; transform: scale(1);}
}

.cldoc-property-section{
    margin-top: 25px;
}
</style>

<script>
/* document.addEventListener("DOMContentLoaded", function () {
    // Open modal
    document.querySelectorAll(".cld-action").forEach(btn => {
        btn.addEventListener("click", function () {
            const modalId = this.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add("show");
        });
    });

    // Close modal when clicking the close button
    document.querySelectorAll(".cldoc-close").forEach(btn => {
        btn.addEventListener("click", function () {
            const modal = this.closest(".cldoc-modal");
            if (modal) modal.classList.remove("show");
        });
    });

    // Close modal when clicking outside the modal box (overlay)
    document.querySelectorAll(".cldoc-modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function () {
            const modal = this.closest(".cldoc-modal");
            if (modal) modal.classList.remove("show");
        });
    });

    // Optional: Close modal with ESC key
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            document.querySelectorAll(".cldoc-modal.show").forEach(modal => {
                modal.classList.remove("show");
            });
        }
    });
}); */
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // ===== OPEN MODAL =====
    document.querySelectorAll("[data-modal]").forEach(trigger => {
        trigger.addEventListener("click", function () {
            const modalId = this.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = "flex"; // show modal
        });
    });

    // ===== CLOSE MODAL =====
    function closeModal(modal) {
        if(modal) modal.style.display = "none";
    }

    // Close via "x" buttons
    document.querySelectorAll(".clup-close-btn, .cldoc-close").forEach(btn => {
        btn.addEventListener("click", function () {
            const modal = this.closest(".clup-modal-overlay, .cldoc-modal");
            closeModal(modal);
        });
    });

    // Close via cancel buttons
    document.querySelectorAll(".clup-cancel").forEach(btn => {
        btn.addEventListener("click", function () {
            const modal = this.closest(".clup-modal-overlay");
            closeModal(modal);
        });
    });

    // Close when clicking outside modal content
    document.querySelectorAll(".clup-modal-overlay, .cldoc-modal-overlay").forEach(overlay => {
        overlay.addEventListener("click", function (e) {
            if (e.target === overlay) closeModal(overlay.closest(".clup-modal-overlay, .cldoc-modal"));
        });
    });

    // ===== FILE UPLOAD TRIGGER =====
    document.querySelectorAll(".clup-browse").forEach(btn => {
        btn.addEventListener("click", function () {
            const modal = btn.closest(".clup-modal-overlay");
            if (!modal) return;
            const fileInput = modal.querySelector(".clup-file-input");
            if (fileInput) fileInput.click();
        });
    });

    // Optional: show selected file name
    document.querySelectorAll(".clup-file-input").forEach(input => {
        input.addEventListener("change", function () {
            if (input.files.length > 0) {
                const fileName = input.files[0].name;
                console.log("Selected file:", fileName); // replace with UI update if needed
            }
        });
    });
});
</script>

