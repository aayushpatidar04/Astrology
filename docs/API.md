# Astrology Platform API

Laravel 12 JSON API powering real-time Vedic astrology tools, horoscopes, AI astrologer consultation chat, and user wallets.

---

## Base URL and authentication

- **Base URL Prefix:** `{{baseUrl}}/api` (Local default: `http://localhost:8000/api`)
- **Required Headers:**
  ```http
  Authorization: Bearer <token>
  Accept: application/json
  Content-Type: application/json
  ```
- **Authentication:** Bearer tokens issued via Sanctum upon OTP verification (`/api/verifyOtp`) or login.
- **Role-based Access Scopes:**
  - **Public / Unauthenticated:** OTP sending/verification, registration, blogs, horoscopes, and public calculators.
  - **User (`role:User`):** Consultations, live astrologer chat/call, wallet recharges, profile management, and paid AI Astrologer chat.
  - **Astrologer (`role:Astrologer`):** Managing online status, accepting/rejecting chat requests, viewing stats, earnings, and transaction history.
  - **Admin (`role:Admin`):** User management, blog publishing, horoscope/banner management, and recharge package configuration.
- **HTTP Status Codes:** `200` Success, `201` Created, `401` Unauthorized, `403` Forbidden, `402` Payment Required (Insufficient Wallet Balance), `404` Not Found, `409` Conflict, `422` Unprocessable Entity (Validation Error), `500` Server Error.

---

## Changes in this API update

- **Added:** `GET /api/ai-astrologer/status` — Retrieve Acharya AI Astrologer online status (`0` or `1`).
- **Added:** `GET /api/user/chat-sessions` — Retrieve user's past and active chat session history (paginated), matching the web app's chat sessions screen.
- **Added:** `POST /api/astrologer/chats/{chatId}/reject` — Astrologer chat request rejection endpoint triggering real-time broadcasting (`ChatRequestRejected`) and tandem Firebase push notifications.
- **Updated:** Event broadcasting (`MessageSent`, `IncomingAstrologerRequest`) now automatically dispatches Firebase push notifications in tandem, removing manual duplicate push calls across controllers.
- **Added:** Acharya AI Paid Astrologer Chat Bot endpoints (`/api/ai-astrologer/init`, `/api/ai-astrologer/send`, `/api/ai-astrologer/settle`) supporting persistent session history, contextual memory, a 30-minute session reuse rule, and real-time per-minute wallet billing (₹15.00/min).

---

## Authentication and account

| Method | Path | Access | Purpose |
|---|---|---|---|
| POST | `/api/sendOtp` | Public | Send login/register OTP (JSON: `{"phone":"9876543210"}`) |
| POST | `/api/verifyOtp` | Public | Verify OTP and issue Sanctum token (JSON: `{"phone":"9876543210","otp":"123456"}`) |
| POST | `/api/register-user` | Public | Register new user account |
| POST | `/api/register-astrologer` | Public | Register new astrologer profile (Multipart) |
| POST | `/api/logout` | Authenticated | Revoke current token |

- **Registration Field Requirements:**
  - **User Required Fields:** `name` (string), `phone` (string), `password` (string), `dob` (date), `birth_time` (string).
  - **User Optional Fields:** `email` (string), `gender` (`male`/`female`/`other`), `location` (string).

---

## Public Content & Horoscopes

| Method | Path | Purpose |
|---|---|---|
| GET | `/api/blogs` | List published blog posts (Query: `?page=N`) |
| GET | `/api/blogs/categories` | List blog categories |
| GET | `/api/blogs/category/{id}` | List blogs by category |
| GET | `/api/blogs/{id}` | Get blog details by ID or slug |
| GET | `/api/{{type}}-horoscopes` | Get horoscopes by type (`daily`, `weekly`, `monthly`, `yearly`) |
| GET | `/api/{{type}}-horoscope/{{sign}}` | Get horoscope for specific zodiac sign |
| GET | `/api/banners` | Get active promotional banners |
| GET | `/api/testimonials` | Get client testimonials |
| GET | `/api/chogdiya` | Get daily Choghadiya timings |
| GET | `/api/cities/autocomplete` | Live city coordinate autocomplete lookup (Query: `?key=Jaipur`) |

---

## Astrological Calculators & Tools

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/tools/kundli` | Generate detailed Janam Kundli with D1 chart and Panchang |
| POST | `/api/tools/matchmaking` | Ashtakoota 36-Guna Kundli matching between two partners |
| POST | `/api/tools/compatibility` | Zodiac compatibility score and elemental harmony |
| GET | `/api/tools/panchang` | Daily Hindu Panchang for given date and location |
| POST | `/api/tools/numerology` | Chaldean and Pythagorean numerology report |
| POST | `/api/tools/baby-names` | Vedic baby name generator based on Nakshatra Pada syllables |
| POST | `/api/tools/sun-sign` | Western tropical vs. Vedic sidereal Sun sign comparison |
| POST | `/api/tools/rising-sign` | Vedic Ascendant (Lagna) 12-sign report |
| POST | `/api/tools/rashi` | Janma Rashi (Moon Sign), Nakshatra, and gemstone recommendations |
| POST | `/api/tools/love-calculator` | Name number romantic compatibility |
| POST | `/api/tools/tarot-reading` | 78-card Tarot reading with AI hybrid synthesis |
| POST | `/api/tools/atmakaraka-darakaraka` | Jaimini Chara Karakas ranking (Soul & Spouse significators) |
| POST | `/api/tools/dasha-calculator` | 120-year Vimshottari Mahadasha + Antardasha timeline and remedies |
| POST | `/api/tools/nakshatra-calculator` | Birth star, Pada insights, Gana, Nadi, and deity analytics |
| POST | `/api/tools/manglik` | Multi-chart Manglik Dosha evaluation and Bhanga cancellations |
| POST | `/api/tools/sade-sati` | Shani Sade Sati transit status and 7.5-year phase timelines |
| GET | `/api/tools/choghadiya` | Day and Night Choghadiya timetable with live active slot spotlight |
| GET | `/api/tools/muhurat-finder` | Auspicious time window finder for business, property, and ceremonies |

---

## AI Astrologer Chat & Wallet Workflow

| Method | Path | Purpose |
|---|---|---|
| GET | `/api/ai-astrologer/status` | Get Acharya AI Astrologer online status (`0` or `1`) |
| GET | `/api/ai-astrologer/init` | Initialize or resume active AI chat session (30-min rule) |
| POST | `/api/ai-astrologer/send` | Send user message to Acharya AI and receive Gemini AI response |
| POST | `/api/ai-astrologer/settle` | Settle per-minute wallet deductions (₹15.00/min) |

---

## User Directory & Astrologer Endpoints

| Method | Path | Purpose |
|---|---|---|
| GET | `/api/astrologers` | List online/available astrologers with filters (12 per page) |
| POST | `/api/chats` | Create or retrieve chat thread with astrologer (`{"astrologer_id": 1}`) |
| POST | `/api/chats/{chatId}/start` | Start live chat session notification |
| GET | `/api/chats` | List user's active/past chat sessions |
| GET | `/api/user/chat-sessions` | Retrieve user's chat sessions history (paginated) |
| GET | `/api/chats/{chatId}/messages` | Get message history for chat thread (Query: `?page=N`) |
| POST | `/api/chats/{chatId}/messages` | Send chat message |
| POST | `/api/end-chat/{chatId}` | End human astrologer chat and settle wallet deduction |
| GET | `/api/wallet` | Get user wallet balance and transaction history |
| GET | `/api/recharge` | Get available wallet recharge packages |
| GET | `/api/astrologer/status/{id?}` | Get live online/busy availability status for an astrologer |
| POST | `/api/astrologer/status` | Update astrologer online status (`{"online": true}`) |
| POST | `/api/astrologer/chats/{chatId}/reject` | Reject incoming chat request (triggers `ChatRequestRejected` broadcast & push) |
| GET | `/api/astrologer/wallet` | Get astrologer earnings and transaction ledger |
