# EduSkillsHub AI Content Engine

**Nebius x NVIDIA Global AI Hackathon — Best Apps and Agents Track**

An always-on content automation for [eduskillshub.site](https://eduskillshub.site), an online course/skills marketplace. A single webhook call kicks off a workflow that generates marketplace copy (course descriptions, listing summaries, promotional blurbs) with an **NVIDIA Nemotron** model served through **Nebius Token Factory**, then publishes the result straight into the site via its WordPress REST API.

> Significantly updated during the Hackathon Submission Period (started Sept 2026): the automation was re-architected to run on Nebius Token Factory with an NVIDIA Nemotron model, replacing an earlier prototype that called a different provider's API.

## What it does

1. A trigger (new course draft, listing update, or a manual request from the EduSkillsHub admin dashboard) sends a JSON payload — `{ "prompt": "..." }` — to a webhook.
2. The workflow calls **Nebius Token Factory's** OpenAI-compatible `chat/completions` endpoint, using an **NVIDIA Nemotron 3** model, to generate the requested content.
3. The generated text is extracted and reshaped into the payload the site expects.
4. The workflow POSTs the result to a custom WordPress REST route (`/wp-json/eduskills/v1/ingest`) registered on eduskillshub.site, which stores it against the relevant course/listing.

This turns a manual copywriting step in the EduSkillsHub course-publishing flow into a self-running pipeline: a course creator (or another part of the site) fires the webhook, and finished marketing copy lands back on the site with no human in the loop.

## Why Nebius Token Factory + NVIDIA Nemotron

- **Nebius Token Factory** is used as the runtime inference provider: every generation call is a live request to `api.tokenfactory.nebius.com`, billed per-token, with no model hosting or GPU management on our side.
- **NVIDIA Nemotron 3** (the `nvidia/nemotron-3-super-120b-a12b` model by default) handles the actual generation — chosen because course/listing copy needs solid everyday reasoning and tone control without the latency or cost of a much larger model. Swapping to `nemotron-3-ultra-550b-a55b` for harder copywriting tasks, or a Nano/Lightning variant for high-volume short blurbs, is a one-line change in the workflow (see [`make-scenario/blueprint.json`](make-scenario/blueprint.json)).

## Architecture

```
eduskillshub.site  ──POST {prompt}──▶  Webhook trigger
                                            │
                                            ▼
                              Nebius Token Factory
                              (NVIDIA Nemotron 3, /v1/chat/completions)
                                            │
                                            ▼
                                Extract generated text
                                            │
                                            ▼
                         WordPress REST route on eduskillshub.site
                            (/wp-json/eduskills/v1/ingest)
```

See [`docs/architecture.md`](docs/architecture.md) for the full module-by-module breakdown.

## Repository contents

| Path | What it is |
|---|---|
| `make-scenario/blueprint.json` | Exported Make.com scenario blueprint — the actual automation, importable directly into Make. |
| `wordpress-plugin/eduskills-ingest-endpoint.php` | The WordPress plugin snippet that registers the `/wp-json/eduskills/v1/ingest` REST route on eduskillshub.site and stores incoming content. |
| `docs/architecture.md` | Module-by-module explanation of the workflow. |
| `docs/setup.md` | Step-by-step setup and configuration instructions. |
| `.env.example` | Names of the environment values/secrets the project needs (no real values). |
| `LICENSE` | MIT License. |

## Quick start

1. **Get a Nebius Token Factory API key** — sign up at [Nebius AI Studio / Token Factory](https://tokenfactory.nebius.com/) (hackathon participants can join the Nebius Builder Program for credits) and generate an API key.
2. **Import the automation** — in Make.com, create a scenario and choose *Import Blueprint*, then select `make-scenario/blueprint.json`.
3. **Configure the Nebius credential** — open the "HTTP – Make a request" module that calls `api.tokenfactory.nebius.com` and paste your API key into the `Authorization: Bearer <key>` header.
4. **Install the WordPress endpoint** — add `wordpress-plugin/eduskills-ingest-endpoint.php` as a plugin (or drop its contents into your theme's `functions.php`) on eduskillshub.site.
5. **Copy the webhook URL** from the Make.com trigger module and use it wherever course/listing generation should be triggered from.
6. **Run once** in Make.com to confirm the full chain (webhook → Nemotron → WordPress) works end to end.

Full details are in [`docs/setup.md`](docs/setup.md).

## Nebius Token Factory / NVIDIA feedback

This section documents our experience using Nebius Token Factory and NVIDIA Nemotron during the hackathon, as requested in the submission requirements. *(To be filled in after we've run the workflow against real course data — see `docs/setup.md` for where to add notes.)*

## License

MIT — see [`LICENSE`](LICENSE).
