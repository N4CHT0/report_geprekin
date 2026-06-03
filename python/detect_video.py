#!/usr/bin/env python3
import argparse
import json
import os
import re
import subprocess
import sys
import tempfile
from urllib.parse import parse_qs, urlparse

import requests


def run(cmd):
    return subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)


def duration_seconds(video_path):
    cmd = [
        "ffprobe", "-v", "error",
        "-show_entries", "format=duration",
        "-of", "default=noprint_wrappers=1:nokey=1",
        video_path,
    ]
    p = run(cmd)
    if p.returncode != 0:
        raise RuntimeError(p.stderr.strip() or "ffprobe gagal membaca durasi video")
    return float(p.stdout.strip())


def extract_google_drive_file_id(url):
    match = re.search(r"/d/([^/]+)", url)
    if match:
        return match.group(1)
    parsed = urlparse(url)
    query = parse_qs(parsed.query)
    if "id" in query and query["id"]:
        return query["id"][0]
    return None


def download_url_to_temp(url):
    import urllib.request
    import urllib.error
    tmp = tempfile.NamedTemporaryFile(suffix=".mp4", delete=False)
    tmp_path = tmp.name
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=180) as response:
            first_chunk = True
            while True:
                chunk = response.read(1024 * 1024)
                if not chunk:
                    break
                if first_chunk:
                    preview = chunk[:300].lower()
                    if b"<html" in preview or b"<!doctype html" in preview:
                        raise RuntimeError("Download menghasilkan HTML, bukan video. Pastikan link Google Drive public.")
                    first_chunk = False
                tmp.write(chunk)
    except Exception as e:
        tmp.close()
        if os.path.isfile(tmp_path):
            os.remove(tmp_path)
        raise
    tmp.close()
    if os.path.getsize(tmp_path) <= 0:
        os.remove(tmp_path)
        raise RuntimeError("File video hasil download kosong")
    return tmp_path


def download_google_drive_to_temp(url):
    file_id = extract_google_drive_file_id(url)
    if not file_id:
        # Laravel bisa kirim direct uc?export URL; fallback download langsung.
        return download_url_to_temp(url)

    tmp = tempfile.NamedTemporaryFile(suffix=".mp4", delete=False)
    tmp_path = tmp.name
    tmp.close()

    p = run(["python3", "-m", "gdown", "--id", file_id, "-O", tmp_path, "--quiet"])
    if p.returncode == 0 and os.path.isfile(tmp_path) and os.path.getsize(tmp_path) > 0:
        return tmp_path

    if os.path.isfile(tmp_path):
        os.remove(tmp_path)

    direct_url = f"https://drive.google.com/uc?export=download&id={file_id}"
    return download_url_to_temp(direct_url)


def prepare_video_source(video, source_type):
    if source_type == "google_drive":
        return download_google_drive_to_temp(video), True
    if video.startswith("http://") or video.startswith("https://"):
        return download_url_to_temp(video), True
    if not os.path.isfile(video):
        raise RuntimeError(f"File video tidak ditemukan: {video}")
    return video, False


def detect_with_yolo(video_path, fps):
    try:
        import cv2
        from ultralytics import YOLO
    except Exception as e:
        raise RuntimeError("Dependency detector belum siap. Jalankan: pip install ultralytics opencv-python requests gdown. Error: " + str(e))

    project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    model_path = os.path.join(project_root, "yolov8n.pt")
    
    try:
        model = YOLO(model_path)
    except Exception as e:
        raise RuntimeError(f"Gagal memuat model YOLO dari {model_path}. Error: {str(e)}")

    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise RuntimeError("Video tidak bisa dibuka oleh OpenCV")

    native_fps = cap.get(cv2.CAP_PROP_FPS)
    if not native_fps or native_fps <= 0:
        native_fps = 25

    frame_interval = max(int(native_fps / fps), 1)
    target_classes = {"person", "motorcycle", "car", "bus", "truck"}
    unique_counts = {k: set() for k in target_classes}
    fallback_counts = {k: 0 for k in target_classes}
    minute_counts = {}
    minute_frames = {}
    frame_idx = 0
    sampled_frames = 0

    while True:
        ok, frame = cap.read()
        if not ok:
            break

        if frame_idx % frame_interval != 0:
            frame_idx += 1
            continue

        minute = int((frame_idx / native_fps) // 60)
        minute_counts.setdefault(minute, 0)
        
        # Simpan frame pertama dari setiap menit sebagai perwakilan
        if minute not in minute_frames:
            minute_frames[minute] = frame.copy()

        results = model.track(frame, persist=True, conf=0.35, iou=0.45, verbose=False, imgsz=640)
        result = results[0]

        if result.boxes is not None:
            for box in result.boxes:
                cls_id = int(box.cls[0])
                label = result.names.get(cls_id, "")
                if label not in target_classes:
                    continue
                track_id = int(box.id[0]) if box.id is not None else None
                if track_id is not None:
                    unique_counts[label].add(track_id)
                else:
                    fallback_counts[label] += 1
                minute_counts[minute] += 1

        sampled_frames += 1
        frame_idx += 1

    cap.release()
    counts = {k: (len(unique_counts[k]) if unique_counts[k] else fallback_counts[k]) for k in target_classes}
    peak_minute = max(minute_counts.items(), key=lambda item: item[1])[0] if minute_counts else 0
    peak_frame = minute_frames.get(peak_minute)
    
    return counts, peak_minute, sampled_frames, peak_frame


def generate_groq_insight(counts, api_key):
    try:
        import urllib.request
        import urllib.error
        
        prompt = f"Data berikut adalah statistik lalu lintas jalan raya pada 1 menit paling sibuk (peak minute): Motor {counts.get('motorcycle', 0)}, Mobil {counts.get('car', 0)}, Pejalan Kaki {counts.get('person', 0)}, Bus {counts.get('bus', 0)}, Truk {counts.get('truck', 0)}. Sebagai pakar bisnis ritel dan kuliner, berikan ulasan singkat (1 paragraf pendek yang tajam) apakah lokasi jalan dengan demografi traffic ini strategis untuk membuka bisnis kuliner ayam goreng cepat saji (Ayam Geprek). Jangan berbasa-basi, langsung berikan penilaian potensi bisnisnya berdasarkan rasio kendaraannya."

        headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json",
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
        }
        
        payload = {
            "model": "llama-3.3-70b-versatile",
            "messages": [
                {
                    "role": "user",
                    "content": prompt
                }
            ],
            "max_tokens": 300,
            "temperature": 0.5
        }
        
        req = urllib.request.Request(
            "https://api.groq.com/openai/v1/chat/completions",
            data=json.dumps(payload).encode('utf-8'),
            headers=headers,
            method="POST"
        )
        
        with urllib.request.urlopen(req, timeout=30) as response:
            if response.status == 200:
                data = json.loads(response.read().decode('utf-8'))
                return data["choices"][0]["message"]["content"].strip()
            else:
                return f"Gagal mendapatkan insight dari Groq: HTTP {response.status}"
    except urllib.error.HTTPError as e:
        return f"Gagal mendapatkan insight dari Groq: HTTP {e.code} - {e.read().decode('utf-8')}"
    except Exception as e:
        return f"Error Groq API: {str(e)}"


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--video", required=True)
    parser.add_argument("--job", required=True)
    parser.add_argument("--fps", type=float, default=1)
    parser.add_argument("--max-duration", type=int, default=900)
    parser.add_argument("--source-type", default="upload")
    parser.add_argument("--groq-key", default="")
    args = parser.parse_args()

    local_video = None
    downloaded_temp = False

    try:
        local_video, downloaded_temp = prepare_video_source(args.video, args.source_type)
        duration = duration_seconds(local_video)
        if duration > args.max_duration:
            raise RuntimeError(f"Durasi video {duration:.2f} detik melebihi batas {args.max_duration} detik")
        
        counts, peak_minute, sampled_frames, peak_frame = detect_with_yolo(local_video, args.fps)
        
        groq_insight = None
        if args.groq_key:
            groq_insight = generate_groq_insight(counts, args.groq_key)

        peak_frame_b64 = None
        if peak_frame is not None:
            import cv2
            import base64
            # Compress to JPEG to save space
            success, buffer = cv2.imencode('.jpg', peak_frame, [cv2.IMWRITE_JPEG_QUALITY, 70])
            if success:
                peak_frame_b64 = base64.b64encode(buffer).decode('utf-8')

        print(json.dumps({
            "job_id": args.job,
            "status": "done",
            "duration_seconds": round(duration, 2),
            "fps_sampling": args.fps,
            "sampled_frames": sampled_frames,
            "counts": counts,
            "peak_minute": peak_minute,
            "ai_insight": groq_insight,
            "peak_frame_b64": peak_frame_b64
        }))
    except Exception as e:
        print(json.dumps({"job_id": args.job, "status": "failed", "error": str(e)}))
        sys.exit(1)
    finally:
        if downloaded_temp and local_video and os.path.isfile(local_video):
            os.remove(local_video)


if __name__ == "__main__":
    main()
