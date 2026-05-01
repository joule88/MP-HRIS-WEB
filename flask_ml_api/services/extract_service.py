import cv2
import os
import numpy as np

import sys
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from utils.lbp_features import preprocess_face, FACE_SIZE

BLUR_THRESHOLD = 30.0
MIN_FACE_RATIO = 0.15

face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)


def get_blur_score(img):
    return cv2.Laplacian(img, cv2.CV_64F).var()


def detect_frontal_face(gray_frame):
    h, w = gray_frame.shape
    min_size = int(min(h, w) * MIN_FACE_RATIO)

    faces = face_cascade.detectMultiScale(
        gray_frame, scaleFactor=1.1, minNeighbors=5,
        minSize=(min_size, min_size)
    )

    if len(faces) == 0:
        faces = face_cascade.detectMultiScale(
            gray_frame, scaleFactor=1.05, minNeighbors=4,
            minSize=(min_size, min_size)
        )

    if len(faces) == 0:
        return None

    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    return faces[0]


def estimate_yaw_from_symmetry(gray_face):
    h, w = gray_face.shape
    mid = w // 2

    left_half = gray_face[:, :mid].astype(np.float64)
    right_half = cv2.flip(gray_face[:, mid:], 1).astype(np.float64)

    min_w = min(left_half.shape[1], right_half.shape[1])
    left_half = left_half[:, :min_w]
    right_half = right_half[:, :min_w]

    if left_half.size == 0 or right_half.size == 0:
        return 999.0

    left_mean = np.mean(left_half)
    right_mean = np.mean(right_half)

    diff = abs(left_mean - right_mean)
    estimated_yaw = diff * 0.8

    return estimated_yaw


def process_frame(frame, gray_frame):
    face_rect = detect_frontal_face(gray_frame)
    if face_rect is None:
        return None, None, "no_face", 0.0, 0.0

    (x, y, w, h) = face_rect

    padding = int(max(w, h) * 0.1)
    x1 = max(0, x - padding)
    y1 = max(0, y - padding)
    x2 = min(gray_frame.shape[1], x + w + padding)
    y2 = min(gray_frame.shape[0], y + h + padding)

    face_crop_gray = gray_frame[y1:y2, x1:x2]
    face_crop_color = frame[y1:y2, x1:x2]

    face_resized = cv2.resize(face_crop_gray, FACE_SIZE, interpolation=cv2.INTER_AREA)

    blur_score = get_blur_score(face_resized)
    if blur_score < BLUR_THRESHOLD:
        return None, None, f"blur ({round(blur_score, 1)})", blur_score, 0.0

    yaw_estimate = estimate_yaw_from_symmetry(face_resized)

    return face_resized, face_crop_color, "ok", blur_score, yaw_estimate


def extract_frames(video_path, output_dir, target_frames=30):
    if not os.path.exists(video_path):
        raise Exception(f"Video tidak ditemukan: {video_path}")

    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    else:
        for f in os.listdir(output_dir):
            if (f.startswith("frame_") or f.startswith("raw_frame_")) and f.endswith(".jpg"):
                os.remove(os.path.join(output_dir, f))

    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise Exception("Gagal membuka file video.")

    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    fps = cap.get(cv2.CAP_PROP_FPS)

    if total_frames <= 0:
        raise Exception("Video tidak valid (0 frame).")

    candidates = []
    frame_idx = 0
    skipped_no_face = 0
    skipped_blur = 0
    skipped_side = 0

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        face_img, face_color, status, blur_score, yaw = process_frame(frame, gray)

        if face_img is None:
            if "blur" in status:
                skipped_blur += 1
            else:
                skipped_no_face += 1
            frame_idx += 1
            continue

        candidates.append({
            'frame_idx': frame_idx,
            'face_gray': face_img,
            'face_color': face_color,
            'blur_score': blur_score,
            'yaw': yaw
        })

        frame_idx += 1

    cap.release()

    if len(candidates) == 0:
        raise Exception(
            "Tidak ada frame wajah frontal berkualitas yang berhasil diekstrak. "
            "Pastikan wajah menghadap depan dengan pencahayaan cukup."
        )

    candidates.sort(key=lambda c: c['blur_score'], reverse=True)

    if len(candidates) > target_frames:
        candidates_by_idx = sorted(candidates, key=lambda c: c['frame_idx'])

        chunk_size = len(candidates_by_idx) // target_frames
        selected = []

        for i in range(target_frames):
            start = i * chunk_size
            end = min(start + chunk_size, len(candidates_by_idx))
            chunk = candidates_by_idx[start:end]

            best_in_chunk = max(chunk, key=lambda c: c['blur_score'])
            selected.append(best_in_chunk)

        candidates = sorted(selected, key=lambda c: c['frame_idx'])

    saved_count = 0
    for cand in candidates:
        filename = f"frame_{saved_count:03d}.jpg"
        filename_raw = f"raw_frame_{saved_count:03d}.jpg"
        cv2.imwrite(
            os.path.join(output_dir, filename),
            cand['face_gray'],
            [int(cv2.IMWRITE_JPEG_QUALITY), 95]
        )
        cv2.imwrite(os.path.join(output_dir, filename_raw), cand['face_color'])
        saved_count += 1

    return {
        "total_video_frames": total_frames,
        "video_fps": round(fps, 1),
        "total_candidates": len(candidates),
        "total_extracted": saved_count,
        "target_frames": target_frames,
        "skipped_no_face": skipped_no_face,
        "skipped_blur": skipped_blur,
        "skipped_side_face": skipped_side,
        "output_dir": output_dir
    }
