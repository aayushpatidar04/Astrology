# Astrology API

This document describes the Laravel JSON API and its Postman collection at
[`postman/Astrology API.postman_collection.json`](./postman/Astrology%20API.postman_collection.json).

## Base URL and authentication

Use `{{baseUrl}}/api` as the request prefix (the included Postman collection defaults to
`http://localhost:8000`). Protected endpoints require a Sanctum personal access token:

```http
Authorization: Bearer <token>
Accept: application/json
```

Obtain a token with `POST /api/verifyOtp` for an existing account, or with either registration
endpoint. `POST /api/sendOtp` and `POST /api/verifyOtp` are rate limited. OTPs expire after five
minutes.

Protected routes are role-scoped:

- **Any authenticated account:** notifications, blogs, horoscopes, banners, testimonials, chat
  messages, and typing indicators.
- **User:** astrologer directory, chat creation/start, wallet, recharge, and chat settlement.
- **Astrologer:** own chats, wallet, stats, bank details, and transaction history.

Validation errors use HTTP `422` and Laravel's JSON `message` and `errors` fields. Authentication
failures use `401`; authorization failures use `403` or `404`. Chat start returns `409` if the
astrologer is offline and `422` if the user wallet is below the four-minute minimum. Message
creation returns `201`.

## Changes in this API update

- Added idempotent `POST /chats` using the astrologer profile ID from the directory response.
- Split persistent chat creation from the session-start action at `POST /chats/{chatId}/start`.
  Session start enforces participant ownership, astrologer availability, the four-minute balance
  minimum, and notifies the astrologer once for a newly opened session.
- Kept `GET /start-chat/{astrologerUserId}` as a compatibility alias for existing clients.
- Scoped astrologer stats, bank details, and payouts to the authenticated astrologer's own profile.
- Changed invalid chat access to `404`, message creation to `201`, invalid horoscope types to `422`,
  and unknown blog/category lookups to `404` instead of returning success-shaped responses.
- Fixed registration failures to return `500` without exposing internal exception details, and made
  an omitted optional user profile image return `null` rather than an incomplete path.

## Authentication and account

| Method | Path | Access | Purpose |
|---|---|---|---|
| POST | `/sendOtp` | Public | Send an OTP. JSON: `{"phone":"9876543210"}` |
| POST | `/verifyOtp` | Public | Verify OTP and log in an existing account. JSON: `{"phone":"9876543210","otp":"123456"}` |
| POST | `/register-user` | Public | Create a User account; returns a token. |
| POST | `/register-astrologer` | Public | Create an Astrologer account; multipart form because `profile_image` is required. |
| POST | `/logout` | Authenticated | Revoke the current Sanctum token. |
| POST | `/device-token` | Authenticated | Register/update this account's FCM token. JSON: `{"device_token":"<fcm-token>"}` |
| POST | `/test-notification` | Authenticated | Send a test FCM notification to the current account. JSON: `{"title":"Test","body":"Message","data":{}}` |

Registration fields:

- User: required `name`, `email`, 10-digit `phone`, `password`, and
  `password_confirmation`; optional `dob`, `birth_time`, `gender` (`male`, `female`, `other`),
  `location`, `preferred_language`, and `profile_image`.
- Astrologer: required `name`, `email`, 10-digit `phone`, `password`,
  `password_confirmation`, `expertise` (array), `experience_years` (integer), and
  `profile_image`.

## Public app content (authenticated)

| Method | Path | Purpose |
|---|---|---|
| GET | `/blogs/categories` | List blog categories. |
| GET | `/blogs` | Paginated blogs (10 per page); supports Laravel `?page=N`. |
| GET | `/blogs/category/{categoryId}` | Paginated blogs for a category. |
| GET | `/blogs/{blogId}` | Fetch one blog. |
| GET | `/{type}-horoscopes` | Horoscope list. `type`: `today`, `tomorrow`, `yesterday`, `weekly`, `monthly`, or `yearly`. |
| GET | `/{type}-horoscope/{sign}` | Horoscope for a sign; uses the same types above. |
| GET | `/chogdiya` | Chogdiya for the current day. |
| GET | `/banners` | Active banners. |
| GET | `/testimonials` | Testimonials. |

Invalid horoscope types return `422`; unknown blog/category IDs return `404`.

## User directory and chat

| Method | Path | Purpose |
|---|---|---|
| GET | `/astrologers` | Paginated online astrologers (12 per page). Each result includes profile `id` and account `user_id`. |
| POST | `/chats` | Create or retrieve a one-to-one chat. |
| POST | `/chats/{chatId}/start` | Start a chat session and notify the astrologer. |
| GET | `/chats` | List the authenticated user's chats and most recent message. |
| GET | `/chats/{chatId}/messages` | Read chat messages, newest first, paginated (50 per page). |
| POST | `/chats/{chatId}/messages` | Send a text message. JSON: `{"message":"Hello"}`; max 1000 characters. |
| POST | `/chats/{chatId}/typing` | Broadcast typing state. JSON: `{"typing":true}`. |
| POST | `/end-chat/{chatId}` | Settle the active chat session. |
| GET | `/start-chat/{astrologerUserId}` | **Legacy alias** for creating/retrieving and starting a chat; use the two POST endpoints above instead. |

Create a chat using the **astrologer profile ID** returned as `id` by `GET /astrologers`:

```http
POST /api/chats
Content-Type: application/json

{"astrologer_id": 12}
```

The response includes `chat.id`, `chat.name`, and the astrologer profile summary. A newly created
chat returns `201`; an existing chat returns `200`. Repeating this request for the same user and
astrologer reuses the existing one-to-one chat.

Start a session only when the user is ready to begin:

```http
POST /api/chats/34/start
```

The response includes a `session_reference` UUID. Retain it and pass it to the settlement endpoint.
Starting requires the astrologer to be online and the user's wallet to contain at least four
minutes at the astrologer's charged text rate. The first start creates the active session and
broadcasts an incoming-chat event; repeating it reuses that active session.

Settle a session:

```http
POST /api/end-chat/34
Content-Type: application/json

{"seconds":120,"reason":"user","session_reference":"<session-reference-uuid>"}
```

`seconds` is required and non-negative; `reason` and `session_reference` are optional. Supplying
the session reference makes settlement safely repeatable: an already settled session returns its
previous deduction. The deduction is calculated from elapsed seconds and the current charged text
rate, capped at the wallet balance.

## User wallet and recharge

| Method | Path | Purpose |
|---|---|---|
| GET | `/wallet` | Current user's wallet and balance. |
| GET | `/recharge` | Recharge offers and current wallet balance. |
| GET | `/user/phonepe/webview/{packageId}` | Create a PhonePe checkout and return its `redirect_url`. |

The payment response must be treated as unsuccessful if `redirect_url` is absent; complete payment
through the returned checkout URL and the configured PhonePe callback flow.

## Astrologer endpoints

| Method | Path | Purpose |
|---|---|---|
| GET | `/astrologer/wallet` | Astrologer wallet balance and paginated transactions. |
| GET | `/astrologer/chats` | List chats belonging to the authenticated astrologer. |
| GET | `/astrologer/chats/{chatId}/messages` | Read messages for a chat the astrologer participates in. |
| GET | `/astrologers/{id}/stats` | Consultation counts, earnings, recent consultations, and astrologer profile. |
| GET | `/astrologers/{id}/bank-details` | Fetch the authenticated astrologer's bank details. |
| POST | `/astrologers/{id}/bank-details` | Create/update own bank details. |
| GET | `/astrologers/{id}/transactions` | Fetch own payout transactions. |

For these endpoints, `{id}` is the Astrologer profile ID (`id` from the astrologer profile). All
data operations are scoped to the authenticated astrologer; another astrologer's ID is not
accessible.

Bank details request:

```json
{
  "account_holder": "Example Astrologer",
  "account_number": "1234567890",
  "ifsc_code": "ABCD0123456",
  "bank_name": "Example Bank",
  "branch_name": "Main Branch"
}
```

`branch_name` is optional. Store bank details securely in the client and avoid logging the
account number.

## Postman

Import [`docs/postman/Astrology API.postman_collection.json`](./postman/Astrology%20API.postman_collection.json).
Set the collection variables `baseUrl`, `token`, `astrologerId`, `chatId`, `packageId`,
`categoryId`, `blogId`, `type`, and `sign`. Run OTP verification or account registration to obtain
a token, then set `token` before running protected requests.
