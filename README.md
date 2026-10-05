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

Swagger lists every registered Android API operation. Select Production, Local development, or Android emulator as the server at the top of the page. The production base URL is `https://arenayakusoma.eportsolutions.co.tz/api`.

All API paths below are relative to the `/api` base URL. Except for authentication and documentation routes, send the token returned by register or login on every request:

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
| GET, PATCH | `/profile` | Read or update name, email, and phone number |
| POST | `/profile/photo` | Upload `profile_photo` as multipart form data |
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
| GET | `/show-applications` | List guest applications and their review statuses |
| POST | `/shows/{show}/applications` | Apply as a show guest with a short motivation statement |
| DELETE | `/show-applications/{application}` | Withdraw a pending guest application |
| GET, POST | `/duels` | List duel records or challenge a reader verified for the same book |
| PATCH | `/duels/{duel}/respond` | Accept or reject an invitation as the invited reader |
| PATCH | `/duels/{duel}/cancel` | Cancel an invitation as its challenger |
| GET | `/leaderboard?period=weekly` | Read daily, weekly, monthly, or all-time verified quiz rankings |
| GET | `/quizzes/{quiz}` | Load a published quiz and this reader's attempt count |
| POST | `/quizzes/{quiz}/submit` | Submit `{ "answers": { "QUESTION_ID": [ANSWER_ID, ...] } }`; a single integer remains accepted for single-choice questions |

### Make portal quiz questions available to Android

Questions are stored in the same database the API reads, so they do not need a separate push or sync. For Android access, publish the related book first, then publish its quiz. Questions created by an author remain in a draft quiz until an administrator publishes it.

After signing in and adding the returned Sanctum token as a bearer token, the app can call `GET /api/books` for book summaries, `GET /api/books/{book_id}` for a book and its published quizzes, or `GET /api/quizzes/{quiz_id}` to load one quiz directly. These responses include aggregate quiz activity and performance for the book and each quiz. Each question includes its ID and answer choices; `allow_multiple_selection` tells the app to render multi-select controls. Draft or unpublished books and quizzes are intentionally omitted or return `404`.

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

Reader content may be saved as a private draft without quiz verification. Publishing a lesson, recommendation, duel challenge, or show guest application requires a passing attempt for the same published book. Duel invitations support pending, accepted, rejected, and cancelled states. Guest application quiz scores are derived from the reader's best passing attempt, never accepted from client input. The leaderboard adds the best passing score once per published quiz in the chosen period.

Quiz submission returns `score` as a percentage from 0 to 100, `passed`, the saved `attempt_id`, and `attempts_used`. Quiz answer maps use question IDs as object keys and one or more selected answer IDs as values.

In-app notifications are created for quiz results, duel invitations and responses, and show RSVP confirmations. Notification preferences can suppress each supported type. Email and push delivery are not wired to these API events yet.

## Android networking notes

- Keep the API base URL in the Android build configuration so emulator, device, and production builds can use different hosts.
- Store the bearer token in Android's secure storage and clear it after `/auth/logout` succeeds.
- Use HTTPS in production. Android cleartext HTTP is suitable only for local development configurations.
- Image and PDF URLs use `APP_URL`; Android should open or download those absolute URLs directly.
- Configure a working mail transport to deliver password reset links. In local development, Laravel's `log` mailer writes the reset message to the application log.
