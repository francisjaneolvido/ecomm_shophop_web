"""Recolor the existing Rider tutorial UI for ShopHop's navy/pink theme.

Requires OpenCV and imageio-ffmpeg. Keeps the original tutorial as a source file.
"""
from pathlib import Path
import subprocess
import cv2
import numpy as np
import imageio_ffmpeg

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / 'public/videos/rider-registration-tutorial.mp4'
TARGET = ROOT / 'public/videos/rider-registration-tutorial-navy-pink.mp4'
POSTER = ROOT / 'public/images/rider-registration-video-poster-navy-pink.png'

capture = cv2.VideoCapture(str(SOURCE))
width = int(capture.get(cv2.CAP_PROP_FRAME_WIDTH))
height = int(capture.get(cv2.CAP_PROP_FRAME_HEIGHT))
fps = capture.get(cv2.CAP_PROP_FPS)
if not capture.isOpened() or width <= 0 or height <= 0 or fps <= 0:
    raise RuntimeError('Could not read the existing Rider tutorial.')

command = [imageio_ffmpeg.get_ffmpeg_exe(), '-y', '-f', 'rawvideo', '-pix_fmt', 'bgr24',
           '-s', f'{width}x{height}', '-r', str(fps), '-i', 'pipe:0',
           '-an', '-c:v', 'libx264', '-preset', 'medium', '-crf', '21',
           '-pix_fmt', 'yuv420p', '-movflags', '+faststart', str(TARGET)]
encoder = subprocess.Popen(command, stdin=subprocess.PIPE, stdout=subprocess.DEVNULL,
                           stderr=subprocess.PIPE)
frames = 0
try:
    while True:
        ok, frame = capture.read()
        if not ok:
            break
        hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)
        # Only the old green/teal UI and backdrop are recolored. White form fields,
        # dark copy and gray controls keep their original contrast and sharpness.
        hue = hsv[:, :, 0]
        saturation = hsv[:, :, 1]
        old_theme = ((hue >= 35) & (hue <= 105)).astype(np.float32)
        # Antialiased lettering contains partially saturated edge pixels. Blend
        # those continuously to avoid a hard green/pink outline around text.
        alpha = old_theme * np.clip((saturation.astype(np.float32) - 8) / 130, 0, 1)
        y = np.arange(height, dtype=np.float32)[:, None]
        dark_target = np.empty_like(frame)
        dark_target[:, :, 0] = 60 + 14 * y / height
        dark_target[:, :, 1] = 25 + 7 * y / height
        dark_target[:, :, 2] = 16 + 4 * y / height
        pink_target = np.empty_like(frame)
        pink_target[:] = (120, 79, 224)
        target = np.where((hsv[:, :, 2] < 115)[:, :, None], dark_target, pink_target)
        updated = np.uint8(frame * (1 - alpha[:, :, None]) + target * alpha[:, :, None])
        if frames == 0:
            cv2.imwrite(str(POSTER), updated)
        encoder.stdin.write(updated.tobytes())
        frames += 1
finally:
    capture.release()
    encoder.stdin.close()
errors = encoder.stderr.read().decode('utf-8', errors='replace')
if encoder.wait() != 0 or frames == 0:
    raise RuntimeError('Video encoding failed: ' + errors[-1000:])
print(f'Wrote {frames} frames to {TARGET.name}')
