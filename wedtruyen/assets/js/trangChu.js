// Banner Slider
let currentSlide = 0;
const slides = document.querySelectorAll('.banner-slide');
const indicators = document.querySelectorAll('.indicator');

function showSlide(index) {
    if (slides.length === 0) return;
    
    slides[currentSlide].classList.remove('active');
    indicators[currentSlide].classList.remove('active');
    
    currentSlide = index;
    
    slides[currentSlide].classList.add('active');
    indicators[currentSlide].classList.add('active');
}

function nextSlide() {
    if (slides.length === 0) return;
    const next = (currentSlide + 1) % slides.length;
    showSlide(next);
}

// Auto slide every 5 seconds
if (slides.length > 1) {
    setInterval(nextSlide, 5000);
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Focus search
function focusSearch() {
    const searchInput = document.querySelector('#search-truyen');
    if (searchInput) {
        searchInput.focus();
        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Show/hide FAB based on scroll position
document.addEventListener('DOMContentLoaded', function() {
    const fabContainer = document.getElementById('fabContainer');
    
    if (fabContainer) {
        // Initially hide if at top
        if (window.scrollY <= 300) {
            fabContainer.classList.add('hidden');
        }
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                fabContainer.classList.remove('hidden');
            } else {
                fabContainer.classList.add('hidden');
            }
        });
    }
});

let currentPage = 1;
const storiesPerPage = 24;

async function loadMoreStories() {
    const button = event.target.closest('.load-more-btn');
    if (!button) return;

    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Đang tải...</span>';
    button.disabled = true;

    currentPage++;
    try {
        const res = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/api/listStories.php?page=${currentPage}`);
        const data = await res.json();
        if (data.success && data.stories.length > 0) {
            const grid = document.querySelector('.story-grid');
            data.stories.forEach(story => {
                const a = document.createElement('a');
                a.href = `/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=${story.id_truyen}`;
                a.className = 'story-card';
                a.innerHTML = `
                    <div class="story-thumb">
                        <img src="/Wed_Doc_Truyen/${story.anh_bia || 'assets/images/default-cover.jpg'}" alt="${story.ten_truyen}">
                        <div class="badge rating-badge">⭐ ${story.danh_gia ? Number(story.danh_gia).toFixed(1) : '0'}</div>
                        ${story.trang_thai ? `<div class="badge status-badge">${story.trang_thai}</div>` : ''}
                        <div class="chapter-counter">${story.max_chapter}</div>
                    </div>
                    <div class="story-info">
                        <h3 class="story-title">${story.ten_truyen}</h3>
                        <div class="story-meta">
                            <span class="story-views"><i class="fas fa-eye"></i> ${Number(story.luot_xem).toLocaleString()} lượt xem</span>
                        </div>
                    </div>
                `;
                grid.appendChild(a);
            });
            button.innerHTML = '<i class="fas fa-plus"></i> <span>Xem thêm truyện</span>';
            button.disabled = false;
        } else {
            button.style.display = 'none';
        }
    } catch (e) {
        alert('Có lỗi xảy ra khi tải thêm truyện!');
        button.innerHTML = '<i class="fas fa-plus"></i> <span>Xem thêm truyện</span>';
        button.disabled = false;
    }
}

// Filter chips interaction
document.querySelectorAll('.filter-chip').forEach(chip => {
    chip.addEventListener('click', function(e) {
        // Let the link work normally, just add visual feedback
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
    });
});

// Story card hover effects
document.querySelectorAll('.story-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Quick slider touch/mouse scroll
const quickSlider = document.querySelector('.quick-slider');
if (quickSlider) {
    let isDown = false;
    let startX;
    let scrollLeft;

    quickSlider.addEventListener('mousedown', (e) => {
        isDown = true;
        quickSlider.style.cursor = 'grabbing';
        startX = e.pageX - quickSlider.offsetLeft;
        scrollLeft = quickSlider.scrollLeft;
    });

    quickSlider.addEventListener('mouseleave', () => {
        isDown = false;
        quickSlider.style.cursor = 'grab';
    });

    quickSlider.addEventListener('mouseup', () => {
        isDown = false;
        quickSlider.style.cursor = 'grab';
    });

    quickSlider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - quickSlider.offsetLeft;
        const walk = (x - startX) * 2;
        quickSlider.scrollLeft = scrollLeft - walk;
    });
}

// Animate story cards on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Initially hide cards for animation
document.querySelectorAll('.story-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
});

// Success message auto-hide
document.addEventListener('DOMContentLoaded', function() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage && window.location.search.includes('success=1')) {
        successMessage.style.display = 'flex';
        setTimeout(() => {
            successMessage.style.opacity = '0';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 300);
        }, 3000);
    }
});

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const heroSection = document.querySelector('.hero-section');
    const rate = scrolled * -0.3;
    
    if (heroSection && scrolled < window.innerHeight) {
        heroSection.style.transform = `translateY(${rate}px)`;
    }
});

// Dynamic stats counter animation
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        element.textContent = Math.floor(start).toLocaleString('vi-VN');
        
        if (start >= target) {
            element.textContent = target.toLocaleString('vi-VN');
            clearInterval(timer);
        }
    }, 16);
}

// Animate stats when they come into view
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            document.querySelectorAll('.stat-number').forEach((stat, index) => {
                const text = stat.textContent.replace(/[,\.]/g, '');
                const number = parseInt(text) || 0;
                if (number > 0) {
                    stat.textContent = '0';
                    animateCounter(stat, number);
                }
            });
            statsObserver.unobserve(entry.target);
        }
    });
});

const statsBar = document.querySelector('.stats-bar');
if (statsBar) {
    statsObserver.observe(statsBar);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Press 'S' to focus search
    if (e.key === 's' || e.key === 'S') {
        if (!e.ctrlKey && !e.altKey && !e.metaKey) {
            const activeElement = document.activeElement;
            if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                focusSearch();
            }
        }
    }
    
    // Press 'T' to scroll to top
    if (e.key === 't' || e.key === 'T') {
        if (!e.ctrlKey && !e.altKey && !e.metaKey) {
            const activeElement = document.activeElement;
            if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                scrollToTop();
            }
        }
    }
});

// Touch gestures for mobile
let touchStartY = 0;
let touchEndY = 0;

document.addEventListener('touchstart', function(e) {
    touchStartY = e.changedTouches[0].screenY;
});

document.addEventListener('touchend', function(e) {
    touchEndY = e.changedTouches[0].screenY;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 100;
    const diff = touchStartY - touchEndY;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe up - could trigger some action
            console.log('Swiped up');
        } else {
            // Swipe down - could trigger refresh or other action
            console.log('Swiped down');
        }
    }
}

// Performance optimization - Lazy load images
document.addEventListener('DOMContentLoaded', function() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});