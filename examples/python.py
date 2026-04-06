"""
FindBSB API — Python examples
https://findbsb.com.au/api
"""
import requests

BASE_URL = 'https://findbsb.com.au/api'
HEADERS = {'User-Agent': 'MyApp/1.0'}


# ── Single BSB lookup ────────────────────────────────────────────────────────

def lookup_bsb(bsb: str) -> dict:
    response = requests.get(f'{BASE_URL}/bsb/{bsb}', headers=HEADERS)
    if response.status_code == 404:
        raise ValueError(f'BSB {bsb} not found')
    if response.status_code == 400:
        raise ValueError(f'Invalid BSB format: {bsb}')
    response.raise_for_status()
    return response.json()


# ── Bulk validation ──────────────────────────────────────────────────────────

def validate_bsbs(bsb_list: list[str]) -> dict:
    """Validate up to 500 BSBs in a single request."""
    response = requests.post(
        f'{BASE_URL}/validate',
        json={'bsbs': bsb_list},
        headers=HEADERS,
    )
    response.raise_for_status()
    return response.json()


# ── Filter by bank / state / suburb / postcode ───────────────────────────────

def filter_bsbs(bank=None, state=None, suburb=None, postcode=None, limit=100) -> dict:
    params = {'limit': limit}
    if bank:     params['bank'] = bank
    if state:    params['state'] = state
    if suburb:   params['suburb'] = suburb
    if postcode: params['postcode'] = postcode
    response = requests.get(f'{BASE_URL}/bsb', params=params, headers=HEADERS)
    response.raise_for_status()
    return response.json()


# ── Clean BSB column in a pandas DataFrame ───────────────────────────────────

def clean_bsb_column(df, bsb_col: str):
    """Add bsb_valid, bsb_closed, bsb_bank columns to a DataFrame."""
    import pandas as pd
    bsbs = df[bsb_col].tolist()
    data = validate_bsbs(bsbs)
    lookup = {r['bsb']: r for r in data['results']}
    df['bsb_valid']  = df[bsb_col].map(lambda b: lookup.get(b, {}).get('valid', False))
    df['bsb_closed'] = df[bsb_col].map(lambda b: lookup.get(b, {}).get('closed', False))
    df['bsb_bank']   = df[bsb_col].map(lambda b: lookup.get(b, {}).get('bank', ''))
    return df


# ── Examples ─────────────────────────────────────────────────────────────────

if __name__ == '__main__':
    # Single lookup
    result = lookup_bsb('062-000')
    print(f"{result['bank']} — {result['branch']}, {result['suburb']} {result['state']}")

    # Bulk validate
    result = validate_bsbs(['062-000', '012-003', '999-999'])
    print(f"Valid: {result['valid']}, Invalid: {result['invalid']}, Closed: {result['closed']}")
    for r in result['results']:
        status = '✓' if r['valid'] else '✗'
        closed = ' (CLOSED)' if r.get('closed') else ''
        print(f"  {status} {r['bsb']} — {r.get('bank', 'N/A')}{closed}")

    # Filter ANZ branches in NSW
    result = filter_bsbs(bank='ANZ', state='NSW', limit=5)
    print(f"\nANZ NSW branches ({result['total']} total):")
    for r in result['results']:
        print(f"  {r['bsb']} — {r['branch']}, {r['suburb']}")
