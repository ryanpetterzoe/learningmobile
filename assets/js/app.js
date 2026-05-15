/**
 * SimpleEdu LMS - Mobile-First JavaScript
 * Modern interactions with dark/light theme support
 */

document.addEventListener('DOMContentLoaded', function() {
    // === Side Panel (Slide Menu) ===
    const menuToggle = document.getElementById('menuToggle');
    const sidePanel = document.getElementById('sidePanel');
    const sidePanelClose = document.getElementById('sidePanelClose');
    const sidePanelOverlay = document.getElementById('sidePanelOverlay');

    function openSidePanel() {
        if (sidePanel) sidePanel.classList.add('open');
        if (sidePanelOverlay) sidePanelOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidePanel() {
        if (sidePanel) sidePanel.classList.remove('open');
        if (sidePanelOverlay) sidePanelOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (menuToggle) menuToggle.addEventListener('click', openSidePanel);
    if (sidePanelClose) sidePanelClose.addEventListener('click', closeSidePanel);
    if (sidePanelOverlay) sidePanelOverlay.addEventListener('click', closeSidePanel);

    // Close side panel on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidePanel();
    });

    // === Theme Toggle ===
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);

            // Save preference
            localStorage.setItem('theme', next);

            // Update icon with animation
            const icon = themeToggle.querySelector('i');
            themeToggle.style.transform = 'scale(0.8) rotate(180deg)';
            setTimeout(() => {
                icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                themeToggle.style.transform = 'scale(1) rotate(0)';
            }, 150);

            // Update meta theme color
            const metaTheme = document.querySelector('meta[name="theme-color"]');
            if (metaTheme) {
                metaTheme.content = next === 'dark' ? '#1e293b' : '#ffffff';
            }

            // Save to server
            fetch(getBaseUrl() + '/api/theme', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: next })
            }).catch(() => {});
        });

        // Apply saved theme on load
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            const icon = themeToggle.querySelector('i');
            if (icon) icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // === Notification Panel ===
    window.toggleNotifPanel = function() {
        const panel = document.getElementById('notifDropdown');
        if (panel) panel.classList.toggle('show');
    };

    // Close notification panel when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.notif-wrapper');
        const panel = document.getElementById('notifDropdown');
        if (wrapper && panel && !wrapper.contains(e.target)) {
            panel.classList.remove('show');
        }
    });

    // === Auto-dismiss alerts ===
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    });

    // === Confirm dialogs ===
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // === File upload preview ===
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function() {
            const previewEl = document.getElementById(this.dataset.preview);
            if (previewEl && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewEl.src = e.target.result;
                    previewEl.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // === Countdown timer for deadlines ===
    document.querySelectorAll('[data-countdown]').forEach(el => {
        const deadline = new Date(el.dataset.countdown).getTime();

        const updateCountdown = () => {
            const now = new Date().getTime();
            const diff = deadline - now;

            if (diff <= 0) {
                el.innerHTML = '<span class="badge badge-danger">Expired</span>';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            if (days > 0) {
                el.textContent = `${days}h ${hours}j`;
                el.style.color = '';
            } else if (hours > 0) {
                el.textContent = `${hours}j ${minutes}m`;
                el.style.color = 'var(--warning)';
            } else {
                el.textContent = `${minutes}m lagi`;
                el.style.color = 'var(--danger)';
            }
        };

        updateCountdown();
        setInterval(updateCountdown, 60000);
    });

    // === Smooth page transitions ===
    document.querySelectorAll('.app-main > *').forEach((el, i) => {
        el.style.animation = `slideUp 0.4s ease ${i * 0.03}s both`;
    });

    // === Pull to refresh feel (haptic on scroll top) ===
    let lastScrollTop = 0;
    const appMain = document.querySelector('.app-main');
    if (appMain) {
        appMain.addEventListener('scroll', () => {
            const st = appMain.scrollTop;
            lastScrollTop = st <= 0 ? 0 : st;
        });
    }
});

// === Helper: Get base URL ===
function getBaseUrl() {
    const scripts = document.querySelectorAll('script[src]');
    for (const script of scripts) {
        const src = script.getAttribute('src');
        if (src.includes('/assets/js/app.js')) {
            return src.replace('/assets/js/app.js', '');
        }
    }
    return window.location.origin;
}

// === Modal helpers ===
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// === Toast notification ===
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = 'position:fixed;top:16px;left:16px;right:16px;z-index:99999;max-width:400px;margin:0 auto;box-shadow:0 8px 24px rgba(0,0,0,0.15);';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// === Drag & Drop file upload helper ===
function initDragDrop(dropZoneId, inputId, previewId) {
    const dropZone = document.getElementById(dropZoneId);
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!dropZone || !input) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, (e) => { e.preventDefault(); e.stopPropagation(); });
    });

    ['dragenter', 'dragover'].forEach(event => {
        dropZone.addEventListener(event, () => dropZone.classList.add('drag-over'));
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, () => dropZone.classList.remove('drag-over'));
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        input.files = files;
        if (preview && files[0]) updateFilePreview(files[0], preview);
        input.dispatchEvent(new Event('change'));
    });

    dropZone.addEventListener('click', () => input.click());
}

function updateFilePreview(file, previewEl) {
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewEl.innerHTML = `<img src="${e.target.result}" style="max-width:100%;max-height:200px;border-radius:12px;">`;
        };
        reader.readAsDataURL(file);
    } else {
        const icon = getFileIcon(file.name);
        previewEl.innerHTML = `<div style="display:flex;align-items:center;gap:10px;padding:10px;"><i class="${icon}" style="font-size:24px;"></i><span>${file.name}</span></div>`;
    }
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fas fa-file-pdf', 'doc': 'fas fa-file-word', 'docx': 'fas fa-file-word',
        'xls': 'fas fa-file-excel', 'xlsx': 'fas fa-file-excel',
        'ppt': 'fas fa-file-powerpoint', 'pptx': 'fas fa-file-powerpoint',
        'jpg': 'fas fa-file-image', 'jpeg': 'fas fa-file-image', 'png': 'fas fa-file-image',
        'zip': 'fas fa-file-archive', 'rar': 'fas fa-file-archive',
        'mp4': 'fas fa-file-video', 'mp3': 'fas fa-file-audio',
    };
    return icons[ext] || 'fas fa-file';
}
