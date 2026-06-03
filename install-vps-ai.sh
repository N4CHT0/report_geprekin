#!/bin/bash
set -e

echo "[1/5] Install system packages..."
dnf install -y python3 python3-pip ffmpeg git

echo "[2/5] Upgrade pip..."
python3 -m pip install --upgrade pip

echo "[3/5] Install Python AI dependencies..."
pip3 install ultralytics opencv-python numpy pillow

echo "[4/5] Test YOLO import..."
python3 - <<'PY'
from ultralytics import YOLO
print("YOLO import OK")
PY

echo "[5/5] Done."
