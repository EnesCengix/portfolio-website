/* ==========================================================
   script.js  —  Portfolio by Enes Cengiz
   ========================================================== */

// ─── THEME MANAGEMENT ─────────────────────────────────────
const setCookie = (name, value, days) => {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${value}; expires=${expires}; path=/`;
};

const getCookie = (name) => {
    return document.cookie.split('; ').reduce((acc, part) => {
        const [k, v] = part.split('=');
        return k === name ? v : acc;
    }, null);
};

function toggleTheme() {
    const isLight = document.body.classList.toggle('light');
    setCookie('theme', isLight ? 'light' : 'dark', 365);
}

(function applyTheme() {
    if (getCookie('theme') === 'light') {
        document.body.classList.add('light');
        document.getElementById('theme-toggle').checked = true;
    }
})();

// ─── MOBILE MENU ──────────────────────────────────────────
function toggleMenu() {
    document.getElementById('nav-links').classList.toggle('active');
}
document.querySelectorAll('nav a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('nav-links').classList.remove('active');
    });
});

// ─── MODERN SCROLL REVEAL (IntersectionObserver) ─────────
const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            sectionObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.section').forEach(sec => {
    sectionObserver.observe(sec);
});

// ─── TYPING EFFECT ────────────────────────────────────────
const text = 'Computer Engineering Student & Software Developer';
let i = 0;
function typeEffect() {
    if (i < text.length) {
        document.getElementById('typing').textContent += text.charAt(i);
        i++;
        setTimeout(typeEffect, 60);
    }
}
window.addEventListener('load', typeEffect);

// ─── SCROLL SPY ───────────────────────────────────────────
const sections = document.querySelectorAll('.section');
const navLinks = document.querySelectorAll('#nav-links a');

window.addEventListener('scroll', () => {
    let currentId = '';
    sections.forEach(section => {
        if (window.scrollY >= section.offsetTop - 200) {
            currentId = section.getAttribute('id');
        }
    });
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentId}`) {
            link.classList.add('active');
        }
    });

    const scrollBtn = document.getElementById('scrollTopBtn');
    scrollBtn.classList.toggle('show', window.scrollY > 400);
});

function scrollToSection(id) {
    const el = document.getElementById(id);
    const navHeight = document.querySelector('nav').offsetHeight;
    window.scrollTo({ top: el.offsetTop - navHeight, behavior: 'smooth' });
}

// ─── TOAST NOTIFICATION  ─────────────
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    setTimeout(() => { toast.className = 'toast'; }, 4000);
}

// ─── PROJECTS & API FALLBACK ──
const escapeHtml = (str) => String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"\'":'&#39;'})[m]);

const STATIC_PROJECTS = [
    {
        title: 'Facial Emotion Recognition & Support',
        description: 'A computer vision project that detects human emotions and displays personalized, supportive messages based on the analyzed mood.',
        github_link: 'https://github.com/EnesCengix'
    },
    {
        title: 'OMNeT++ Network Simulation',
        description: 'A network simulation built to deeply visualize and analyze data circulation and routing protocols within a complex network environment.',
        github_link: 'https://github.com/EnesCengix'
    },
    {
        title: 'Comprehensive Hospital Appointment System',
        description: 'A robust hospital appointment management system featuring automated email notifications for booking confirmations and patient reminders.',
        github_link: 'https://github.com/EnesCengix'
    }
];

function renderProjects(projects) {
    const container = document.getElementById('projects-container');
    if (!projects || projects.length === 0) {
        container.innerHTML = '<p style="grid-column:1/-1;color:#94a3b8;text-align:center;">No projects found.</p>';
        return;
    }
    container.innerHTML = projects.map(p => `
        <div class="project-card glass-panel">
            <h3>${escapeHtml(p.title)}</h3>
            <p>${escapeHtml(p.description)}</p>
            ${p.github_link && p.github_link !== '#'
                ? `<a href="${escapeHtml(p.github_link)}" target="_blank" rel="noopener" class="project-link">View Repository <span>→</span></a>`
                : `<a href="#" class="project-link" style="opacity:.5;cursor:default;">GitHub (soon) <span>→</span></a>`
            }
        </div>
    `).join('');
}

async function fetchProjects() {
    const container = document.getElementById('projects-container');
    container.innerHTML = '<div class="spinner"></div>';
    try {
        const res = await fetch('api/projects.php');
        if (!res.ok) throw new Error('Server error');
        const data = await res.json();
        if (data.success && data.projects.length > 0) {
            renderProjects(data.projects);
        } else {
            renderProjects(STATIC_PROJECTS);
        }
    } catch {
        renderProjects(STATIC_PROJECTS);
    }
}
fetchProjects();

// ─── FORM VALIDATION & AJAX SUBMIT ──
function setError(id, message) {
    const inp = document.getElementById('inp-' + id);
    const err = document.getElementById('err-' + id);
    inp.classList.add('invalid');
    err.textContent = message;
}

function clearError(id) {
    const inp = document.getElementById('inp-' + id);
    const err = document.getElementById('err-' + id);
    inp.classList.remove('invalid');
    err.textContent = '';
}

function validateContactForm() {
    let isValid = true;
    ['name', 'email', 'message'].forEach(f => clearError(f));

    const name    = document.getElementById('inp-name').value.trim();
    const email   = document.getElementById('inp-email').value.trim();
    const message = document.getElementById('inp-message').value.trim();

    if (!name || name.length < 2) {
        setError('name', 'Name must be at least 2 characters.');
        isValid = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        setError('email', 'Email address is required.');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        setError('email', 'Please enter a valid email address.');
        isValid = false;
    }

    if (!message || message.length < 10) {
        setError('message', 'Message must be at least 10 characters.');
        isValid = false;
    }

    return isValid;
}

document.getElementById('contact-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!validateContactForm()) return;

    const btn = document.getElementById('submit-btn');
    btn.textContent = 'Sending...';
    btn.disabled = true;

    try {
        const res = await fetch('contact.php', {
            method: 'POST',
            body: new FormData(this)
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Message sent successfully! 🎉', 'success');
            this.reset();
        } else {
            showToast(data.message || 'Something went wrong.', 'error');
        }
    } catch {
        showToast('Could not connect to server. Please try again.', 'error');
    } finally {
        btn.textContent = 'Send Message';
        btn.disabled = false;
    }
});

// Clear error styling on input
['name', 'email', 'message'].forEach(id => {
    const el = document.getElementById('inp-' + id);
    el.addEventListener('input', () => clearError(id));
});

// ─── AI ASSISTANT ─────────────────────────────────────────
function askAI() {
    const input = document.getElementById('aiInput').value.toLowerCase().trim();
    const response = document.getElementById('aiResponse');
    
    if (!input) { response.innerHTML = '<i>Please type a question first ✨</i>'; return; }
    
    let reply = '';
    if (input.includes('who') || input.includes('enes')) {
        reply = 'Enes is a senior Computer Engineering student at Haliç University, specializing in software engineering, backend systems, and computer vision.';
    } else if (input.includes('skill') || input.includes('tech')) {
        reply = 'His main technical skills include Python, Java, C/C++, PHP, MySQL, Git, FastAPI, and web development.';
    } else if (input.includes('project')) {
        reply = 'Some of his notable works include a Facial Emotion Recognition system, the Finger Math Project, and OMNeT++ Network Simulations.';
    } else if (input.includes('experience') || input.includes('intern')) {
        reply = 'He completed a Software Engineering internship at Golden Gateway Software Company in 2024, focusing on Python and Java ecosystems.';
    } else if (input.includes('education')) {
        reply = 'He is currently studying Computer Engineering (English) at Haliç University.';
    } else {
        reply = "I'm a simple AI! Try asking about Enes's skills, projects, experience, or education.";
    }
    
    // Typing effect for AI
    response.innerHTML = '';
    let c = 0;
    const interval = setInterval(() => {
        response.innerHTML += reply.charAt(c);
        c++;
        if(c >= reply.length) clearInterval(interval);
    }, 20);
}

document.getElementById('aiInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') askAI();
});