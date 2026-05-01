import cv2
import os
import sys
import json
import logging
import numpy as np
import joblib

logger = logging.getLogger('flask_ml')

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from utils.lbp_features import extract_lbp_features, preprocess_face, FACE_SIZE

UNKNOWN_LABEL = "unknown"
BLUR_THRESHOLD = 30.0
DF_APPROVED = 1.5
DF_PENDING = 0.5

face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
)
profile_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + 'haarcascade_profileface.xml'
)


def get_blur_score(img):
    return cv2.Laplacian(img, cv2.CV_64F).var()


def fix_exif_rotation(img_path):
    try:
        from PIL import Image
        pil_img = Image.open(img_path)
        exif = pil_img.getexif()
        orientation = exif.get(274, 1)

        if orientation == 3:
            pil_img = pil_img.rotate(180, expand=True)
        elif orientation == 6:
            pil_img = pil_img.rotate(270, expand=True)
        elif orientation == 8:
            pil_img = pil_img.rotate(90, expand=True)

        img_array = np.array(pil_img)
        if len(img_array.shape) == 3 and img_array.shape[2] == 3:
            img_array = cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
        return img_array
    except ImportError:
        return None
    except Exception:
        return None


def detect_and_crop_face(gray):
    h, w = gray.shape

    if max(h, w) > 640:
        scale = 640.0 / max(h, w)
        gray = cv2.resize(gray, (int(w * scale), int(h * scale)), interpolation=cv2.INTER_AREA)
        h, w = gray.shape

    min_size = int(min(h, w) * 0.1)

    faces = face_cascade.detectMultiScale(gray, 1.1, 5, minSize=(min_size, min_size))
    if len(faces) == 0:
        faces = face_cascade.detectMultiScale(gray, 1.05, 3, minSize=(min_size, min_size))
    if len(faces) == 0:
        faces = profile_cascade.detectMultiScale(gray, 1.1, 3, minSize=(min_size, min_size))
    if len(faces) == 0:
        flipped = cv2.flip(gray, 1)
        faces = face_cascade.detectMultiScale(flipped, 1.05, 3, minSize=(min_size, min_size))
        if len(faces) > 0:
            gray = flipped

    if len(faces) == 0:
        return None

    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    (x, y, fw, fh) = faces[0]

    padding = int(max(fw, fh) * 0.1)
    x1 = max(0, x - padding)
    y1 = max(0, y - padding)
    x2 = min(w, x + fw + padding)
    y2 = min(h, y + fh + padding)

    face_crop = gray[y1:y2, x1:x2]

    if face_crop.shape[0] < 10 or face_crop.shape[1] < 10:
        return None

    face_resized = cv2.resize(face_crop, FACE_SIZE, interpolation=cv2.INTER_AREA)
    return face_resized


def preprocess_image(image_path):
    if not os.path.exists(image_path):
        raise Exception("File gambar tidak ditemukan.")

    img = fix_exif_rotation(image_path)
    if img is None:
        img = cv2.imread(image_path)

    if img is None:
        raise Exception("Gagal membaca file gambar.")

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY) if len(img.shape) == 3 else img

    face = detect_and_crop_face(gray)
    if face is None:
        raise Exception(
            "Wajah tidak terdeteksi. Pastikan pencahayaan cukup dan wajah terlihat jelas."
        )

    blur_score = get_blur_score(face)

    if blur_score < BLUR_THRESHOLD:
        raise Exception(
            f"Foto terlalu buram (Score: {round(blur_score, 1)}). Harap foto ulang."
        )

    face_preprocessed = preprocess_face(face)
    return face_preprocessed, blur_score


def extract_frames_from_video(video_path, target_frames=10):
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise Exception("Gagal membuka file video verifikasi.")

    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

    if total_frames <= 0:
        cap.release()
        raise Exception("Video tidak valid (0 frame).")

    candidates = []
    frame_idx = 0
    sample_interval = max(1, total_frames // (target_frames * 3))

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        if frame_idx % sample_interval != 0:
            frame_idx += 1
            continue

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        face_raw = detect_and_crop_face(gray)

        if face_raw is None:
            frame_idx += 1
            continue

        blur_score = get_blur_score(face_raw)
        if blur_score < BLUR_THRESHOLD:
            frame_idx += 1
            continue

        face_preprocessed = preprocess_face(face_raw)
        candidates.append({
            'face': face_preprocessed,
            'blur_score': blur_score,
            'frame_idx': frame_idx,
        })

        frame_idx += 1

    cap.release()

    if len(candidates) == 0:
        raise Exception(
            "Tidak ada frame wajah yang valid dalam video. "
            "Pastikan wajah menghadap depan dengan pencahayaan cukup."
        )

    candidates.sort(key=lambda c: c['blur_score'], reverse=True)
    selected = candidates[:target_frames]

    return selected


def _classify_frame(features, svm, scaler, expected_user):
    classes = [str(c) for c in svm.classes_]
    features_s = scaler.transform([features])
    df_values = svm.decision_function(features_s)[0]
    proba = svm.predict_proba(features_s)[0]

    if expected_user in classes:
        idx = classes.index(expected_user)
        user_df = float(df_values[idx] if hasattr(df_values, '__len__') else df_values)
        user_conf = float(proba[idx])
    else:
        user_df = -999.0
        user_conf = 0.0

    df_sorted = sorted(enumerate(df_values), key=lambda x: -x[1])
    predicted_class = str(classes[df_sorted[0][0]])

    top_df = float(df_values[df_sorted[0][0]])
    second_df = float(df_values[df_sorted[1][0]]) if len(df_sorted) > 1 else -999.0
    df_margin = top_df - second_df

    # === Verifikasi 1:1 ===
    # Untuk model LBP+SVM, cukup cek skor OVR user sendiri.
    # Gap check dihapus karena terlalu ketat untuk variasi pencahayaan/sudut.
    gap = top_df - user_df if predicted_class != expected_user else 0.0

    if user_df >= DF_APPROVED:
        status = "APPROVED"
        is_match = True
    elif user_df >= DF_PENDING:
        status = "PENDING"
        is_match = True
    else:
        status = "REJECTED"
        is_match = False

    return {
        'is_match': is_match,
        'status': status,
        'user_df': user_df,
        'confidence': user_conf,
        'predicted_class': predicted_class,
        'df_margin': round(df_margin, 4),
        'gap': round(gap, 4),
    }


def _aggregate_frame_results(frame_results):
    total = len(frame_results)
    approved = sum(1 for r in frame_results if r['status'] == 'APPROVED')
    pending = sum(1 for r in frame_results if r['status'] == 'PENDING')
    rejected = sum(1 for r in frame_results if r['status'] == 'REJECTED')

    approved_ratio = approved / total
    pending_ratio = pending / total

    avg_df = float(np.mean([r['user_df'] for r in frame_results]))
    avg_conf = float(np.mean([r['confidence'] for r in frame_results]))

    if approved_ratio >= 0.4:
        final_status = "APPROVED"
        final_match = True
    elif (approved_ratio + pending_ratio) >= 0.5:
        final_status = "PENDING"
        final_match = True
    else:
        final_status = "REJECTED"
        final_match = False

    return {
        'verification_status': final_status,
        'match': final_match,
        'avg_df': round(avg_df, 4),
        'confidence': round(avg_conf, 4),
        'frames_total': total,
        'frames_approved': approved,
        'frames_pending': pending,
        'frames_rejected': rejected,
        'approved_ratio': round(approved_ratio, 3),
    }


def verify_face(model_dir, user_id, input_path, is_video=False):
    model_file = os.path.join(model_dir, "face_model.pkl")
    scaler_file = os.path.join(model_dir, "face_scaler.pkl")
    labels_file = os.path.join(model_dir, "face_labels.json")

    if not os.path.exists(model_file):
        raise Exception("Model wajah belum tersedia. Belum ada data training.")
    if not os.path.exists(scaler_file):
        raise Exception("File scaler belum tersedia.")
    if not os.path.exists(labels_file):
        raise Exception("File label belum tersedia.")

    svm = joblib.load(model_file)
    scaler = joblib.load(scaler_file)

    expected_user = str(user_id)

    if is_video:
        try:
            frames = extract_frames_from_video(input_path, target_frames=10)
        except Exception as e_vid:
            return {
                "status": "success",
                "match": False,
                "confidence": 0,
                "verification_status": "PREPROCESSING_FAILED",
                "message": str(e_vid),
                "blur_score": 0,
                "frames_total": 0,
            }

        frame_results = []
        blur_scores = []

        for i, frame_data in enumerate(frames):
            face = frame_data['face']
            blur_scores.append(frame_data['blur_score'])
            features = extract_lbp_features(face)
            result = _classify_frame(features, svm, scaler, expected_user)
            frame_results.append(result)
            logger.info(
                f"  Frame {i}: predicted={result['predicted_class']}, "
                f"user_df={result['user_df']:.4f}, "
                f"gap={result['gap']:.4f}, "
                f"conf={result['confidence']:.4f}, "
                f"status={result['status']}"
            )

        aggregated = _aggregate_frame_results(frame_results)
        avg_blur = float(np.mean(blur_scores))

        return {
            "status": "success",
            "match": bool(aggregated['match']),
            "verification_status": aggregated['verification_status'],
            "confidence": aggregated['confidence'],
            "svm_df": aggregated['avg_df'],
            "blur_score": round(avg_blur, 1),
            "frames_total": aggregated['frames_total'],
            "frames_approved": aggregated['frames_approved'],
            "frames_pending": aggregated['frames_pending'],
            "frames_rejected": aggregated['frames_rejected'],
            "approved_ratio": aggregated['approved_ratio'],
            "predicted_user": expected_user if aggregated['match'] else "unknown",
            "actual_predicted": max(set(r['predicted_class'] for r in frame_results), key=lambda c: sum(1 for r in frame_results if r['predicted_class'] == c)),
            "expected_user": expected_user,
            "user_id": int(user_id) if str(user_id).isdigit() else str(user_id),
            "message": None,
        }

    else:
        try:
            processed_face, blur_score = preprocess_image(input_path)
        except Exception as e_proc:
            return {
                "status": "success",
                "match": False,
                "confidence": 0,
                "verification_status": "PREPROCESSING_FAILED",
                "message": str(e_proc),
                "blur_score": 0,
            }

        features = extract_lbp_features(processed_face)
        result = _classify_frame(features, svm, scaler, expected_user)

        return {
            "status": "success",
            "match": bool(result['is_match']),
            "verification_status": result['status'],
            "confidence": float(round(result['confidence'], 4)),
            "svm_df": float(round(result['user_df'], 4)),
            "predicted_user": result['predicted_class'],
            "expected_user": expected_user,
            "blur_score": float(round(blur_score, 1)),
            "user_id": int(user_id) if str(user_id).isdigit() else str(user_id),
            "message": None,
        }
