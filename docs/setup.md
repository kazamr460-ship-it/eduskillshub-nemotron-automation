# Setup

## Prerequisites

- A [Make.com](https://www.make.com) account (free tier works).
- A [Nebius Token Factory](https://tokenfactory.nebius.com/) account and API
  key. Hackathon participants can join the Nebius Builder Program for credits.
- Admin access to eduskillshub.site (WordPress) to install the plugin file.

## 1. Import the Make.com scenario

1. In Make.com, go to **Scenarios → Create a scenario → Import Blueprint**.
2. Select `make-scenario/blueprint.json` from this repo.
3. The four modules (Webhook → Nebius HTTP call → Set variable → WordPress
   HTTP call) will appear pre-wired.

## 2. Configure the Nebius Token Factory credential

1. Open module 2 ("HTTP – Make a request", pointed at
   `api.tokenfactory.nebius.com`).
2. Under **Headers**, find `Authorization` and replace the placeholder value
   with `Bearer <your real Nebius API key>`.
3. Optionally change the `model` field in the request body to a different
   Nemotron variant (see `docs/architecture.md`).

## 3. Install the WordPress endpoint

1. Copy `wordpress-plugin/eduskills-ingest-endpoint.php` into
   `wp-content/plugins/eduskills-ingest-endpoint/` on eduskillshub.site (or
   merge its contents into an existing custom plugin/theme functions file if
   `/wp-json/eduskills/v1/ingest` already exists there).
2. Activate the plugin from **Plugins** in wp-admin.
3. **Before going live**, replace the placeholder `permission_callback` and
   storage logic with real auth and real course/listing update logic — see
   the `TODO` comments in the file.

## 4. Get the webhook URL

1. Open module 1 ("Webhooks – Custom webhook") in the Make.com scenario.
2. Copy the **Webhook URL** shown there.
3. Wire that URL into whatever should trigger content generation (an admin
   button, a script, a cron job, etc.).

## 5. Test end-to-end

1. Click **Run once** in Make.com so the scenario listens for one execution.
2. Send a test request:
   ```bash
   curl -X POST "<your webhook URL>" \
     -H "Content-Type: application/json" \
     -d '{"prompt": "Write a two-sentence description for a beginner Python course."}'
   ```
3. Confirm all four modules complete without errors, and that
   `eduskillshub_last_ai_content` (or your real storage target) is updated on
   the WordPress side.

## 6. Lock it down before real use

- Add API-key auth to the Make.com webhook (**Webhook → Show advanced
  settings → API Key authentication**).
- Replace the WordPress route's `permission_callback` with a real check (a
  shared secret header, or a logged-in capability check).
- Include the target course/listing ID in the webhook payload so the
  WordPress handler updates the correct record instead of a placeholder
  option.
