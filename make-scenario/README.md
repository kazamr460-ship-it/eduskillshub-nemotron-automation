# Make.com scenario blueprint

`blueprint.json` is a hand-verified equivalent of the scenario built in Make.com
for this project (Webhook → Nebius Token Factory/Nemotron → Set variable →
WordPress ingest). It contains every module's real configuration (URLs,
headers, model name, body mapping) and imports directly via **Make.com →
Create a scenario → Import Blueprint**.

It omits Make's auto-regenerated UI metadata (parameter/RPC descriptions),
which Make reconstructs automatically for known module types on import. If
you ever hit an import issue, the safest fallback is to open the live
scenario in Make.com and use **⋮ → Export blueprint** to get a byte-for-byte
export, replacing this file.

Remember to paste your own Nebius API key into module 2's `Authorization`
header after importing — it ships with a placeholder, not a real key.
