import re, sys

"""Membuat theme-green.css: menyalin deklarasi bermuatan warna dari
material-dashboard.css lalu menukar warna tema ke palette hijau SI-ABDI.

Jalankan: python3 tools/generate-theme.py public/assets/css/theme-green.css
"""

SRC = "/Users/yogawakamenta/Documents/Latsar/siabdi/public/assets/css/material-dashboard.css"

# Peta warna Material Dashboard -> palette hijau
HEX = {
    # primary (ungu) -> hijau olive
    '9c27b0': '386C0B', '8e24aa': '293F14', 'ab47bc': '38A700',
    # info (cyan) -> hijau paling gelap
    '00bcd4': '293F14', '00acc1': '293F14', '26c6da': '386C0B',
    # success (hijau material) -> hijau cerah palette
    '4caf50': '38A700', '43a047': '386C0B', '66bb6a': '31D843',
    # primary bawaan bootstrap (biru) -> hijau olive
    '2196f3': '386C0B',

    # --- Varian hover / focus / active ---
    # Material Dashboard menyimpan shade gelap/terang tiap warna sebagai hex
    # tersendiri, sehingga tidak ikut tertukar oleh pemetaan warna dasar di atas.
    # Semuanya diarahkan ke tingkat palette terdekat.
    # primary (ungu)
    '9124a3': '293F14', '701c7e': '293F14', '3f1048': '293F14',
    '89229b': '293F14', 'a72abd': '38A700',
    # primary bawaan bootstrap (biru)
    '0c7cd5': '293F14', '0b75c9': '293F14', '0c83e2': '293F14',
    '0a6ebd': '293F14', '9acffa': '3EFF8B',
    # info (cyan)
    '00aec5': '386C0B', '008697': '386C0B', '008fa1': '386C0B',
    '009aae': '386C0B', '008394': '386C0B', '00a5bb': '386C0B',
    '00cae3': '38A700', '08e3ff': '31D843', '55ecff': '31D843',
    'a2e6ef': '3EFF8B', 'b8ecf3': '3EFF8B',
    '00626e': '293F14', '004b55': '293F14', '00353b': '293F14',
    # success (hijau material)
    '47a44b': '386C0B', '39843c': '386C0B', '3d8b40': '386C0B',
    '39833c': '386C0B', '409444': '386C0B',
    '285b2a': '293F14', '255627': '293F14', '18381a': '293F14',
    '6ec071': '31D843', '55b559': '31D843', 'a3d7a5': '3EFF8B',
}

# Sengaja TIDAK dipetakan:
#   - abu-abu gelap (#212529, #343a40, #3c4858, ...) -> warna teks/netral
#   - merah, oranye, rose -> penanda semantik (hapus, error, peringatan)
#   - #3b5998/#55acee dsb -> warna brand Facebook & Twitter
#   - #9368e9, #18ce0f, #2ca8ff, #00bbff -> swatch widget .fixed-plugin (nonaktif)
RGB = {
    '156, 39, 176': '56, 108, 11',    # primary
    '0, 188, 212': '41, 63, 20',      # info
    '76, 175, 80': '56, 167, 0',      # success
    '33, 150, 243': '56, 108, 11',    # biru bootstrap
    '154, 207, 250': '62, 255, 139',  # cincin focus .form-control
}

def strip_comments(css):
    return re.sub(r'/\*.*?\*/', '', css, flags=re.S)

def parse(css):
    """Kembalikan daftar (context_stack, selector, declarations) untuk rule terdalam."""
    out, stack, buf = [], [], []
    quote = None
    for ch in css:
        if quote:
            buf.append(ch)
            if ch == quote:
                quote = None
            continue
        if ch in '"\'':
            quote = ch; buf.append(ch); continue
        if ch == '{':
            stack.append(''.join(buf).strip()); buf = []
        elif ch == '}':
            prelude = stack.pop() if stack else ''
            decls = ''.join(buf).strip(); buf = []
            if decls:
                out.append((list(stack), prelude, decls))
        else:
            buf.append(ch)
    return out

def has_color(text):
    low = text.lower()
    return any(h in low for h in HEX) or any(r in text for r in RGB)

def recolor(text):
    for h, g in HEX.items():
        text = re.sub('#' + h, '#' + g, text, flags=re.I)
    for a, b in RGB.items():
        text = re.sub(r'rgba\(\s*' + a.replace(', ', r',\s*') + r'\s*,',
                      'rgba(' + b + ',', text)
    return text

css = strip_comments(open(SRC).read())
rules = parse(css)

blocks, stats = [], {'rules': 0, 'decls': 0}
for ctx, sel, decls in rules:
    if sel.startswith('@'):          # lewati @font-face/@keyframes dsb
        continue
    keep = [d.strip() for d in decls.split(';') if d.strip() and has_color(d)]
    if not keep:
        continue
    stats['rules'] += 1
    stats['decls'] += len(keep)
    body = recolor(';\n  '.join(keep)) + ';'
    rule = "%s {\n  %s\n}" % (sel, body)
    for c in reversed(ctx):          # bungkus ulang @media
        rule = "%s {\n%s\n}" % (c, rule)
    blocks.append(rule)

header = (
    "/*!\n"
    " * Tema warna SI-ABDI - palette hijau\n"
    " * Dihasilkan otomatis: python3 tools/generate-theme.py public/assets/css/theme-green.css\n * Jangan diedit manual - ubah pemetaan warna di script lalu jalankan ulang.\n"
    " * Hanya berisi deklarasi bermuatan warna, dimuat SETELAH file vendor.\n"
    " * primary #9c27b0 & biru #2196f3 -> #386C0B | info #00bcd4 -> #293F14\n * success #4caf50 -> #38A700\n"
    " * danger & warning sengaja dibiarkan (penanda semantik).\n"
    " */\n\n"
)
MANUAL = '''

/* ------------------------------------------------------------------
   Penyesuaian manual
   ------------------------------------------------------------------ */

/* Teks butuh kontras lebih tinggi daripada bidang berwarna.
   #38A700 hanya 3.17:1 di atas putih, jadi teks dipaksa ke hijau gelap. */
.text-success,
.text-info,
.text-primary {
  color: #386C0B !important;
}

/* Sidebar section "QR" memakai data-color="danger" (merah bawaan).
   Diseragamkan ke palette agar seluruh sidebar konsisten hijau. */
.sidebar[data-color="danger"] li.active > a,
.off-canvas-sidebar[data-color="danger"] li.active > a {
  background-color: #293F14;
  box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14),
              0 7px 10px -5px rgba(41, 63, 20, 0.4);
}

/* card-header-danger hanya dipakai sebagai header dekoratif (Dashboard &
   Generate QR Code), bukan penanda error -> ikut palette.
   btn-danger, alert-danger, dan text-danger sengaja tetap merah. */
.card .card-header-danger .card-icon,
.card .card-header-danger .card-text,
.card .card-header-danger:not(.card-header-icon):not(.card-header-text) {
  background: linear-gradient(60deg, #3EFF8B, #38A700);
  box-shadow: 0 4px 20px 0px rgba(0, 0, 0, 0.14),
              0 7px 10px -5px rgba(56, 167, 0, 0.4);
}
'''

open(sys.argv[1], 'w').write(header + '\n\n'.join(blocks) + '\n' + MANUAL)
print("rule: %d, deklarasi: %d" % (stats['rules'], stats['decls']))
