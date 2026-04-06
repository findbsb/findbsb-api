/**
 * FindBSB API — JavaScript examples
 * https://findbsb.com.au/api
 *
 * Works in browser and Node.js 18+
 */

const BASE_URL = 'https://findbsb.com.au/api'

// ── Single BSB lookup ────────────────────────────────────────────────────────

async function lookupBSB(bsb) {
  const res = await fetch(`${BASE_URL}/bsb/${encodeURIComponent(bsb)}`)
  if (res.status === 404) throw new Error(`BSB ${bsb} not found`)
  if (res.status === 400) throw new Error(`Invalid BSB format: ${bsb}`)
  if (!res.ok) throw new Error(`API error: ${res.status}`)
  return res.json()
}

// ── Bulk validation ──────────────────────────────────────────────────────────

async function validateBSBs(bsbList) {
  const res = await fetch(`${BASE_URL}/validate`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ bsbs: bsbList }),
  })
  if (!res.ok) throw new Error(`API error: ${res.status}`)
  return res.json()
}

// ── Filter by bank / state / suburb / postcode ───────────────────────────────

async function filterBSBs({ bank, state, suburb, postcode, limit = 100 } = {}) {
  const params = new URLSearchParams({ limit })
  if (bank)     params.set('bank', bank)
  if (state)    params.set('state', state)
  if (suburb)   params.set('suburb', suburb)
  if (postcode) params.set('postcode', postcode)
  const res = await fetch(`${BASE_URL}/bsb?${params}`)
  if (!res.ok) throw new Error(`API error: ${res.status}`)
  return res.json()
}

// ── Examples ─────────────────────────────────────────────────────────────────

async function main() {
  // Single lookup
  const bsb = await lookupBSB('062-000')
  console.log(`${bsb.bank} — ${bsb.branch}, ${bsb.suburb} ${bsb.state}`)

  // Bulk validate
  const result = await validateBSBs(['062-000', '012-003', '999-999'])
  console.log(`Valid: ${result.valid}, Invalid: ${result.invalid}, Closed: ${result.closed}`)
  result.results.forEach(r => {
    const status = r.valid ? '✓' : '✗'
    const closed = r.closed ? ' (CLOSED)' : ''
    console.log(`  ${status} ${r.bsb} — ${r.bank ?? 'N/A'}${closed}`)
  })

  // Filter CBA branches in VIC
  const cbaVic = await filterBSBs({ bank: 'CBA', state: 'VIC', limit: 5 })
  console.log(`\nCBA VIC branches (${cbaVic.total} total):`)
  cbaVic.results.forEach(r => console.log(`  ${r.bsb} — ${r.branch}, ${r.suburb}`))
}

main().catch(console.error)

// ── ES module export (for use as a library) ──────────────────────────────────

export { lookupBSB, validateBSBs, filterBSBs }
