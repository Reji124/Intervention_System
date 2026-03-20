#!/usr/bin/env python3
# scripts/parse_item_matrix.py
#
# Usage: python parse_item_matrix.py /path/to/matrix.pdf
# Output: JSON to stdout, errors to stderr

import sys
import re
import json

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No PDF path provided'}))
        sys.exit(1)

    pdf_path = sys.argv[1]

    try:
        import pdfplumber
    except ImportError:
        print(json.dumps({'error': 'pdfplumber not installed. Run: pip install pdfplumber'}))
        sys.exit(1)

    # Column x-boundaries — midpoints between header label x0 positions
    # Derived from actual PDF header word positions:
    #   <.00=121.8  .00-.14=210.7  .15-.24=304.3  .25-.29=397.9
    #   .30-.44=491.5  .45+=571.2  Total=652.9
    COL_BOUNDARIES = [
        ('<.00',            0,     166.2),
        ('.00-.14',       166.2,   257.5),
        ('.15-.24',       257.5,   351.1),
        ('.25-.29',       351.1,   444.7),
        ('.30-.44',       444.7,   531.4),
        ('.45 and above', 531.4,   652.0),
    ]

    DIFFICULTY_BANDS = ['81-100%', '61-80%', '41-60%', '21-40%', '0-20%']

    def assign_col(x0):
        for name, lo, hi in COL_BOUNDARIES:
            if lo <= x0 < hi:
                return name
        return None

    try:
        with pdfplumber.open(pdf_path) as pdf:
            words = pdf.pages[0].extract_words()
    except Exception as e:
        print(json.dumps({'error': 'Could not open PDF: ' + str(e)}))
        sys.exit(1)

    # ── Meta ─────────────────────────────────────────────────────────────────
    title = ''
    for w in words:
        if '[' in w['text'] and '-202' in w['text'] and w['x0'] > 100:
            t = w['top']
            title = ' '.join(x['text'] for x in words if abs(x['top'] - t) < 3 and x['x0'] > 100)
            break

    module = ''
    for w in words:
        if w['text'] == 'Module' and w['x0'] > 100:
            t = w['top']
            module = ' '.join(x['text'] for x in words if abs(x['top'] - t) < 3 and x['x0'] > 100)
            break

    date_str = ''
    days = ('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
    for w in words:
        if w['text'] in days and w['x0'] < 100:
            t = w['top']
            date_str = ' '.join(x['text'] for x in words if abs(x['top'] - t) < 3 and x['x0'] < 200)
            break

    # ── Band label tops ───────────────────────────────────────────────────────
    band_tops = {}
    for w in words:
        for band in DIFFICULTY_BANDS:
            if w['text'] == band and w['x0'] < 80:
                band_tops[band] = w['top']

    sorted_bands = sorted(DIFFICULTY_BANDS, key=lambda b: band_tops.get(b, 9999))

    # 'Total' appears twice — header (x~652) and footer row (x<80). We want footer only.
    total_top = next(
        (w['top'] for w in words if w['text'] == 'Total' and w['x0'] < 80),
        9999
    )

    # ── Row vertical bounds ───────────────────────────────────────────────────
    # Start 8px ABOVE each band label to catch items rendered on the line above it
    row_bounds = []
    for i, band in enumerate(sorted_bands):
        t     = band_tops.get(band, 0)
        start = t - 8
        end   = (band_tops.get(sorted_bands[i + 1], total_top) - 8
                 if i + 1 < len(sorted_bands) else total_top)
        row_bounds.append((band, start, end))

    # ── Cell extraction ───────────────────────────────────────────────────────
    cells = {b: {col: [] for col, _, _ in COL_BOUNDARIES} for b in DIFFICULTY_BANDS}

    for band, start, end in row_bounds:
        for w in words:
            if not (start <= w['top'] < end):
                continue
            if w['x0'] < 80 or w['x0'] >= 652:   # skip row label & Total column
                continue
            col = assign_col(w['x0'])
            if col is None:
                continue
            nums = re.findall(r'\d+', w['text'])
            cells[band][col].extend(int(n) for n in nums)

    for band in cells:
        for col in cells[band]:
            cells[band][col] = sorted(cells[band][col])

    # ── Row totals ────────────────────────────────────────────────────────────
    row_totals = {b: sum(len(v) for v in cells[b].values()) for b in DIFFICULTY_BANDS}

    # ── Column totals (from the footer Total row) ─────────────────────────────
    col_totals = {col: 0 for col, _, _ in COL_BOUNDARIES}
    for w in words:
        if abs(w['top'] - total_top) >= 4:
            continue
        if w['x0'] < 80 or w['x0'] >= 652:
            continue
        col = assign_col(w['x0'])
        if col and re.match(r'^\d+$', w['text']):
            col_totals[col] = int(w['text'])

    # Grand total
    grand_total = 0
    for w in words:
        if abs(w['top'] - total_top) < 4 and w['x0'] >= 652 and re.match(r'^\d+$', w['text']):
            grand_total = int(w['text'])
            break
    if grand_total == 0:
        grand_total = sum(col_totals.values())

    print(json.dumps({
        'title':       title,
        'module':      module,
        'date':        date_str,
        'cells':       cells,
        'row_totals':  row_totals,
        'col_totals':  col_totals,
        'total_items': grand_total,
    }))

if __name__ == '__main__':
    main()