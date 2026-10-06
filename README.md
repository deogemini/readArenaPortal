# ReadArena Portal and Android API

ReadArena is a Laravel portal for readers, authors, and administrators. Its authenticated JSON API is designed for the Android client and uses Laravel Sanctum bearer tokens.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, and a database supported by Laravel.

1. Copy `.env.example` to `.env` and configure the database, mail transport, and `APP_URL`.
2. Run `composer install` and `npm install`.
3. Run `php artisan key:generate`, `php artisan migrate`, and `php artisan storage:link`.
4. If the database contains PDFs uploaded before private storage was enabled, run `php artisan books:protect-pdfs` once to move them out of the public disk.
5. Run `npm run build` for the portal assets.
6. Start the app with `php artisan serve --host=0.0.0.0 --port=8000`.

For the Android emulator, use `http://10.0.2.2:8000/api` as the API base URL when the Laravel server runs on the development computer. A physical phone needs the computer's reachable LAN address, and a deployed app needs its HTTPS domain. Configure `APP_URL` to the externally reachable site URL so image and PDF links point back to the server.

The admin **Users** page is a near-real-time activity dashboard. It refreshes every five seconds and shows recent sign-ins and reader actions from both the web portal and Android API, plus online presence (a user is considered online for two minutes after their last authenticated request). Apply pending database migrations after deploying so the activity table and presence fields are available.

### Large book uploads on Ubuntu/Apache

Book PDFs can be up to 100 MiB by default (`BOOK_PDF_MAX_KB=102400`). Apache must accept a request slightly larger than the PDF. Add this directive inside the site's active `<VirtualHost>` block:

```apache
LimitRequestBody 125829120
```

Set PHP's `upload_max_filesize=100M`, `post_max_size=110M`, and `memory_limit=256M` in the configuration for the PHP handler Apache actually uses:

- PHP-FPM: `/etc/php/8.3/fpm/php.ini` (and check `/etc/php/8.3/fpm/pool.d/` for pool-level overrides).
- Apache `mod_php`: `/etc/php/8.3/apache2/php.ini`.

`public/.user.ini` applies to CGI/FastCGI PHP handlers, including PHP-FPM; it is ignored when PHP runs as an Apache module. The PHP POST limit must be higher than the file size because the multipart request also includes form data.

To identify the handler, run `sudo apachectl -M | grep -E 'php_module|proxy_fcgi_module'`. After editing the configuration, validate Apache and apply the matching restarts:

```bash
sudo apachectl -t
sudo systemctl reload apache2
# If using PHP-FPM:
sudo systemctl restart php8.3-fpm
# If using mod_php, restart Apache instead of reloading it:
sudo systemctl restart apache2
```

The application returns a readable HTTP 413 page if PHP's effective `post_max_size` is still too low. An Apache `LimitRequestBody` rejection happens before Laravel and needs to be diagnosed from Apache's error log.

## API reference

Open the interactive Swagger UI at `/api/documentation`. The OpenAPI JSON is at `/docs/api-docs.json`.

## English and Kiswahili

The portal supports English (`en`) and Kiswahili (`sw`). Visitors can switch languages from the language selector; signed-in users keep their choice in their account and session. For Android, `GET /api/language` returns the saved choice and available languages, and `PATCH /api/language` with `{ "locale": "sw" }` or `{ "locale": "en" }` saves it. `GET /api/translations/sw` returns the shared UI string catalog; English uses each key as its displayed text. API clients can also send `X-Locale: sw` or `Accept-Language: sw` to choose a request language. Book and reader-authored content stays in the language it was entered in. After deployment, apply the new user locale field with `php artisan migrate --force` and rebuild frontend assets with `npm run build`.

Swagger lists every registered Android API operation. Select Production, Local development, or Android emulator as the server at the top of the page. The production base URL is `https://arenayakusoma.eportsolutions.co.tz/api`.

All API paths below are relative to the `/api` base URL. Except for authentication, documentation, and the public quiz-performance endpoint, send the token returned by register or login on every request:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

Send JSON request bodies with `Content-Type: application/json`. Validation errors return HTTP `422` with Laravel's `message` and `errors` fields. Unauthenticated requests return HTTP `401`; missing or unpublished books and quizzes return HTTP `404`.

### Authentication

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/auth/register` | Create a reader account and receive a token |
| POST | `/auth/login` | Sign in with email and password |
| POST | `/auth/google` | Sign in with a Google access token |
| POST | `/auth/forgot-password` | Request an email password reset link |
| POST | `/auth/reset-password` | Reset a password with the emailed token |
| POST | `/auth/logout` | Revoke the current device token |

Register accepts `name`, `email`, `password`, `password_confirmation`, optional `role` (`reader` or `author`), and optional `device_name`. Login accepts `email`, `password`, and optional `device_name`. Auth responses contain `user`, `token`, and `token_type` (`Bearer`).

### Reader API

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/dashboard` | Reader statistics and dashboard sections |
| GET | `/notifications?filter=unread` | Paginated in-app notifications and unread count |
| PATCH | `/notifications/{notification}/read` | Mark an owned notification as read |
| PATCH | `/notifications/read-all` | Mark all your unread notifications as read |
| DELETE | `/notifications/{notification}` | Delete an owned notification |
| GET, PATCH | `/notification-preferences` | Read or update per-type in-app notification settings |
| PUT, DELETE | `/push-token` | Register or remove this Android device's Firebase push token |
| GET, PATCH | `/profile` | Read or update name, email, and phone number |
| POST | `/profile/photo` | Upload `profile_photo` as JPEG, PNG, or WebP multipart form data (up to 2 MB); response returns the refreshed profile including `profile_photo_url` |
| GET | `/books` | Search and browse published books |
| GET | `/books/{book}` | Book details, published quizzes, and this reader's progress |
| GET | `/books/{book}/content` | Stream an authorized published book PDF |
| POST | `/books/{book}/progress` | Sync `current_page` and update active pages goals |
| GET | `/reading/progress` | List the signed-in reader's book progress |
| GET | `/shelf` | List this reader's books and shelf statuses |
| PUT, DELETE | `/books/{book}/shelf` | Set one of the five shelf statuses or remove a book |
| GET, POST | `/bookmarks` | List bookmarks or save a page and optional label |
| PATCH, DELETE | `/bookmarks/{bookmark}` | Update or remove an owned bookmark |
| GET, POST | `/reviews` | List your reviews or submit one for moderation |
| PATCH, DELETE | `/reviews/{review}` | Update or remove your review |
| GET | `/books/{book}/reviews` | List approved reviews and average rating for a book |
| GET, POST | `/goals` | List goals or create a reading goal |
| PATCH, DELETE | `/goals/{goal}` | Update or delete one of the signed-in reader's goals |
| GET, POST, PATCH, DELETE | `/lessons`, `/lessons/{lesson}` | Manage owned lessons; publishing requires a passing quiz for the selected book |
| GET, POST, PATCH, DELETE | `/recommendations`, `/recommendations/{recommendation}` | Manage owned recommendations; publishing requires a passing quiz for the selected book |
| GET | `/shows` | List upcoming shows, RSVP count, and the current reader's RSVP state |
| POST, DELETE | `/shows/{show}/rsvp` | RSVP to or cancel an upcoming show |
| GET | `/shows/{show}/application-options` | List published books whose quizzes you have passed for a show application |
| GET | `/show-applications` | List your live show applications, selected books, and review statuses |
| POST | `/shows/{show}/applications` | Apply with `{ "book_id": 12, "motivation": "..." }` |
| DELETE | `/show-applications/{application}` | Withdraw a pending show application |
| GET, POST | `/duels` | List invitations and eligible opponents, or invite a reader verified for the same book |
| PATCH | `/duels/{duel}/respond` | Accept or reject an invitation as the invited reader; notifies the challenger |
| PATCH | `/duels/{duel}/cancel` | Cancel an invitation as its challenger |
| GET | `/leaderboard?type=reading&period=weekly` | Paginated reading rankings for all readers, including completed books and book-goal performance |
| GET | `/leaderboard?type=quizzes&period=weekly` | Daily, weekly, monthly, or all-time quiz competition rankings |
| GET | `/quizzes/{quiz}` | Load a published quiz and this reader's attempt count |
| POST | `/quizzes/{quiz}/submit` | Submit `{ "answers": { "QUESTION_ID": [ANSWER_ID, ...] } }`; a single integer remains accepted for single-choice questions |

### Make portal quiz questions available to Android

Questions are stored in the same database the API reads, so they do not need a separate push or sync. For Android access, publish the related book first, then publish its quiz. Questions created by an author remain in a draft quiz until an administrator publishes it.

The app can call `GET /api/public/books/{book_id}/quiz-performance` without signing in to get a published book's quiz count and aggregate reader performance, including per-quiz reader counts, attempts, averages, pass rates, and best scores. The public endpoint never returns reader identities or individual scores. After signing in and adding the returned Sanctum token as a bearer token, the app can also call `GET /api/books` for book summaries, `GET /api/books/{book_id}` for a book and its published quizzes, or `GET /api/quizzes/{quiz_id}` to load one quiz directly. These responses include aggregate quiz activity and performance for the book and each quiz. Each question includes `question_type` and `response_format`; choice questions include options, while text questions do not expose accepted answers or marking guides. `latest_attempt` lets Android refresh a pending written response and see its finalized result. Draft or unpublished books and quizzes are intentionally omitted or return `404`.

Submit every question's selected answer IDs to `POST /api/quizzes/{quiz_id}/submit`. For example:

```json
{
  "answers": {
    "14": [90, 91],
    "15": [96]
  }
}
```

For multi-select questions, readers earn the question's points only when they select every correct choice and no incorrect choices.

Book search supports `q`, `genre` (slug or name), `author`, `publisher`, `language`, `publication_year`, `min_pages`, `max_pages`, `reading_status`, `quiz_available`, `featured`, and `min_rating`. Use `sort` with `newest`, `title`, `highest_rated`, `popularity`, `most_completed`, or `most_dueled`; `page` and `per_page` (1 to 50) control pagination. The response keeps results in `data` and includes pagination values in `meta`. Book summaries include review, reader, completion, duel, and quiz activity metrics: `published_quizzes_count`, `quiz_readers_count`, `quiz_attempts_count`, `quiz_pending_review_attempts_count`, `quiz_graded_attempts_count`, `quiz_passed_attempts_count`, `quiz_average_score`, `quiz_pass_rate`, and `quiz_best_score`. Each quiz also includes a `performance` object with distinct readers, attempts, pending reviews, graded attempts, passed attempts, average score, pass rate, and best score. Pending written responses count as attempts, but are excluded from score averages and pass rates until an administrator grades them. These are aggregate results and do not expose individual reader scores or identities. Book records provide `cover_image_url`; book details provide an authenticated `pdf_url` pointing to `/books/{book}/content`. Send the bearer token when streaming. New book PDFs are saved outside public storage. Quiz questions support `single_choice`, `multiple_choice`, `true_false`, `one_word`, `short_answer`, and `written_response`. One-word and short answers are automatically checked against accepted variants without regard to case or punctuation. Written responses are saved for administrator review; API submissions return `review_status: "pending_review"` and a null score until graded. Quiz responses never disclose accepted answers or marking guides. For choice questions, submit one answer ID or an array of selected IDs; multi-choice points are awarded only when every correct option and no incorrect options are selected. For a pages goal, send `goal_type: "pages"` and a published `book_id`. For a books goal, use `goal_type: "books"` and omit `book_id`. Both types require `title`, `target_value`, `start_date`, and `end_date` (`YYYY-MM-DD`). Reading progress cannot exceed the book's page count when one is set. Repeated syncs at the same or an earlier page do not add pages to the total. Goal updates preserve completed progress up to the new target.

Reader content may be saved as a private draft without quiz verification. Publishing a lesson, recommendation, or duel challenge requires a passing attempt for the same published book. Live show applications also require a passing quiz for the book selected by the reader; the server records the reader's best passing score. Duel invitations support pending, accepted, rejected, and cancelled states. The leaderboard adds the best passing score once per published quiz in the chosen period.

For profile photos, send `POST /api/profile/photo` as `multipart/form-data` with the file field `profile_photo`; accepted image formats are JPEG, PNG, and WebP, with a 2 MB maximum. Use the returned `data.profile_photo_url` to refresh the Android profile. Reading rankings include every reader, even readers with no completed books. They rank by distinct books marked completed within the selected period, then by book-goal completion rate and achieved book-goal count. Pass `type=reading` or `type=quizzes`; reading results are paginated with `page` and `per_page` (maximum 50). Mark books completed through `PUT /api/books/{book_id}/shelf` with `{ "status": "completed" }` for reading rankings and book goals to update. Quiz rankings remain the default type for backward compatibility.

Reader usernames are optional and can be set or cleared in the web profile or with `PATCH /api/profile`. Send `username` as 3–24 lowercase letters, numbers, or underscores; values are normalized to lowercase and must be unique. The profile API returns the saved username, or `null` if the reader has not set one.

To send a duel request, call `GET /api/duels` to retrieve books you are verified for and eligible opponents, then `POST /api/duels` with `{ "book_id": 12, "opponent_id": 34 }`. The opponent receives an in-app notification and can accept or reject with `PATCH /api/duels/{duel_id}/respond` and `{ "action": "accept" }` or `{ "action": "reject" }`. The challenger can cancel a pending invite at `/api/duels/{duel_id}/cancel`. Both readers must have passed a published quiz for that book, and duplicate active invitations are prevented. These endpoints and request/response schemas are available in Swagger.

Readers can send product improvement suggestions from Android with `POST /api/feedback` as `multipart/form-data`, including required `title` and `description`, optional `category` (`app`, `books`, `quizzes`, `community`, or `other`), and an optional `attachment` (PDF, image, TXT, DOC, or DOCX up to 10 MB). `GET /api/feedback` lists the signed-in reader's submissions, and `GET /api/feedback/{idea}` returns its latest review status. The attachment URL returned by the API requires the same bearer token and is available only to its owner or an administrator. Administrators can review submissions, update their status, add private internal notes, and download attachments at Admin > Reader ideas. All endpoints are documented in Swagger under Feedback.

Quiz submission returns `score` as a percentage from 0 to 100, `passed`, the saved `attempt_id`, `attempts_used`, and `review_status`. For `written_response`, score and pass state are null until an administrator reviews the response. Submit one or more selected answer IDs for choice questions and a string for text questions.

Quiz results, duel invitations and responses, and show RSVP confirmations are delivered through in-app notifications and each globally enabled channel: email, SMS, and Android push. Reader in-app preferences only control the in-app copy. Email, Firebase, and Flex SMS delivery can be enabled and edited in Admin → Settings; SMTP passwords, Firebase service-account JSON, and SMS client secrets are encrypted in the database and never shown again in the settings form. Firebase push requires the Android app to register its current FCM token with `PUT /api/push-token` using the signed-in bearer token; unregister it with `DELETE /api/push-token` on sign-out. The Firebase service-account JSON is configured by an administrator in the portal and is not sent to Android clients.

## Android networking notes

- Keep the API base URL in the Android build configuration so emulator, device, and production builds can use different hosts.
- Store the bearer token in Android's secure storage and clear it after `/auth/logout` succeeds.
- Use HTTPS in production. Android cleartext HTTP is suitable only for local development configurations.
- Image and PDF URLs use `APP_URL`; Android should open or download those absolute URLs directly.
- Configure a working mail transport to deliver password reset links. In local development, Laravel's `log` mailer writes the reset message to the application log.
