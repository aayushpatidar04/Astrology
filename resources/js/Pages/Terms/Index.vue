<template>
    <Head title="Terms & Conditions" />

    <div class="terms-page">
        <!-- Starfield background -->
        <div class="stars" aria-hidden="true">
            <span v-for="n in 60" :key="n" class="star" :style="starStyle(n)" />
        </div>

        <!-- Constellation lines decoration -->
        <svg class="constellation" aria-hidden="true" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
            <line x1="50" y1="80" x2="180" y2="140" stroke="rgba(201,168,76,0.08)" stroke-width="1" />
            <line x1="180" y1="140" x2="300" y2="90" stroke="rgba(201,168,76,0.08)" stroke-width="1" />
            <line x1="300" y1="90" x2="350" y2="200" stroke="rgba(201,168,76,0.08)" stroke-width="1" />
            <circle cx="50" cy="80" r="2" fill="rgba(201,168,76,0.3)" />
            <circle cx="180" cy="140" r="2" fill="rgba(201,168,76,0.3)" />
            <circle cx="300" cy="90" r="2" fill="rgba(201,168,76,0.3)" />
            <circle cx="350" cy="200" r="2" fill="rgba(201,168,76,0.3)" />
        </svg>

        <div class="container">
            <!-- Header -->
            <header class="page-header">
                <div class="emblem">✦</div>
                <h1 class="title">My Astro Sathi</h1>
                <p class="subtitle">Terms &amp; Conditions</p>
                <div class="header-divider">
                    <span class="divider-line" />
                    <span class="divider-star">★</span>
                    <span class="divider-line" />
                </div>
            </header>

            <!-- Intro -->
            <p class="intro">
                Welcome to My Astro Sathi! These terms outline the Services offered through
                <strong>MyAstroSathi.com</strong> and its mobile applications. By using our Website,
                you agree to abide by these Terms. We reserve the right to modify or discontinue
                Services without prior notice.
            </p>

            <!-- Sections -->
            <section v-for="(section, index) in sections" :key="section.title" class="terms-section"
                :style="{ animationDelay: index * 0.06 + 's' }">
                <div class="section-header">
                    <span class="section-number">{{ String(index + 1).padStart(2, '0') }}</span>
                    <span class="section-icon">{{ section.icon }}</span>
                    <h2 class="section-title">{{ section.title }}</h2>
                </div>
                <div class="section-body">
                    <p v-for="(para, pi) in section.paragraphs" :key="pi" class="section-text" v-html="para" />
                    <ul v-if="section.bullets" class="terms-list">
                        <li v-for="(bullet, bi) in section.bullets" :key="bi" class="terms-list-item">
                            {{ bullet }}
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Acceptance notice -->
            <div class="acceptance-box">
                <span class="acceptance-icon">✦</span>
                <p class="acceptance-text">
                    By using the Website, Users acknowledge reading, understanding, and agreeing to
                    these Terms. Acceptance is indicated by clicking the
                    <strong>"Accepted and Agreed to"</strong> button presented during account registration.
                </p>
            </div>

            <!-- Contact -->
            <section class="contact-section">
                <div class="section-icon-lg">✉️</div>
                <div>
                    <h2 class="section-title">Contact &amp; Grievances</h2>
                    <p class="section-text">
                        All notices to My Astro Sathi must be in writing and sent via email or post.
                        Grievance officer contact details and postal address are available on the Website.
                        Complaints are investigated on a case-by-case basis.
                        <a href="mailto:support@myastrosathi.com" class="link">support@myastrosathi.com</a>
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
        title: 'Description of Services',
        paragraphs: [
            'My Astro Sathi offers astrological consultation services — including audio, video, or written reports — through its Website and Mobile Application. Services may be accessed freely or may require payment (<em>Paid Services</em>). Details of each Service and associated fees are outlined on the Website.',
            'Our offerings include astrological content, tarot card readings, palmistry, and more, categorised into Free and Paid options visible on the Website.',
        ],
    },
    {
        icon: '🪐',
        title: 'Eligibility',
        paragraphs: [
            'Our Services are available to individuals legally eligible to contract under the Indian Contract Act 1872. Minors, individuals suspended by My Astro Sathi, and those prohibited by law are ineligible to access our Services.',
        ],
    },
    {
        icon: '🔍',
        title: 'Use of User Information',
        paragraphs: [
            'By using our Services, you consent to us accessing the information you provide for the purpose of delivering and improving our Services. You may receive communication from us for newsletters, updates, and promotions via various channels.',
        ],
    },
    {
        icon: '🧾',
        title: 'Membership Registration',
        paragraphs: [
            'To access Paid Services, you must create a Registered Member account with accurate personal information. Each account is linked to a unique phone number or other login method.',
        ],
    },
    {
        icon: '🚫',
        title: 'Suspension of User Accounts',
        paragraphs: [
            'We reserve the right to suspend or restrict access for Registered Members who provide inaccurate information or engage in defamatory behaviour toward My Astro Sathi or its affiliates.',
        ],
    },
    {
        icon: '🔒',
        title: 'Account Security',
        paragraphs: [
            'Registered Members are responsible for maintaining the security of their accounts, including login information and linked payment accounts. Any unauthorised use should be reported promptly.',
            'By using our Website, you agree not to engage in any activities that disrupt or compromise the integrity of our Services.',
        ],
    },
    {
        icon: '🔮',
        title: 'Communication with Astrologers',
        paragraphs: [
            'Users may engage in consultations with astrological experts ("Astrologers") via phone calls, video calls, and texts. By accepting these Terms, you allow us to arrange consultation calls with Astrologers for telephonic consultation Services.',
            'Astrologers are prohibited from sharing personal contact details and from recommending black magic, vashikaran, or related rituals. Users must promptly report any violations.',
        ],
    },
    {
        icon: '🛡️',
        title: 'Privacy Policy',
        paragraphs: [
            'My Astro Sathi\'s privacy policy outlines how we collect, store, use, and process data from Registered Members. By accessing our Website, all information collected — including registration and payment details — is subject to our Privacy Policy available at <a href="https://www.MyAstroSathi.com" class="link" target="_blank">www.MyAstroSathi.com</a>.',
        ],
    },
    {
        icon: '⚖️',
        title: 'Breach of Terms & Account Termination',
        paragraphs: [
            'In the event of a breach, My Astro Sathi reserves the right to immediately block a Registered Member\'s account or restrict access if:',
        ],
        bullets: [
            'We are unable to verify the accuracy of information provided, or it is found to be false or misleading.',
            'The actions of a Registered Member could expose the Website or any affiliated third party to legal liability.',
            'The actions of a Registered Member interfere with our Services or violate our privacy policy.',
        ],
    },
    {
        icon: '👤',
        title: 'User Responsibility',
        paragraphs: [
            'By using the Website, you agree to use it solely for lawful purposes. You agree not to:',
        ],
        bullets: [
            'Violate any laws or regulations in force within or outside India.',
            'Engage in activities that disrupt access to the Website.',
            'Resell or commercially use the Services without written consent.',
            'Collect user information for advertisement or spam purposes.',
            'Send unsolicited emails or advertisements.',
            'Interfere with the Website\'s functionality or other users\' access.',
        ],
    },
    {
        icon: '📜',
        title: 'Representations & Warranties',
        paragraphs: [
            'All Services are offered "as is" and "as available" without express or implied warranties, unless otherwise specified. My Astro Sathi does not guarantee constant availability or error-free operation. Users warrant that they will fully disclose their current emotional, mental, or physical state to Astrologers.',
        ],
    },
    {
        icon: '⚠️',
        title: 'Limitation of Liability',
        paragraphs: [
            'To the fullest extent permitted by applicable law, My Astro Sathi does not provide warranties for the Services offered. Consultation quality may vary due to individual Astrologer knowledge, skills, and methodologies.',
            'My Astro Sathi is not a suicide prevention helpline. If you are experiencing suicidal thoughts, please seek immediate assistance from relevant authorities or medical professionals.',
            'My Astro Sathi is not a medical institution and does not guarantee the accuracy of any medical advice. Users are advised to seek independent medical advice from licensed professionals.',
            'Liability for any action or consequence will be limited to the amount paid by a Registered Member for the Services availed.',
        ],
    },
    {
        icon: '💳',
        title: 'Banking & Payments',
        paragraphs: [
            'All payments for Paid Services must be made through the My Astro Sathi Wallet linked to your account. Registered Members warrant that all banking details provided are accurate and that they are legally authorised to use them.',
        ],
    },
    {
        icon: '↩️',
        title: 'Refund & Cancellation Policy',
        paragraphs: [
            'A refund for a Paid Service may be requested if: a network issue affects the session; the Astrologer cannot communicate clearly in the specified language; response time is excessively long; or the user is dissatisfied with the consultation quality.',
            'Refund requests initiated more than two hours after payment, or for incorrect information provided by the Registered Member, will not be accepted. Refunds are processed within seven business days to the My Astro Sathi Wallet.',
            'For products: no refunds for printable products or requests after two days of delivery, except for damaged or broken items.',
        ],
    },
    {
        icon: '✏️',
        title: 'Modification of These Terms',
        paragraphs: [
            'My Astro Sathi reserves the right to modify or update these Terms without notice. It is the responsibility of Users and Registered Members to periodically visit the Website to stay informed of any changes.',
        ],
    },
    {
        icon: '🏛️',
        title: 'Governing Law & Jurisdiction',
        paragraphs: [
            'These Terms are governed by Indian laws. Any disputes shall be exclusively referred to the courts in New Delhi, India, and resolved through arbitration under the Arbitration and Conciliation Act, 1996, with proceedings conducted in English.',
        ],
    },
    {
        icon: '🤝',
        title: 'Indemnification',
        paragraphs: [
            'Users agree to indemnify and hold harmless My Astro Sathi, its affiliates, and related parties from any liability, losses, claims, or expenses arising from their use of the Website or any violation of these Terms.',
        ],
    },
    {
        icon: '©️',
        title: 'Intellectual Property',
        paragraphs: [
            'Access to the Website grants a limited licence to use the information for personal purposes only. My Astro Sathi owns all content on the Website; copying or reproducing content without consent is prohibited.',
        ],
    },
]

function starStyle(n) {
    const size = (((n * 7) % 3) + 1)
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
.terms-page {
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

    0%,
    100% {
        opacity: 0;
    }

    50% {
        opacity: 0.7;
    }
}

/* ─── Constellation SVG ─────────────────────────── */
.constellation {
    position: fixed;
    top: 0;
    right: 0;
    width: 400px;
    height: 400px;
    pointer-events: none;
    z-index: 0;
    opacity: 0.6;
}

/* ─── Layout ────────────────────────────────────── */
.container {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 24px 80px;
}

/* ─── Header ────────────────────────────────────── */
.page-header {
    text-align: center;
    margin-bottom: 44px;
}

.emblem {
    font-size: 2.2rem;
    color: #c9a84c;
    margin-bottom: 12px;
    display: block;
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.7;
        transform: scale(1.15);
    }
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
    letter-spacing: 0.22em;
    text-transform: uppercase;
    margin: 0 0 20px;
}

.header-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.divider-line {
    display: block;
    width: 80px;
    height: 1px;
    background: linear-gradient(to right, transparent, rgba(201, 168, 76, 0.5), transparent);
}

.divider-star {
    color: #c9a84c;
    font-size: 0.7rem;
    opacity: 0.8;
}

/* ─── Intro ─────────────────────────────────────── */
.intro {
    text-align: center;
    font-size: 0.96rem;
    line-height: 1.85;
    color: #c5b8d8;
    margin-bottom: 48px;
    padding: 22px 28px;
    border: 1px solid rgba(201, 168, 76, 0.18);
    border-radius: 12px;
    background: rgba(201, 168, 76, 0.04);
}

.intro strong {
    color: #f0e6c8;
    font-weight: 400;
}

/* ─── Terms Sections ────────────────────────────── */
.terms-section {
    padding: 28px 0;
    border-bottom: 1px solid rgba(148, 128, 176, 0.12);
    animation: fadeUp 0.5s ease both;
}

.terms-section:last-of-type {
    border-bottom: none;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(14px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.section-number {
    font-family: 'Cinzel', serif;
    font-size: 0.72rem;
    color: #6a5880;
    letter-spacing: 0.1em;
    min-width: 24px;
}

.section-icon {
    font-size: 1.2rem;
    filter: drop-shadow(0 0 5px rgba(201, 168, 76, 0.35));
}

.section-title {
    font-family: 'Cinzel', serif;
    font-size: 0.98rem;
    font-weight: 600;
    color: #f0e6c8;
    letter-spacing: 0.06em;
    margin: 0;
}

.section-body {
    padding-left: 36px;
}

.section-text {
    font-size: 0.92rem;
    line-height: 1.85;
    color: #b8aac8;
    margin: 0 0 10px;
}

.section-text:last-child {
    margin-bottom: 0;
}

/* ─── Bullet list ───────────────────────────────── */
.terms-list {
    list-style: none;
    padding: 0;
    margin: 10px 0 0;
}

.terms-list-item {
    font-size: 0.91rem;
    line-height: 1.8;
    color: #b8aac8;
    padding: 6px 0 6px 22px;
    position: relative;
    border-bottom: 1px solid rgba(148, 128, 176, 0.08);
}

.terms-list-item:last-child {
    border-bottom: none;
}

.terms-list-item::before {
    content: '✦';
    position: absolute;
    left: 0;
    top: 8px;
    font-size: 0.5rem;
    color: #c9a84c;
    opacity: 0.7;
}

/* ─── Acceptance box ────────────────────────────── */
.acceptance-box {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    background: rgba(201, 168, 76, 0.06);
    border: 1px solid rgba(201, 168, 76, 0.28);
    border-radius: 12px;
    padding: 24px 28px;
    margin: 40px 0 24px;
}

.acceptance-icon {
    font-size: 1.4rem;
    color: #c9a84c;
    min-width: 24px;
    text-align: center;
    margin-top: 2px;
    animation: pulse 3s ease-in-out infinite;
}

.acceptance-text {
    font-size: 0.92rem;
    line-height: 1.85;
    color: #c5b8d8;
    margin: 0;
}

.acceptance-text strong {
    color: #f0e6c8;
    font-weight: 400;
}

/* ─── Contact section ───────────────────────────── */
.contact-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding: 28px 0 0;
}

.section-icon-lg {
    font-size: 1.4rem;
    min-width: 40px;
    text-align: center;
    filter: drop-shadow(0 0 6px rgba(201, 168, 76, 0.4));
    padding-top: 2px;
}

/* ─── Links ─────────────────────────────────────── */
.link {
    color: #c9a84c;
    text-decoration: none;
    border-bottom: 1px solid rgba(201, 168, 76, 0.35);
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