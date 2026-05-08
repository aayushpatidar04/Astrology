<template>
  <Head title="Privacy Policy" />
  <div class="privacy-page">
    <div class="stars" aria-hidden="true">
      <span v-for="n in 60" :key="n" class="star" :style="starStyle(n)" />
    </div>

    <div class="container">
      <header class="page-header">
        <div class="emblem">✦</div>
        <h1 class="title">My Astro Sathi</h1>
        <p class="subtitle">Privacy Policy</p>
      </header>

      <p class="intro">
        This Privacy Policy describes how 9752337971 and its affiliates (collectively &quot;9752337971, we, our, us&quot;) collect, use, share, protect or otherwise process your information/ personal data through our website https://myastrosathi.com/ (hereinafter referred to as Platform). Please note that you may be able to browse certain sections of the Platform without registering with us.We do not offer any product/service under this Platform outside India and your personal data will primarily be stored and processed in India. By visiting this Platform, providing your information or availing any product/service offered on the Platform, you expressly agree to be bound by the terms and conditions of this Privacy Policy, the Terms of Use and the applicable service/product terms and conditions, and agree to be governed by the laws of India including but not limited to the laws applicable to data protection and privacy. If you do not agree please do not use or access our Platform.
      </p>

      <section v-for="section in sections" :key="section.title" class="policy-section">
        <div class="section-icon">{{ section.icon }}</div>
        <div class="section-body">
          <h2 class="section-title">{{ section.title }}</h2>
          <p class="section-text" v-html="section.text" />
        </div>
      </section>

      <section class="policy-section">
        <div class="section-icon">⚖️</div>
        <div class="section-body">
          <h2 class="section-title">Grievance Officer</h2>
          <p class="section-text">
            Insert Name of the Office: My Astro Sathi<br><br>
            Designation: CEO<br><br>
            My Astro Sathi - 12 EXT Lokmanya Nagar Indore<br><br>
            Contact us: 9691565883<br><br>
            Phone: Time: Monday - Friday(9:00 - 18:00)
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
    title: 'Collection',
    text: 'We collect your personal data when you use our Platform, services or otherwise interact with us during the course of our relationship.and related information provided from time to time. Some of the information that we may collect includes but is not limited to personal data / information provided to us during sign-up/registering or using our Platform such as name, date of birth, address, telephone/mobile number, email IDand/or any such information shared as proof of identity or address. Some of the sensitive personal data may be collected with your consent, such as your bank account or credit or debit card or other payment instrument information or biometric information such as your facial features or physiological information (in order to enable use of certain features when opted for, available on the Platform) etc all of the above being in accordance with applicable law(s) You always have the option to not provide information, by choosing not to use a particular service or feature on the Platform. We may track your behaviour, preferences, and other information that you choose to provide on our Platform. This information is compiled and analysed on an aggregated basis. We will also collect your information related to your transactions on Platform and such third-party business partner platforms. When such a third-party business partner collects your personal data directly from you, you will be governed by their privacy policies. We shall not be responsible for the third-party business partner’s privacy practices or the content of their privacy policies, and we request you to read their privacy policies prior to disclosing any information. If you receive an email, a call from a person/association claiming to be 9752337971 seeking any personal data like debit/credit card PIN, net-banking or mobile banking password, we request you to never provide such information. If you have already revealed such information, report it immediately to an appropriate law enforcement agency.',
  },
  {
    icon: '🔍',
    title: 'Usage',
    text: 'We use personal data to provide the services you request. To the extent we use your personal data to market to you, we will provide you the ability to opt-out of such uses. We use your personal data to assist sellers and business partners in handling and fulfilling orders; enhancing customer experience; to resolve disputes; troubleshoot problems; inform you about online and offline offers, products, services, and updates; customise your experience; detect and protect us against error, fraud and other criminal activity; enforce our terms and conditions; conduct marketing research, analysis and surveys; and as otherwise described to you at the time of collection of information. You understand that your access to these products/services may be affected in the event permission is not provided to us.',
  },
  {
    icon: '🔗',
    title: 'Sharing',
    text: 'We may share your personal data internally within our group entities, our other corporate entities, and affiliates to provide you access to the services and products offered by them. These entities and affiliates may market to you as a result of such sharing unless you explicitly opt-out. We may disclose personal data to third parties such as sellers, business partners, third party service providers including logistics partners, prepaid payment instrument issuers, third-party reward programs and other payment opted by you. These disclosure may be required for us to provide you access to our services and products offered to you, to comply with our legal obligations, to enforce our user agreement, to facilitate our marketing and advertising activities, to prevent, detect, mitigate, and investigate fraudulent or illegal activities related to our services. We may disclose personal and sensitive personal data to government agencies or other authorised law enforcement agencies if required to do so by law or in the good faith belief that such disclosure is reasonably necessary to respond to subpoenas, court orders, or other legal process. We may disclose personal data to law enforcement offices, third party rights owners, or others in the good faith belief that such disclosure is reasonably necessary to: enforce our Terms of Use or Privacy Policy; respond to claims that an advertisement, posting or other content violates the rights of a third party; or protect the rights, property or personal safety of our users or the general public.',
  },
  {
    icon: '🔒',
    title: 'Security Precautions',
    text: 'To protect your personal data from unauthorised access or disclosure, loss or misuse we adopt reasonable security practices and procedures. Once your information is in our possession or whenever you access your account information, we adhere to our security guidelines to protect it against unauthorised access and offer the use of a secure server. However, the transmission of information is not completely secure for reasons beyond our control. By using the Platform, the users accept the security implications of data transmission over the internet and the World Wide Web which cannot always be guaranteed as completely secure, and therefore, there would always remain certain inherent risks regarding use of the Platform. Users are responsible for ensuring the protection of login and password records for their account.',
  },
  {
    icon: '🗑️',
    title: 'Data Deletion and Retention',
    text: 'You have an option to delete your account by visiting your profile and settings on our Platform , this action would result in you losing all information related to your account. You may also write to us at the contact information provided below to assist you with these requests. We may in event of any pending grievance, claims, pending shipments or any other services we may refuse or delay deletion of the account. Once the account is deleted, you will lose access to the account. We retain your personal data information for a period no longer than is required for the purpose for which it was collected or as required under any applicable law. However, we may retain data related to you if we believe it may be necessary to prevent fraud or future abuse or for other legitimate purposes. We may continue to retain your data in anonymised form for analytical and research purposes.',
  },
  {
    icon: '⚙️',
    title: 'Your Rights',
    text: 'You may access, rectify, and update your personal data directly through the functionalities provided on the Platform.',
  },
  {
    icon: '✅',
    title: 'Consent',
    text: 'By visiting our Platform or by providing your information, you consent to the collection, use, storage, disclosure and otherwise processing of your information on the Platform in accordance with this Privacy Policy. If you disclose to us any personal data relating to other people, you represent that you have the authority to do so and permit us to use the information in accordance with this Privacy Policy. You, while providing your personal data over the Platform or any partner platforms or establishments, consent to us (including our other corporate entities, affiliates, lending partners, technology partners, marketing channels, business partners and other third parties) to contact you through SMS, instant messaging apps, call and/or e-mail for the purposes specified in this Privacy Policy. You have an option to withdraw your consent that you have already provided by writing to the Grievance Officer at the contact information provided below. Please mention “Withdrawal of consent for processing personal data” in your subject line of your communication. We may verify such requests before acting on our request. However, please note that your withdrawal of consent will not be retrospective and will be in accordance with the Terms of Use, this Privacy Policy, and applicable laws. In the event you withdraw consent given to us under this Privacy Policy, we reserve the right to restrict or deny the provision of our services for which we consider such information to be necessary.',
  },
  {
    icon: '📝',
    title: 'Changes to this Privacy Policy',
    text: 'Please check our Privacy Policy periodically for changes. We may update this Privacy Policy to reflect changes to our information practices. We may alert / notify you about the significant changes to the Privacy Policy, in the manner as may be required under applicable laws.',
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