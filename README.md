# FindBSB API

Free JSON API for Australian BSB number lookups. No API key required. No signup.

**Base URL:** `https://findbsb.com.au/api`

[![Featured on Product Hunt](https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=findbsb&theme=light)](https://www.producthunt.com/posts/findbsb)

→ [Full documentation](https://findbsb.com.au/api) · [BSB Lookup Tool](https://findbsb.com.au) · [RapidAPI](https://rapidapi.com/findbsb/api/findbsb-australian-bsb-lookup)

---

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/bsb/{bsb}` | Single BSB lookup |
| GET | `/api/bsb?bank=ANZ&state=NSW` | Filter by bank, state, suburb, postcode |
| POST | `/api/validate` | Bulk validate up to 500 BSBs |

## Quick Start

```bash
# Single lookup
curl https://findbsb.com.au/api/bsb/062-000

# Filter
curl "https://findbsb.com.au/api/bsb?bank=ANZ&state=NSW&limit=5"

# Bulk validate
curl -X POST https://findbsb.com.au/api/validate \
  -H "Content-Type: application/json" \
  -d '{"bsbs": ["062-000", "012-003", "999-999"]}'
```

## Response

```json
{
  "bsb": "062-000",
  "mnemonic": "CBA",
  "bank": "Commonwealth Bank of Australia",
  "branch": "48 Martin Place Sydney",
  "address": "48 Martin Place",
  "suburb": "Sydney",
  "state": "NSW",
  "postcode": "2000",
  "payments": ["paper", "electronic", "cash"],
  "closed": false
}
```

## Examples

| Language | File |
|----------|------|
| Python | [examples/python.py](examples/python.py) |
| JavaScript | [examples/javascript.js](examples/javascript.js) |
| PHP | [examples/php.php](examples/php.php) |

## Rate Limits

- 30 requests per minute per IP
- 500 BSBs per validate request
- Free, no auth required

## Data Source

BSB data sourced from [AusPayNet](https://auspaynet.com.au) and updated on the first business day of each month.

## License

MIT
