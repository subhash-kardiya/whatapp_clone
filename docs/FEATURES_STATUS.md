# Feature Status — WhatsApp Clone

> Last updated: June 2026 | See [ARCHITECTURE.md](./ARCHITECTURE.md) for full system design

## ✅ Working (WhatsApp-like)

| Feature                             | Status | Notes                             |
| ----------------------------------- | ------ | --------------------------------- |
| Email login/register                | ✅     | Session-based auth                |
| 1:1 text chat                       | ✅     | Send, receive, chat list          |
| 1:1 media (image/video/audio/file)  | ✅     | Up to 20MB                        |
| Message ticks (sent/delivered/seen) | ✅     | DMs only                          |
| Typing indicator                    | ✅     | DMs via WebSocket                 |
| Reply to message                    | ✅     | Persisted with `reply_to_id`      |
| Star message                        | ✅     | Saved to database                 |
| Delete message                      | ✅     | For me / for everyone             |
| Pin / Mute / Archive / Favorite     | ✅     | Persisted in `chat_preferences`   |
| Message search                      | ✅     | `GET /messages/search?q=`         |
| Infinite scroll (older messages)    | ✅     | Load more on scroll up            |
| New chat / user search              | ✅     |                                   |
| Online / last seen                  | ✅     | Presence channel + ping           |
| Groups — create & manage            | ✅     | Add/remove members, rename, exit  |
| Groups — text + media messages      | ✅     | File upload supported             |
| Groups — real-time                  | ✅     | WebSocket `group.{id}`            |
| Groups — avatar upload              | ✅     | Admin only                        |
| Channels — CRUD                     | ✅     | Create, subscribe, post, discover |
| Channel message auth                | ✅     | Subscribers only                  |
| Communities — CRUD                  | ✅     | Create, join, leave, add groups   |
| Community announcements             | ✅     | Fixed enum issue                  |
| Communities filter in chat list     | ✅     | Shows community groups            |
| Status / Stories                    | ✅     | Text, image, video, 24h expiry    |
| Profile                             | ✅     | Name, about, avatar               |
| WebSocket (Reverb)                  | ✅     | DMs + groups when configured      |
| Polling fallback                    | ✅     | 3-second global poll              |

## 🔄 Partial / Next Phase

| Feature                  | Status       | Phase   |
| ------------------------ | ------------ | ------- |
| Password reset           | Stub         | Phase 1 |
| OTP login                | Not started  | Phase 1 |
| Social login             | Not started  | Phase 1 |
| 2FA                      | Not started  | Phase 3 |
| E2E encryption           | Not started  | Phase 3 |
| Message edit UI          | API ready    | Phase 1 |
| Reactions UI             | API ready    | Phase 1 |
| Voice/video calls        | Placeholder  | Phase 3 |
| Push notifications (FCM) | Browser only | Phase 1 |
| Channel real-time        | Polling only | Phase 2 |
| Next.js frontend         | Not started  | Phase 2 |
| AI features              | Not started  | Phase 4 |
| Admin panel              | Not started  | Phase 3 |
| Business/CRM             | Not started  | Phase 4 |

## 🐛 Bugs Fixed (This Session)

1. Chat list query returning wrong users (`orWhere` without grouping)
2. `formatFileSize` JS error (`round` → `Math.round`)
3. Reply message cleared before send
4. Communities filter showing empty list
5. Channel messages accessible without subscription
6. Community announcements without membership check
7. Broadcast channel auth too permissive
8. Group file attachments ignored
9. Group icon upload client-only (now persisted)
10. Announcement enum MySQL failure (uses `text` + `community_id`)
11. Pin/mute/archive lost on refresh (now in DB)
12. Star/delete message client-only (now in DB)
13. Missing Reverb env vars in `.env.example`
14. Conversation scope including group messages

## Quick Start

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm run build

# Terminal 1: App
php artisan serve

# Terminal 2: WebSocket
php artisan reverb:start

# Terminal 3: Queue (optional)
php artisan queue:work
```

Test users (password: `password`): seeded via `DatabaseSeeder`
