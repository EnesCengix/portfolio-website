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
            sectionObserver.unobserve(entry.target); // Sadece bir kere tetikle
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
        setTimeout(typeEffect, 60); // Biraz daha hızlı, modern his
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

    // Scroll to Top Button
    const scrollBtn = document.getElementById('scrollTopBtn');
    scrollBtn.classList.toggle('show', window.scrollY > 400);
});

function scrollToSection(id) {
    const el = document.getElementById(id);
    const navHeight = document.querySelector('nav').offsetHeight;
    window.scrollTo({ top: el.offsetTop - navHeight, behavior: 'smooth' });
}

// ─── PROJECTS & API FALLBACK ──────────────────────────────
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
        title: 'Finger Math Project (Computer Vision)',
        description: 'Developed an interactive application using Python and Mediapipe to calculate math operations purely through hand gestures.',
        github_link: 'https://github.com/EnesCengix'
    },
    {
        title: 'Hospital Appointment System',
        description: 'A robust hospital appointment management system featuring automated email notifications for booking confirmations.',
        github_link: 'https://github.com/EnesCengix'
    }
];

function renderProjects(projects) {
    const container = document.getElementById('projects-container');
    container.innerHTML = projects.map(p => `
        <div class="project-card glass-panel">
            <h3>${escapeHtml(p.title)}</h3>
            <p>${escapeHtml(p.description)}</p>
            <a href="${p.github_link || '#'}" target="_blank" class="project-link">
                View Repository <span>→</span>
            </a>
        </div>
    `).join('');
}

async function fetchProjects() {
    try {
        const res = await fetch('api/projects.php');
        const data = await res.json();
        renderProjects(data.success && data.projects.length ? data.projects : STATIC_PROJECTS);
    } catch {
        renderProjects(STATIC_PROJECTS);
    }
}
fetchProjects();

// ─── AI ASSISTANT ─────────────────────────────────────────
function askAI() {
    const input = document.getElementById('aiInput').value.toLowerCase().trim();
    const response = document.getElementById('aiResponse');
    
    if (!input) { response.innerHTML = '<i>Please type a question first ✨</i>'; return; }
    
    let reply = '';
    if (input.includes('who') || input.includes('enes')) {
        reply = 'Enes is a senior Computer Engineering student at Haliç University, specializing in software engineering, backend systems, and computer vision.';
    } else if (input.includes('skill') || input.includes('tech')) {
        reply = 'He is proficient in Python, Java, C/C++, and PHP. He also works with tools like MySQL, Git, FastAPI, and Mediapipe.';
    } else if (input.includes('project')) {
        reply = 'Some of his notable works include a Facial Emotion Recognition system, the Finger Math Project, and OMNeT++ Network Simulations.';
    } else if (input.includes('experience') || input.includes('intern')) {
        reply = 'He completed a Software Engineering internship at Golden Gateway Software Company in 2024, focusing on Python and Java databases.';
    } else {
        reply = "I'm a simple AI! Try asking about Enes's <b>skills</b>, <b>projects</b>, or <b>education</b>.";
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