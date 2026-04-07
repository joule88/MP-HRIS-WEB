import cv2
import os
import sys
import json
import argparse
import numpy as np

FACE_SIZE = (128, 128)
BLUR_THRESHOLD = 30.0
MIN_FACE_RATIO = 0.15

face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)
profile_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_profileface.xml'
)


def apply_clahe(gray_img):
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray_img)


def get_blur_score(img):
    return cv2.Laplacian(img, cv2.CV_64F).var()


def detect_face(gray_frame):
    h, w = gray_frame.shape
    min_size = int(min(h, w) * MIN_FACE_RATIO)

    faces = face_cascade.detectMultiScale(
        gray_frame, scaleFactor=1.1, minNeighbors=5,
        minSize=(min_size, min_size)
    )

    if len(faces) == 0:
        faces = face_cascade.detectMultiScale(
            gray_frame, scaleFactor=1.05, minNeighbors=3,
            minSize=(min_size, min_size)
        )

    if len(faces) == 0:
        faces = profile_cascade.detectMultiScale(
            gray_frame, scaleFactor=1.1, minNeighbors=3,
            minSize=(min_size, min_size)
        )

    if len(faces) == 0:
        flipped = cv2.flip(gray_frame, 1)
        faces = profile_cascade.detectMultiScale(
            flipped, scaleFactor=1.1, minNeighbors=3,
            minSize=(min_size, min_size)
        )

    if len(faces) == 0:
        return None

    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    return faces[0]


def process_frame(frame, gray_frame):
    face_rect = detect_face(gray_frame)
    if face_rect is None:
        return None, None, "no_face"

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
        return None, None, f"blur ({round(blur_score, 1)})"

    face_denoised = cv2.GaussianBlur(face_resized, (3, 3), 0)
    face_final = apply_clahe(face_denoised)

    return face_final, face_crop_color, "ok"


def extract_frames(video_path, output_dir, max_frames=100):
    if not os.path.exists(video_path):
        raise Exception(f"Video tidak ditemukan: {video_path}")

    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    else:
        for f in os.listdir(output_dir):
            if f.startswith("frame_") and f.endswith(".jpg"):
                os.remove(os.path.join(output_dir, f))

    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise Exception("Gagal membuka file video.")

    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    fps = cap.get(cv2.CAP_PROP_FPS)

    if total_frames <= 0:
        raise Exception("Video tidak valid (0 frame).")

    interval = max(1, total_frames // max_frames)

    saved_count = 0
    skipped_no_face = 0
    skipped_blur = 0
    frame_idx = 0

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        if frame_idx % interval != 0:
            frame_idx += 1
            continue

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

        face_img, face_color, status = process_frame(frame, gray)

        if face_img is None:
            if "blur" in status:
                skipped_blur += 1
            else:
                skipped_no_face += 1
            frame_idx += 1
            continue

        filename = f"frame_{saved_count:03d}.jpg"
        filename_raw = f"raw_frame_{saved_count:03d}.jpg"
        cv2.imwrite(os.path.join(output_dir, filename), face_img)
        cv2.imwrite(os.path.join(output_dir, filename_raw), face_color)
        saved_count += 1

        frame_idx += 1

    cap.release()

    if saved_count == 0:
        raise Exception(
            "Tidak ada frame wajah berkualitas yang berhasil diekstrak. "
            "Pastikan wajah terlihat jelas dan pencahayaan cukup."
        )

    return {
        "total_video_frames": total_frames,
        "video_fps": round(fps, 1),
        "sampling_interval": interval,
        "total_extracted": saved_count,
        "skipped_no_face": skipped_no_face,
        "skipped_blur": skipped_blur,
        "output_dir": output_dir
    }


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Extract face frames from video")
    parser.add_argument("video_path", help="Path ke file video")
    parser.add_argument("output_dir", help="Folder output untuk frame wajah")
    parser.add_argument("--max_frames", type=int, default=100,
                        help="Maksimal jumlah frame yang diekstrak (default: 100)")

    args = parser.parse_args()

    try:
        result = extract_frames(args.video_path, args.output_dir, args.max_frames)
        print(json.dumps({
            "status": "success",
            "message": f"Berhasil mengekstrak {result['total_extracted']} frame wajah",
            **result
        }))
    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)
