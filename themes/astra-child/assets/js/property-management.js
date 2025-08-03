document.addEventListener('DOMContentLoaded', function() {
            // Make gallery images clickable to view larger
            document.querySelectorAll('.gallery img').forEach(img => {
                img.addEventListener('click', function() {
                    // Get the main image container in the same property item
                    const mainImg = this.closest('.property-item').querySelector('.main-image');
                    
                    // Swap the src between clicked image and main image
                    const tempSrc = mainImg.src;
                    mainImg.src = this.src;
                    this.src = tempSrc;
                });
            });
        });