# Production Blueprint — WhatsApp-Like Communication Platform

> Laravel 12 · MySQL · Redis · Reverb WebSockets · Sanctum · OpenAI/Ollama · AWS

See also: [ARCHITECTURE.md](./ARCHITECTURE.md) · [FEATURES_STATUS.md](./FEATURES_STATUS.md)

---

## 1. System Architecture

```
Clients (Web/Mobile) → Nginx → Laravel API → MySQL
                              ↓ Redis (cache/queue/pub-sub)
                              ↓ Reverb (WebSockets)
                              ↓ S3 + CloudFront (media)
                              ↓ Horizon (queue workers)
                              ↓ OpenAI/Ollama (AI service)
```

| Layer     | Technology                                  |
| --------- | ------------------------------------------- |
| API       | Laravel 12 modular monolith → microservices |
| Auth      | Laravel Sanctum (SPA) + JWT (mobile)        |
| Real-time | Laravel Reverb + Redis pub/sub              |
| Queue     | Redis + Horizon                             |
| Search    | Meilisearch                                 |
| Push      | Firebase FCM                                |
| Media     | S3 + CloudFront                             |

---

## 2. Authentication Module

### Tables

| Table                    | Columns                                                     |
| ------------------------ | ----------------------------------------------------------- |
| `users`                  | id, name, email, phone, password, avatar, email_verified_at |
| `otp_codes`              | id, phone, code, expires_at, verified_at                    |
| `personal_access_tokens` | Sanctum tokens (multi-device)                               |
| `sessions`               | id, user_id, ip, user_agent, last_activity                  |
| `two_factor_secrets`     | user_id, secret, recovery_codes                             |
| `login_history`          | user_id, ip, device, location, logged_in_at                 |
| `social_accounts`        | user_id, provider, provider_id, token                       |

### API Routes (`/api/v1/auth`)

```
POST   /register              Email + password
POST   /login                 Email login
POST   /otp/send              Send OTP (Twilio/MSG91)
POST   /otp/verify            Verify OTP → token
POST   /social/{provider}     Google/Facebook OAuth
POST   /2fa/enable            Enable TOTP
POST   /2fa/verify            Verify 2FA on login
GET    /devices               List active sessions
DELETE /devices/{id}          Revoke device
POST   /logout                Current session
POST   /logout-all            All devices
GET    /login-history         Audit trail
```

### Security Flow

```
Login → Rate limit → Validate credentials → 2FA check?
  → Issue Sanctum token (web) / JWT (mobile, 15min + refresh)
  → Log login_history → Broadcast device login event
```

### Laravel Structure

```
app/Modules/Auth/
├── Actions/LoginUser.php, SendOtp.php, VerifyOtp.php
├── Http/Controllers/AuthController.php
├── Http/Requests/LoginRequest.php, OtpRequest.php
├── Services/OtpService.php, TwoFactorService.php
└── Middleware/EnsureTwoFactorVerified.php
```

---

## 3. Chat System (Core)

### ER Diagram

```
users ──┬── messages (sender_id, receiver_id)
        ├── groups ── group_members
        ├── channels ── channel_subscribers
        └── communities ── community_groups ── groups

messages: id, sender_id, receiver_id, group_id, channel_id,
          community_id, reply_to_id, message, type, status,
          file_path, edited_at, deleted_at, deleted_for_everyone
```

### Broadcasting Events

| Event                  | Channel              | When           |
| ---------------------- | -------------------- | -------------- |
| `MessageSent`          | `chat.{receiverId}`  | DM sent        |
| `GroupMessageSent`     | `group.{groupId}`    | Group message  |
| `MessageStatusUpdated` | `chat.{userId}`      | Delivered/seen |
| `TypingIndicator`      | `chat.{receiverId}`  | Typing         |
| `MessageEdited`        | conversation channel | Edit           |
| `MessageDeleted`       | conversation channel | Delete         |

### API Routes

```
GET    /chats                    Chat list
POST   /chats/send               Send DM
POST   /chats/group/send         Send group message
PUT    /messages/{id}            Edit (15 min window)
DELETE /messages/{id}            Delete for me
DELETE /messages/{id}/everyone   Delete for everyone
POST   /messages/{id}/reply      Reply
POST   /messages/{id}/forward    Forward
POST   /messages/{id}/star       Star
POST   /messages/{id}/react      Reaction
GET    /messages/search?q=       Search
POST   /chat-preferences         Pin/mute/archive
```

### Redis Queue Flow

```
MessageSent event → Broadcast (sync)
Media upload → ProcessMediaJob → compress → S3 → update message
Push notification → SendPushNotificationJob → FCM
```

---

## 4. Media System

### Tables

| Table         | Purpose                                      |
| ------------- | -------------------------------------------- |
| `media_files` | id, user_id, path, mime, size, disk, cdn_url |

### Flow

```
Upload → Validate mime/size → Store temp local
  → ProcessMediaJob (compress image/video)
  → Upload S3 → Generate signed CDN URL
  → Save media_files record → Return URL in message
```

### API

```
POST /media/upload     Multipart → CDN URL
GET  /media/{id}       Signed download URL
```

---

## 5. Status System

### Tables: `statuses`, `status_views`

### Scheduler: `status:cleanup` hourly (delete expired 24h)

### Job: `DeleteExpiredStatuses`

---

## 6. Call System (WebRTC)

```
Client A ←→ Signaling Server (Laravel/Reverb) ←→ Client B
                ↓
         LiveKit / Janus SFU (group calls)
```

| Component    | Role                                |
| ------------ | ----------------------------------- |
| Laravel      | Room creation, ICE candidates relay |
| Node/LiveKit | Media SFU for group/video           |
| TURN server  | NAT traversal (coturn)              |

---

## 7. Community System ✅ (Implemented)

### Tables

- `communities` — name, description, avatar, owner_id
- `community_members` — role: admin/member
- `community_groups` — links groups to community

### WhatsApp Flow (Current)

1. Communities tab → list owned + member communities
2. Select community → left panel shows Announcements + Groups
3. Click Announcements → right panel shows announcement feed
4. Click Group → opens group chat on right panel
5. Owner can add groups, send announcements, delete community

### API

```
GET    /communities
GET    /communities/{id}         Full detail with groups
POST   /communities/create
POST   /communities/{id}/add-group
POST   /communities/{id}/announce
GET    /communities/{id}/announcements
POST   /communities/{id}/join
DELETE /communities/{id}         Owner delete
POST   /communities/{id}/leave   Member leave
```

---

## 8. Channel System (Broadcast)

One-to-many: owner posts → `ChannelMessageSent` → all subscribers via queue fan-out.

```
POST /channels/send → Dispatch BroadcastChannelMessageJob
  → For each subscriber batch: push notification + websocket
```

---

## 9. AI Module

```
app/Modules/AI/
├── Services/AiService.php       OpenAI/Ollama abstraction
├── Jobs/TranslateMessageJob.php
├── Jobs/SummarizeChatJob.php
├── Jobs/ModerateContentJob.php
└── Prompts/                     Prompt templates
```

| Feature     | Model         | Strategy              |
| ----------- | ------------- | --------------------- |
| Smart Reply | gpt-4o-mini   | Cache common patterns |
| Translation | gpt-4o        | Queue async           |
| Moderation  | gpt-4o-mini   | Real-time on send     |
| Summary     | gpt-4o        | On-demand             |
| Voice→Text  | whisper-1     | After audio upload    |
| Local       | Ollama llama3 | Self-hosted fallback  |

---

## 10. Hidden Systems

| System     | Implementation                               |
| ---------- | -------------------------------------------- |
| Cache      | Redis: user online, chat list, unread counts |
| Queue      | Horizon: media, push, AI, broadcast fan-out  |
| Rate limit | `throttle:api` middleware per route          |
| Audit      | `audit_logs` table + Observer                |
| Spam       | AI moderation + rate limits                  |
| Push       | FCM via `SendPushNotificationJob`            |

---

## 11–12. Business & Productivity (Phase 4)

Deferred to Phase 4 — CRM, orders, tasks, calendar modules in `app/Modules/Business/` and `app/Modules/Productivity/`.

---

## 13. Laravel Folder Structure (Target)

```
app/
├── Modules/
│   ├── Auth/
│   ├── Chat/
│   ├── Community/        ← CommunityService, CommunityController
│   ├── Channel/
│   ├── Media/
│   ├── Status/
│   ├── Call/
│   ├── AI/
│   └── Admin/
├── Events/
├── Jobs/
├── Services/             ← CommunityService (current)
└── Http/Middleware/
database/migrations/
routes/api/v1/
docs/                     ← This documentation
```

---

## Deployment (Docker + AWS)

```yaml
# docker-compose.yml
services:
    app: # PHP-FPM Laravel
    nginx: # Reverse proxy
    reverb: # WebSocket
    worker: # php artisan horizon
    mysql: # RDS in production
    redis: # ElastiCache in production
```

### AWS Stack

- **ECS Fargate** — API + Reverb + Workers
- **RDS MySQL** — Multi-AZ
- **ElastiCache Redis** — Cache + Queue + Pub/Sub
- **S3 + CloudFront** — Media CDN
- **SES** — Email OTP
- **Route 53** — DNS

---

## Implementation Roadmap

| Phase          | Duration  | Deliverables                                     |
| -------------- | --------- | ------------------------------------------------ |
| **0 — MVP** ✅ | Done      | Chat, groups, channels, communities, status      |
| **1**          | 4–6 wks   | OTP, Sanctum API, message edit, FCM push, search |
| **2**          | 6–8 wks   | Next.js frontend, channel broadcast scale, calls |
| **3**          | 8–12 wks  | 2FA, E2E, admin panel, RBAC                      |
| **4**          | 12–16 wks | AI full suite, CRM, microservices, K8s           |

---

_Version 1.1 — Community module redesigned WhatsApp-style_
