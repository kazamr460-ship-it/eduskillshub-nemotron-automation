# Architecture

The automation is built as a four-module Make.com scenario (exported to
[`../make-scenario/blueprint.json`](../make-scenario/blueprint.json)).

## 1. Webhooks — Custom webhook (trigger)

- Type: Make.com "Custom webhook".
- Accepts a POST with a JSON body: `{ "prompt": "<what to generate>" }`.
- Anything on eduskillshub.site (or an admin tool, or a script) that needs
  AI-generated copy calls this URL to kick off the pipeline.

## 2. HTTP — Make a request (Nebius Token Factory)

- `POST https://api.tokenfactory.nebius.com/v1/chat/completions`
- Header: `Authorization: Bearer <NEBIUS_API_KEY>`
- Body:
  ```json
  {
    "model": "nvidia/nemotron-3-super-120b-a12b",
    "messages": [
      { "role": "user", "content": "<prompt from step 1>" }
    ]
  }
  ```
- This is an OpenAI-compatible chat completion call. The `model` field is the
  only thing you change to move between Nemotron sizes:
  - `nvidia/nemotron-3-nano-30b` / a Lightning variant — fast, cheap, short copy.
  - `nvidia/nemotron-3-super-120b-a12b` — default; good balance for course/listing copy.
  - `nvidia/nemotron-3-ultra-550b-a55b` — heavier reasoning, for long-form or nuanced copy.

## 3. Tools — Set variable

- Extracts `choices[0].message.content` from the Nebius response into a
  variable named `generated_content`, so the next module doesn't need to know
  about the raw API response shape.

## 4. HTTP — Make a request (publish to eduskillshub.site)

- `POST https://eduskillshub.site/wp-json/eduskills/v1/ingest`
- Body: `{ "source": "gemini-automation", "content": "<generated_content>" }`
  (the `source` label is left over from an earlier prototype name and can be
  renamed to `"nebius-nemotron-automation"`).
- Handled on the WordPress side by
  [`../wordpress-plugin/eduskills-ingest-endpoint.php`](../wordpress-plugin/eduskills-ingest-endpoint.php).

## Data flow at a glance

```
POST {prompt} ─▶ [1. Webhook] ─▶ [2. Nebius Token Factory / Nemotron] ─▶ [3. Extract text] ─▶ [4. POST to eduskillshub.site] ─▶ stored on site
```

## Known gaps / next steps

- The WordPress handler currently stores only the *most recent* generated
  item (`eduskills_last_ai_content` option) as a placeholder. Before this is
  used for real course listings, the payload needs a course/listing ID so the
  handler can update the right record.
- No authentication is enforced yet on either the Make.com webhook or the
  WordPress route — both use permissive defaults so the pipeline could be
  tested end-to-end quickly. See `docs/setup.md` for how to lock this down.
