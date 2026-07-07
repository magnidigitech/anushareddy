document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('open');
            navToggle.classList.toggle('active');
            
            // Animate hamburger lines
            const lines = navToggle.querySelectorAll('span');
            if (navToggle.classList.contains('active')) {
                lines[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                lines[1].style.opacity = '0';
                lines[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                lines[0].style.transform = 'none';
                lines[1].style.opacity = '1';
                lines[2].style.transform = 'none';
            }
        });
    }

    // 2. Client-side Live Search (for Collections Page)
    const searchInput = document.getElementById('searchInput');
    const collectionCards = document.querySelectorAll('.collection-card');
    const noResultsMsg = document.getElementById('noResultsMessage');
    const resultsSummary = document.getElementById('resultsSummary');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            collectionCards.forEach(card => {
                const title = card.querySelector('.card-title') ? card.querySelector('.card-title').textContent.toLowerCase() : '';
                const descEl = card.querySelector('.card-desc');
                const desc = descEl ? descEl.textContent.toLowerCase() : '';
                const category = card.getAttribute('data-category') ? card.getAttribute('data-category').toLowerCase() : '';
                
                if (title.includes(query) || desc.includes(query) || category.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update UI based on search results
            if (noResultsMsg) {
                if (visibleCount === 0) {
                    noResultsMsg.style.display = 'block';
                } else {
                    noResultsMsg.style.display = 'none';
                }
            }

            if (resultsSummary) {
                if (query === '') {
                    resultsSummary.textContent = '';
                } else {
                    resultsSummary.textContent = `Showing ${visibleCount} design${visibleCount === 1 ? '' : 's'} matching "${query}"`;
                }
            }
        });
    }

    // 3. Image Zoom/Lightbox Triggers
    const zoomableImages = document.querySelectorAll('.zoomable');
    zoomableImages.forEach(img => {
        img.addEventListener('click', () => {
            openLightbox(img.src);
        });
    });

    // 4. Celebrity Showcase Jump-Cut Carousel
    initCelebrityCarousel();

    // 5. Hero Slideshow – auto cross-fade
    initHeroSlideshow();
});

// ── Hero Slideshow ────────────────────────────────────────────
function initHeroSlideshow() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length < 2) return; // Nothing to do with 0 or 1 slide

    let current = 0;

    const advance = () => {
        slides[current].classList.remove('active');
        current = (current + 1) % slides.length;
        slides[current].classList.add('active');
    };

    setInterval(advance, 5000); // Change slide every 5 seconds
}

// Premium Fullscreen Lightbox Actions
function openLightbox(src) {
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    if (lightbox && lightboxImg) {
        lightboxImg.src = src;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden'; // Lock scrolling
    }
}

function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Bind Escape key event to close lightbox
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Celebrity Showcase Carousel - Center Focus Smooth Scroller
function initCelebrityCarousel() {
    const marquee = document.querySelector('.celebrity-marquee');
    const track = document.querySelector('.celebrity-track');
    if (!marquee || !track) return;

    const cards = track.querySelectorAll('.celebrity-card');
    if (cards.length === 0) return;

    const totalItems = parseInt(track.getAttribute('data-count'), 10) || (cards.length / 4);
    let currentIndex = totalItems; // Start in the second set to populate previous items on the left side
    let isTransitioning = false;

    // Detect center card and scale it up dynamically
    const updateFocus = () => {
        const marqueeCenter = marquee.scrollLeft + (marquee.offsetWidth / 2);
        
        let closestCard = null;
        let minDistance = Infinity;
        
        cards.forEach(card => {
            const cardCenter = card.offsetLeft + (card.offsetWidth / 2);
            const distance = Math.abs(cardCenter - marqueeCenter);
            
            if (distance < minDistance) {
                minDistance = distance;
                closestCard = card;
            }
        });
        
        cards.forEach(card => {
            if (card === closestCard) {
                card.classList.add('center-focus');
            } else {
                card.classList.remove('center-focus');
            }
        });
    };

    // Calculate center offset for target index
    const centerCard = (index) => {
        const card = cards[index];
        if (!card) return 0;
        return card.offsetLeft - (marquee.offsetWidth / 2) + (card.offsetWidth / 2);
    };

    // Initialize position: center the active card in the second set
    const firstTarget = centerCard(totalItems);
    marquee.scrollLeft = firstTarget;
    setTimeout(updateFocus, 100);

    // Bind scroll listener for real-time focus scaling
    marquee.addEventListener('scroll', updateFocus);

    // Autoplay logic
    const nextSlide = () => {
        if (isTransitioning) return;
        currentIndex++;
        
        const targetLeft = centerCard(currentIndex);
        marquee.scrollTo({
            left: targetLeft,
            behavior: 'smooth'
        });
        
        // Loop wrapping logic: wrap from the end of the second set back to the start of the second set
        if (currentIndex >= 2 * totalItems) {
            isTransitioning = true;
            // Wait for smooth scroll completion (600ms)
            setTimeout(() => {
                marquee.scrollLeft = centerCard(totalItems);
                currentIndex = totalItems;
                isTransitioning = false;
                updateFocus();
            }, 600);
        }
    };

    let intervalId = setInterval(nextSlide, 2500); // Step every 2.5 seconds

    // Pause on hover
    marquee.addEventListener('mouseenter', () => {
        clearInterval(intervalId);
    });

    marquee.addEventListener('mouseleave', () => {
        intervalId = setInterval(nextSlide, 2500);
    });

    // Handle window resize dynamically to preserve centering offsets
    window.addEventListener('resize', () => {
        marquee.scrollLeft = centerCard(currentIndex);
        updateFocus();
    });
}

/**
 * Premium Custom Alert Modal
 */
function showCoutureAlert(title, message, buttonText = 'OK') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'couture-modal-overlay';
        
        const modal = document.createElement('div');
        modal.className = 'couture-modal-card';
        
        modal.innerHTML = `
            <div class="couture-modal-header">
                <h3>${title}</h3>
            </div>
            <div class="couture-modal-body">
                <p>${message}</p>
            </div>
            <div class="couture-modal-footer" style="justify-content: center;">
                <button class="btn btn-solid ok-btn" style="min-width: 120px; padding: 0.7rem 1.5rem;">${buttonText}</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            overlay.classList.add('active');
            modal.classList.add('active');
        }, 10);
        
        const cleanup = () => {
            overlay.classList.remove('active');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => {
                overlay.remove();
            }, 400);
            resolve();
        };
        
        modal.querySelector('.ok-btn').addEventListener('click', cleanup);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cleanup();
        });
    });
}

/**
 * Premium Custom Confirmation Modal
 */
function showCoutureConfirm(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'couture-modal-overlay';
        
        const modal = document.createElement('div');
        modal.className = 'couture-modal-card';
        
        modal.innerHTML = `
            <div class="couture-modal-header">
                <h3>${title}</h3>
            </div>
            <div class="couture-modal-body">
                <p>${message}</p>
            </div>
            <div class="couture-modal-footer">
                <button class="btn btn-outline cancel-btn" style="padding: 0.7rem 1.5rem;">${cancelText}</button>
                <button class="btn btn-solid confirm-btn" style="padding: 0.7rem 1.5rem;">${confirmText}</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            overlay.classList.add('active');
            modal.classList.add('active');
        }, 10);
        
        const cleanup = (result) => {
            overlay.classList.remove('active');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => {
                overlay.remove();
            }, 400);
            resolve(result);
        };
        
        modal.querySelector('.cancel-btn').addEventListener('click', () => cleanup(false));
        modal.querySelector('.confirm-btn').addEventListener('click', () => cleanup(true));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cleanup(false);
        });
    });
}

// Global form submit interceptor for custom confirmations
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.classList.contains('confirm-action-form')) {
        if (form.dataset.confirmed === 'true') {
            return; // let form submit naturally
        }
        
        e.preventDefault();
        const title = form.getAttribute('data-confirm-title') || 'Confirmation Required';
        const msg = form.getAttribute('data-confirm-msg') || 'Are you sure you want to proceed?';
        const confirmBtn = form.getAttribute('data-confirm-btn') || 'Confirm';
        const cancelBtn = form.getAttribute('data-cancel-btn') || 'Cancel';
        
        showCoutureConfirm(title, msg, confirmBtn, cancelBtn).then(confirmed => {
            if (confirmed) {
                form.dataset.confirmed = 'true';
                // Trigger form submit programmatically
                form.submit();
            }
        });
    }
});

