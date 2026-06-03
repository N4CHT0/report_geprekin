#!/usr/bin/env python3

"""

ocr_processor.py v6 — Gemini Vision API (GRATIS 1500 req/hari)

Menggantikan Tesseract OCR yang tidak akurat untuk struk termal.



Keunggulan:

  - Akurasi 95%+ vs Tesseract 60-70%

  - Tidak perlu preprocessing gambar manual

  - Langsung parse JSON terstruktur dari AI

  - Gratis: 1500 request/hari di Gemini 1.5 Flash



Setup:

  pip install google-generativeai pillow

  Pastikan GEMINI_API_KEY ada di .env Laravel



Penggunaan (sama seperti v5):

  python3 ocr_processor.py <image_path> <outlets_json> <delete_after>

"""



import sys

import os

import json

import re

import base64

import urllib.request

import urllib.error

from typing import Optional





# ─────────────────────────────────────────────────────────────────────────────

# GEMINI API — tanpa library eksternal (pakai urllib bawaan Python)

# ─────────────────────────────────────────────────────────────────────────────



GEMINI_API_URL = (

    "https://generativelanguage.googleapis.com/v1beta/models/"

    "gemini-2.5-flash:generateContent"

)



PROMPT_STRUK = """

Kamu adalah sistem ekstraksi data struk belanja yang sangat akurat.

Analisis gambar struk/receipt berikut dan ekstrak informasi ini PERSIS seperti tertulis.



Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan):

{

  "nomor_struk": "...",

  "total_belanja": 12345,

  "tanggal_struk": "YYYY-MM-DD",

  "nama_outlet": "..."

}



Aturan:

- nomor_struk: cari "Receipt Number", "No. Receipt", "No. Struk", "Order ID", atau kode transaksi alfanumerik. Ambil yang paling mirip nomor transaksi (bukan Order ID jika ada Receipt Number).

- total_belanja: angka TOTAL/GRAND TOTAL dalam Rupiah (integer, tanpa titik/koma). Bukan Subtotal, bukan Cash, bukan Change.

- tanggal_struk: format YYYY-MM-DD. Jika tidak ada, kembalikan null.

- nama_outlet: nama toko/outlet dari header struk (bukan alamat). Kembalikan string lengkap nama outlet.



Jika tidak bisa membaca field tertentu, kembalikan null untuk field itu.

"""





def image_to_base64(image_path: str) -> tuple[str, str]:

    """Konversi gambar ke base64 dan deteksi MIME type."""

    ext = os.path.splitext(image_path)[1].lower()

    mime_map = {

        '.jpg': 'image/jpeg',

        '.jpeg': 'image/jpeg',

        '.png': 'image/png',

        '.webp': 'image/webp',

        '.gif': 'image/gif',

    }

    mime = mime_map.get(ext, 'image/jpeg')



    with open(image_path, 'rb') as f:

        data = base64.b64encode(f.read()).decode('utf-8')



    return data, mime





def call_gemini(image_path: str, api_key: str, retries: int = 3) -> dict:

    """

    Kirim gambar ke Gemini 2.5 Flash dan minta parse struk.

    Retry otomatis jika kena 429 (rate limit).

    """

    import time

    img_b64, mime_type = image_to_base64(image_path)



    payload = {

        "contents": [

            {

                "parts": [

                    {

                        "inline_data": {

                            "mime_type": mime_type,

                            "data": img_b64,

                        }

                    },

                    {

                        "text": PROMPT_STRUK

                    }

                ]

            }

        ],

        "generationConfig": {

            "temperature": 0.1,       # rendah = konsisten, tidak "kreatif"

            "maxOutputTokens": 1024,

        }

    }



    url = f"{GEMINI_API_URL}?key={api_key}"

    body = json.dumps(payload).encode('utf-8')



    req = urllib.request.Request(

        url,

        data=body,

        headers={'Content-Type': 'application/json'},

        method='POST'

    )



    last_error = None

    for attempt in range(retries):

        try:

            with urllib.request.urlopen(req, timeout=30) as resp:

                raw = resp.read().decode('utf-8')

            break  # sukses, keluar dari loop retry

        except urllib.error.HTTPError as e:

            err_body = e.read().decode('utf-8')

            if e.code == 429 and attempt < retries - 1:

                wait = 10 * (attempt + 1)  # 10s, 20s, 30s

                import sys as _sys

                print(f"[OCR] Rate limit 429, retry {attempt+1}/{retries} dalam {wait}s...", file=_sys.stderr)

                time.sleep(wait)

                last_error = RuntimeError(f"Gemini HTTP {e.code}: {err_body[:300]}")

                continue

            raise RuntimeError(f"Gemini HTTP {e.code}: {err_body[:300]}")

        except urllib.error.URLError as e:

            raise RuntimeError(f"Gemini network error: {e.reason}")

    else:

        raise last_error



    response = json.loads(raw)



    # Ambil teks dari response Gemini

    try:

        text = response['candidates'][0]['content']['parts'][0]['text']

    except (KeyError, IndexError) as e:

        raise RuntimeError(f"Unexpected Gemini response structure: {str(response)[:300]}")



    # Bersihkan markdown code fence jika ada

    text = text.strip()

    text = re.sub(r'^```(?:json)?\s*', '', text)

    text = re.sub(r'\s*```$', '', text)

    text = text.strip()



    # Parse JSON

    try:

        result = json.loads(text)

    except json.JSONDecodeError:

        # Coba cari JSON di dalam teks (fallback)

        match = re.search(r'\{[\s\S]*\}', text)

        if match:

            result = json.loads(match.group())

        else:

            raise RuntimeError(f"Gemini tidak mengembalikan JSON valid: {text[:200]}")



    return result





# ─────────────────────────────────────────────────────────────────────────────

# OUTLET MATCHING — fuzzy per kata (sama seperti v5, dipertahankan)

# ─────────────────────────────────────────────────────────────────────────────



def match_outlet(nama: str, outlets_json: str) -> Optional[int]:

    if not nama or not outlets_json:

        return None

    try:

        outlets = json.loads(outlets_json)

    except Exception:

        return None



    NOISE = {

        'geprek', 'ayam', 'warung', 'resto', 'rumah', 'makan', 'cafe',

        'kafe', 'dan', 'the', 'aja', 'kinaja', 'geprekin', 'g', 'gp',

        'spesialis', 'spesial', 'by', 'and',

    }



    raw_words = re.sub(r'[^A-Za-z0-9\s]', ' ', nama).split()

    keywords = [w for w in raw_words if len(w) >= 3 and w.lower() not in NOISE]



    if not keywords:

        return None



    # P1: Kata paling spesifik (terpanjang) match ke nama_outlet DB

    for kw in sorted(keywords, key=len, reverse=True):

        kw_lower = kw.lower()

        for o in outlets:

            if kw_lower in o.get("nama_outlet", "").lower():

                return int(o["id"])



    # P2: Scoring kata

    best_score = 0

    best_id = None

    kw_set = {w.lower() for w in keywords}

    for o in outlets:

        db_words = set(

            re.sub(r'[^a-z0-9\s]', ' ', o.get("nama_outlet", "").lower()).split()

        ) - NOISE

        score = len(kw_set & db_words)

        if score > best_score:

            best_score = score

            best_id = int(o["id"])



    return best_id if best_score >= 1 else None





# ─────────────────────────────────────────────────────────────────────────────

# NORMALISASI HASIL GEMINI

# ─────────────────────────────────────────────────────────────────────────────



def normalize_result(raw: dict) -> dict:

    """Bersihkan dan validasi hasil dari Gemini."""



    # Nomor struk — bersihkan spasi

    nomor_struk = raw.get('nomor_struk') or ''

    if isinstance(nomor_struk, str):

        nomor_struk = nomor_struk.strip()

    else:

        nomor_struk = ''



    # Total belanja — pastikan integer

    total_raw = raw.get('total_belanja')

    try:

        # Kadang Gemini return string "32000" atau "32.000"

        if isinstance(total_raw, str):

            total_raw = re.sub(r'[.,\s]', '', total_raw)

        total_belanja = int(total_raw) if total_raw else 0

    except (ValueError, TypeError):

        total_belanja = 0



    # Tanggal — pastikan format YYYY-MM-DD

    tanggal = raw.get('tanggal_struk') or ''

    if tanggal and not re.match(r'^\d{4}-\d{2}-\d{2}$', str(tanggal)):

        # Coba konversi dari berbagai format

        tanggal = _parse_tanggal_fallback(str(tanggal))



    # Nama outlet

    nama_outlet = raw.get('nama_outlet') or ''

    if isinstance(nama_outlet, str):

        nama_outlet = nama_outlet.strip()



    return {

        'nomor_struk': nomor_struk or None,

        'total_belanja': total_belanja,

        'tanggal_struk': tanggal or None,

        'nama_outlet': nama_outlet,

    }





def _parse_tanggal_fallback(text: str) -> Optional[str]:

    """Konversi tanggal format campuran ke YYYY-MM-DD."""

    mon_map = {

        'jan': '01', 'feb': '02', 'mar': '03', 'apr': '04',

        'mei': '05', 'may': '05', 'jun': '06', 'jul': '07',

        'agu': '08', 'aug': '08', 'sep': '09',

        'okt': '10', 'oct': '10', 'nov': '11',

        'des': '12', 'dec': '12',

    }



    # "07 Jun 2025" / "7 Juni 2025"

    m = re.search(

        r'(\d{1,2})\s+([A-Za-z]{3,})\s+(\d{4})', text

    )

    if m:

        mon_key = m.group(2).lower()[:3]

        mon = mon_map.get(mon_key)

        if mon:

            return f"{m.group(3)}-{mon}-{m.group(1).zfill(2)}"



    # "07/06/2025" atau "07-06-2025"

    m2 = re.search(r'(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})', text)

    if m2:

        return f"{m2.group(3)}-{m2.group(2).zfill(2)}-{m2.group(1).zfill(2)}"



    return None





# ─────────────────────────────────────────────────────────────────────────────

# ENTRY POINT

# ─────────────────────────────────────────────────────────────────────────────



if __name__ == "__main__":

    if len(sys.argv) < 2:

        print(json.dumps({"error": "No image path provided"}))

        sys.exit(1)



    image_path   = sys.argv[1]

    outlets_json = sys.argv[2] if len(sys.argv) > 2 else "[]"

    delete_after = sys.argv[3] if len(sys.argv) > 3 else "1"



    if not os.path.isfile(image_path):

        print(json.dumps({"error": f"File not found: {image_path}"}))

        sys.exit(1)



    # Ambil API key dari argv[4] (dikirim langsung dari PHP)

    # Fallback ke environment variable jika tidak ada di argv

    api_key = sys.argv[4] if len(sys.argv) > 4 else os.environ.get('GEMINI_API_KEY', '')



    if not api_key:

        print(json.dumps({"error": "GEMINI_API_KEY tidak ditemukan. Set di env atau argumen ke-4."}))

        sys.exit(1)



    try:

        # Panggil Gemini Vision

        raw_result = call_gemini(image_path, api_key)



        # Normalisasi

        parsed = normalize_result(raw_result)



        # Match outlet

        outlet_id = match_outlet(parsed['nama_outlet'], outlets_json)



        result = {

            "nomor_struk":   parsed['nomor_struk'],

            "total_belanja": parsed['total_belanja'],

            "outlet_id":     outlet_id,

            "tanggal_struk": parsed['tanggal_struk'],

            "nama_outlet":   parsed['nama_outlet'],

            "_debug_gemini": raw_result,   # debug: hasil mentah dari Gemini

        }



        print(json.dumps(result, ensure_ascii=False))



    except Exception as exc:

        print(json.dumps({"error": str(exc)}))

        sys.exit(1)



    finally:

        if delete_after == "1" and os.path.isfile(image_path):

            try:

                os.remove(image_path)

            except Exception:

                pass

