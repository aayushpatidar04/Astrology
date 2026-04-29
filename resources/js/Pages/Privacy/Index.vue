<template>
  <Head title="Privacy Policy" />
  <div class="privacy-page">
    <!-- Starfield background -->
    <div class="stars" aria-hidden="true">
      <span v-for="n in 60" :key="n" class="star" :style="starStyle(n)" />
    </div>

    <div class="container">
      <!-- Header -->
      <header class="page-header">
        <div class="emblem">✦</div>
        <h1 class="title">My Astro Sathi</h1>
        <p class="subtitle">Privacy Policy</p>
        <p class="effective-date">Effective Date: 26 April 2024</p>
      </header>

      <p class="intro">
        Thank you for using My Astro Sathi. This Privacy Policy explains how we collect, use,
        share, and protect your personal information. By using our services, you agree to this policy.
      </p>

      <!-- Policy Sections -->
      <section v-for="section in sections" :key="section.title" class="policy-section">
        <div class="section-icon">{{ section.icon }}</div>
        <div class="section-body">
          <h2 class="section-title">{{ section.title }}</h2>
          <p class="section-text" v-html="section.text" />
        </div>
      </section>

      <!-- Account Deletion -->
      <section class="policy-section highlight-section">
        <div class="section-icon">🗑️</div>
        <div class="section-body">
          <h2 class="section-title">Account Deletion Request</h2>
          <p class="section-text">
            Fill the Google Form to request account deletion.
            <a
              href="https://forms.gle/EFUoUZaqxVYUodEz7"
              target="_blank"
              rel="noopener noreferrer"
              class="link"
            >
              Submit Deletion Request ↗
            </a>
          </p>
        </div>
      </section>

      <!-- Contact -->
      <section class="policy-section">
        <div class="section-icon">✉️</div>
        <div class="section-body">
          <h2 class="section-title">Contact Us</h2>
          <p class="section-text">
            If you have any questions or concerns about this Privacy Policy or our practices, please
            contact us at
            <a href="mailto:support@myastrosathi.com" class="link">support@myastrosathi.com</a>.
          </p>
        </div>
      </section>

      <footer class="page-footer">
        <span>✦ My Astro Sathi — Written in the Stars ✦</span>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';

const sections = [
  {
    icon: '📋',
    title: 'Information We Collect',
    text: 'We collect personal information such as your name, email address, phone number, address, date of birth, gender, and other necessary details when you register with My Astro Sathi. We may also collect information about your interactions with our services, such as consultations and payments.',
  },
  {
    icon: '🔍',
    title: 'How We Use Your Information',
    text: 'We use your information to provide and improve our services, personalize your experience, communicate with you, and send you relevant content and promotions. We do not sell or rent your information to third parties without your consent.',
  },
  {
    icon: '🔒',
    title: 'Data Security',
    text: 'We take appropriate security measures to protect your information from unauthorized access, alteration, disclosure, or destruction.',
  },
  {
    icon: '🍪',
    title: 'Cookies and Similar Technologies',
    text: 'We use cookies and similar technologies to enhance your experience, analyze trends, administer the website, track users\' movements around the site, and gather demographic information.',
  },
  {
    icon: '🔗',
    title: 'Third-Party Links',
    text: 'Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of these websites.',
  },
  {
    icon: '⚙️',
    title: 'Your Choices',
    text: 'You can manage your communication preferences and update your information by contacting us. You also have the right to access, correct, or delete your personal information.',
  },
  {
    icon: '✅',
    title: 'Privacy Policy Compliance',
    text: 'We acknowledge the importance of privacy policies and have updated ours to comply with Google Play policies. Our privacy policy now includes details about how we handle users\' phone numbers and other personal information.',
  },
  {
    icon: '📝',
    title: 'Changes to This Policy',
    text: 'We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page.',
  },
]

function starStyle(n) {
  const size = Math.random() * 2 + 1
  return {
    width: size + 'px',
    height: size + 'px',
    top: ((n * 137.5) % 100) + '%',
    left: ((n * 97.3) % 100) + '%',
    animationDelay: (n * 0.3) % 5 + 's',
    animationDuration: 3 + (n % 4) + 's',
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Lato:wght@300;400&display=swap');

/* ─── Base ─────────────────────────────────────── */
.privacy-page {
  min-height: 100vh;
  background: #0a0614;
  color: #e8dff5;
  font-family: 'Lato', sans-serif;
  font-weight: 300;
  position: relative;
  overflow-x: hidden;
}

/* ─── Starfield ─────────────────────────────────── */
.stars {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}
.star {
  position: absolute;
  border-radius: 50%;
  background: #fff;
  opacity: 0;
  animation: twinkle linear infinite;
}
@keyframes twinkle {
  0%, 100% { opacity: 0; }
  50%       { opacity: 0.7; }
}

/* ─── Layout ────────────────────────────────────── */
.container {
  position: relative;
  z-index: 1;
  max-width: 780px;
  margin: 0 auto;
  padding: 60px 24px 80px;
}

/* ─── Header ────────────────────────────────────── */
.page-header {
  text-align: center;
  margin-bottom: 48px;
}
.emblem {
  font-size: 2.2rem;
  color: #c9a84c;
  margin-bottom: 12px;
  display: block;
  animation: pulse 3s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1);   }
  50%       { opacity: 0.7; transform: scale(1.15); }
}
.title {
  font-family: 'Cinzel', serif;
  font-size: clamp(1.8rem, 5vw, 2.8rem);
  font-weight: 700;
  color: #f0e6c8;
  letter-spacing: 0.08em;
  margin: 0 0 6px;
}
.subtitle {
  font-family: 'Cinzel', serif;
  font-size: 1rem;
  color: #c9a84c;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin: 0 0 10px;
}
.effective-date {
  font-size: 0.82rem;
  color: #9480b0;
  margin: 0;
  letter-spacing: 0.06em;
}

/* ─── Intro ─────────────────────────────────────── */
.intro {
  text-align: center;
  font-size: 0.97rem;
  line-height: 1.8;
  color: #c5b8d8;
  margin-bottom: 48px;
  padding: 20px 24px;
  border: 1px solid rgba(201, 168, 76, 0.18);
  border-radius: 12px;
  background: rgba(201, 168, 76, 0.04);
}

/* ─── Sections ──────────────────────────────────── */
.policy-section {
  display: flex;
  gap: 20px;
  align-items: flex-start;
  padding: 28px 0;
  border-bottom: 1px solid rgba(148, 128, 176, 0.15);
  animation: fadeUp 0.5s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.policy-section:last-of-type {
  border-bottom: none;
}
.section-icon {
  font-size: 1.4rem;
  min-width: 40px;
  text-align: center;
  padding-top: 2px;
  filter: drop-shadow(0 0 6px rgba(201, 168, 76, 0.4));
}
.section-body {
  flex: 1;
}
.section-title {
  font-family: 'Cinzel', serif;
  font-size: 1rem;
  font-weight: 600;
  color: #f0e6c8;
  letter-spacing: 0.06em;
  margin: 0 0 10px;
}
.section-text {
  font-size: 0.93rem;
  line-height: 1.8;
  color: #b8aac8;
  margin: 0;
}

/* ─── Highlight section ─────────────────────────── */
.highlight-section {
  background: rgba(201, 168, 76, 0.06);
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 12px;
  padding: 28px 24px;
  margin: 8px 0;
}

/* ─── Links ─────────────────────────────────────── */
.link {
  color: #c9a84c;
  text-decoration: none;
  border-bottom: 1px solid rgba(201, 168, 76, 0.4);
  transition: color 0.2s, border-color 0.2s;
  margin-left: 4px;
}
.link:hover {
  color: #f0d98a;
  border-color: #f0d98a;
}

/* ─── Footer ────────────────────────────────────── */
.page-footer {
  text-align: center;
  margin-top: 56px;
  font-family: 'Cinzel', serif;
  font-size: 0.78rem;
  color: #6a5880;
  letter-spacing: 0.12em;
}
</style>