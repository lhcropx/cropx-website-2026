#!/usr/bin/env python3
"""
CropX Photo Optimization Pipeline
===================================
Usage:
    python3 optimize-photos.py

Reads source images from:  raw-photos/   (any depth, subfolders OK)
Reads SEO filename map from: raw-photos/rename-map.json  (optional but recommended)
Outputs optimized files to:  assets/images/photos/

── rename-map.json ──────────────────────────────────────────────────────────
Optional file that maps source filenames to SEO-optimized output slugs.
If a source file isn't in the map, the script falls back to slugifying the
original filename. Generate this file by running:

    python3 optimize-photos.py --scan

That prints a JSON template with all found source files. Fill in the "out"
fields with SEO-friendly names, or let Claude do it by passing the photos
through vision analysis first.

Format:
    {
      "almond orchard drone.jpg": "almond-orchard-aerial-california",
      "IMG_4821.JPG":             "agronomist-checking-soil-sensor-vineyard",
      "photo (3).png":            "wheat-field-harvest-ukraine"
    }

Values must be lowercase slugs only (a-z, 0-9, hyphens) — no extension.

── Output sizes per image (WebP) ────────────────────────────────────────────
    full      — max 2400px wide, quality 85  → hero / full-bleed
    768w      — 768px wide, quality 82        → tablet srcset
    480w      — 480px wide, quality 80        → mobile srcset
    thumb     — 400px wide, quality 75        → cards / thumbnails
    + one JPG fallback at full size (quality 88)
"""

import json
import os
import re
import sys
from pathlib import Path
from PIL import Image

# Raise the decompression bomb limit for large high-res photos
Image.MAX_IMAGE_PIXELS = 400_000_000

# ── Config ───────────────────────────────────────────────────────────────────
SOURCE_DIR  = Path(__file__).parent / 'raw-photos'
OUTPUT_DIR  = Path(__file__).parent / 'assets' / 'images' / 'photos'
MAP_FILE    = SOURCE_DIR / 'rename-map.json'
SOURCE_EXTS = {'.jpg', '.jpeg', '.png', '.webp', '.tiff', '.tif', '.bmp'}

SIZES = [
    ('full',  2400, 85),
    ('768w',   768, 82),
    ('480w',   480, 80),
    ('thumb',  400, 75),
]
JPG_QUALITY = 88

# ── Helpers ───────────────────────────────────────────────────────────────────
def slugify(name):
    name = name.lower()
    name = re.sub(r'[\s_]+', '-', name)
    name = re.sub(r'[^a-z0-9\-]', '', name)
    name = re.sub(r'-{2,}', '-', name)
    return name.strip('-')

def resize(img, max_width):
    w, h = img.size
    if w <= max_width:
        return img
    new_h = int(h * max_width / w)
    return img.resize((max_width, new_h), Image.LANCZOS)

def open_as_rgb(src_path):
    img = Image.open(src_path)
    if img.mode in ('P', 'RGBA', 'LA'):
        img = img.convert('RGBA')
        bg = Image.new('RGB', img.size, (255, 255, 255))
        bg.paste(img, mask=img.split()[3])
        return bg
    return img.convert('RGB')

def process(src_path, out_stem):
    try:
        img = open_as_rgb(src_path)
    except Exception as e:
        print(f'  ✗ Cannot open: {e}')
        return False

    for suffix, max_w, quality in SIZES:
        resized = resize(img, max_w)
        out_name = f'{out_stem}.webp' if suffix == 'full' else f'{out_stem}-{suffix}.webp'
        out_path = OUTPUT_DIR / out_name
        if out_path.exists():
            print(f'  → {out_name} (skipped — already exists)')
        else:
            resized.save(out_path, 'WEBP', quality=quality, method=6)
            kb = out_path.stat().st_size // 1024
            w, h = resized.size
            print(f'  → {out_name}  ({w}×{h}, {kb} KB)')

    jpg_path = OUTPUT_DIR / f'{out_stem}.jpg'
    if jpg_path.exists():
        print(f'  → {out_stem}.jpg (skipped — already exists)')
    else:
        full_jpg = resize(img, SIZES[0][1])
        full_jpg.save(jpg_path, 'JPEG', quality=JPG_QUALITY, optimize=True, progressive=True)
        kb = jpg_path.stat().st_size // 1024
        w, h = full_jpg.size
        print(f'  → {out_stem}.jpg  ({w}×{h}, {kb} KB)  [JPG fallback]')

    return True

def find_sources():
    return sorted([
        p for p in SOURCE_DIR.rglob('*')
        if p.is_file()
        and p.suffix.lower() in SOURCE_EXTS
        and p.name != 'rename-map.json'
    ])

# ── --scan mode: print a rename-map template ─────────────────────────────────
def scan_mode():
    sources = find_sources()
    if not sources:
        print(f'\n✗  No images found in {SOURCE_DIR}\n')
        sys.exit(1)

    print(f'\nFound {len(sources)} image(s). Template rename-map.json:\n')
    mapping = {}
    for src in sources:
        fallback = slugify(src.stem)
        mapping[src.name] = fallback

    print(json.dumps(mapping, indent=2, ensure_ascii=False))
    print(f'\nTo use: save this JSON to {MAP_FILE}')
    print('Then replace the values with SEO-optimized slugs and run without --scan.\n')

# ── Main ──────────────────────────────────────────────────────────────────────
def main():
    if '--scan' in sys.argv:
        scan_mode()
        return

    if not SOURCE_DIR.exists():
        print(f'\n✗  Source folder not found: {SOURCE_DIR}')
        print('   Create raw-photos/ and add your images first.\n')
        sys.exit(1)

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    # Load rename map if present
    rename_map = {}
    if MAP_FILE.exists():
        with open(MAP_FILE) as f:
            raw_map = json.load(f)
        # Normalise keys to lowercase for case-insensitive lookup
        rename_map = {k.lower(): v for k, v in raw_map.items()}
        print(f'\n📋  Loaded rename-map.json ({len(rename_map)} entries)')
    else:
        print('\n⚠️   No rename-map.json found — using slugified original filenames.')
        print('    Run with --scan to generate a template, or ask Claude to analyse your photos.\n')

    sources = find_sources()
    if not sources:
        print(f'✗  No images found in {SOURCE_DIR}\n')
        sys.exit(1)

    print(f'📷  {len(sources)} source image(s) to process\n')

    ok = fail = 0
    seen_stems = {}

    for src in sources:
        # Look up SEO name from map (case-insensitive on filename)
        mapped = rename_map.get(src.name.lower())
        if mapped:
            out_stem = slugify(mapped)
            label = f'[mapped] {out_stem}'
        else:
            out_stem = slugify(src.stem)
            label = f'[auto]   {out_stem}'

        # Collision guard
        original_stem = out_stem
        counter = 2
        while out_stem in seen_stems and seen_stems[out_stem] != str(src):
            out_stem = f'{original_stem}-{counter}'
            counter += 1
        seen_stems[out_stem] = str(src)

        print(f'[{ok + fail + 1}/{len(sources)}]  {src.name}')
        print(f'          {label}')

        if process(src, out_stem):
            ok += 1
        else:
            fail += 1
        print()

    webp_count = len(list(OUTPUT_DIR.glob('*.webp')))
    print(f'✅  Done — {ok} processed, {fail} failed')
    print(f'   {webp_count} WebP files total in assets/images/photos/\n')

if __name__ == '__main__':
    main()
