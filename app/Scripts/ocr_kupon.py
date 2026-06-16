#!/usr/bin/env python3
"""
ocr_kupon.py
Script untuk memproses gambar kupon undian manual menggunakan Gemini Vision.
Membaca tulisan tangan untuk: No. Struk, Nama, Alamat, No HP, No KTP.
"""
import sys
import os
import json
import re
import base64
import urllib.request
import urllib.error
from typing import Optional

GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent"

PROMPT_KUPON = """
Kamu adalah sistem ekstraksi data dari gambar kupon undian.
Gambar ini berisi kupon dengan kolom-kolom yang sebagian besar diisi dengan tulisan tangan.
Ekstrak informasi yang tertulis dengan sangat hati-hati dan akurat, perhatikan bentuk angka dan huruf.

Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan):
{
  "nomor_struk": "...",
  "nama_lengkap": "...",
  "alamat": "...",
  "no_telp": "...",
  "no_ktp": "..."
}

Aturan:
- nomor_struk: cari nomor yang tertulis di sebelah "No. Struk". Biasanya berupa deretan angka panjang (misal 16-20 digit).
- nama_lengkap: cari nama yang tertulis di sebelah "Nama".
- alamat: cari alamat yang tertulis di sebelah "Alamat".
- no_telp: cari nomor handphone yang tertulis di sebelah "No. HP" (misal berawalan 08).
- no_ktp: cari Nomor Induk Kependudukan (NIK) 16 digit yang tertulis di sebelah "No. KTP".
- Jika tulisan tidak jelas, coba tebak karakter yang paling logis (terutama angka untuk no hp dan ktp).
- Jika field benar-benar kosong, kembalikan null.
"""

def image_to_base64(image_path: str) -> tuple[str, str]:
    ext = os.path.splitext(image_path)[1].lower()
    mime_map = {'.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png', '.webp': 'image/webp'}
    mime = mime_map.get(ext, 'image/jpeg')
    with open(image_path, 'rb') as f:
        data = base64.b64encode(f.read()).decode('utf-8')
    return data, mime

def call_gemini(image_path: str, api_key: str, retries: int = 3) -> dict:
    import time
    img_b64, mime_type = image_to_base64(image_path)
    payload = {
        "contents": [{
            "parts": [
                {"inline_data": {"mime_type": mime_type, "data": img_b64}},
                {"text": PROMPT_KUPON}
            ]
        }],
        "generationConfig": {"temperature": 0.1, "maxOutputTokens": 1024}
    }
    url = f"{GEMINI_API_URL}?key={api_key}"
    body = json.dumps(payload).encode('utf-8')
    req = urllib.request.Request(url, data=body, headers={'Content-Type': 'application/json'}, method='POST')

    last_error = None
    for attempt in range(retries):
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                raw = resp.read().decode('utf-8')
            break
        except urllib.error.HTTPError as e:
            err_body = e.read().decode('utf-8')
            if e.code == 429 and attempt < retries - 1:
                wait = 10 * (attempt + 1)
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
    try:
        text = response['candidates'][0]['content']['parts'][0]['text']
    except (KeyError, IndexError):
        raise RuntimeError(f"Unexpected Gemini response structure: {str(response)[:300]}")

    text = text.strip()
    text = re.sub(r'^```(?:json)?\s*', '', text)
    text = re.sub(r'\s*```$', '', text)
    text = text.strip()

    try:
        result = json.loads(text)
    except json.JSONDecodeError:
        match = re.search(r'\{[\s\S]*\}', text)
        if match:
            result = json.loads(match.group())
        else:
            raise RuntimeError(f"Gemini tidak mengembalikan JSON valid: {text[:200]}")
    return result

def normalize_result(raw: dict) -> dict:
    def clean_str(val):
        if not val: return ''
        return str(val).strip()
    
    def clean_number(val):
        if not val: return ''
        return re.sub(r'[^0-9]', '', str(val))

    return {
        'nomor_struk': clean_str(raw.get('nomor_struk')),
        'nama_lengkap': clean_str(raw.get('nama_lengkap')),
        'alamat': clean_str(raw.get('alamat')),
        'no_telp': clean_number(raw.get('no_telp')),
        'no_ktp': clean_number(raw.get('no_ktp')),
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No image path provided"}))
        sys.exit(1)

    image_path = sys.argv[1]
    delete_after = sys.argv[2] if len(sys.argv) > 2 else "1"
    api_key = sys.argv[3] if len(sys.argv) > 3 else os.environ.get('GEMINI_API_KEY', '')

    if not os.path.isfile(image_path):
        print(json.dumps({"error": f"File not found: {image_path}"}))
        sys.exit(1)

    if not api_key:
        print(json.dumps({"error": "GEMINI_API_KEY tidak ditemukan."}))
        sys.exit(1)

    try:
        raw_result = call_gemini(image_path, api_key)
        parsed = normalize_result(raw_result)
        parsed["_debug_gemini"] = raw_result
        print(json.dumps(parsed, ensure_ascii=False))
    except Exception as exc:
        print(json.dumps({"error": str(exc)}))
        sys.exit(1)
    finally:
        if delete_after == "1" and os.path.isfile(image_path):
            try:
                os.remove(image_path)
            except Exception:
                pass
