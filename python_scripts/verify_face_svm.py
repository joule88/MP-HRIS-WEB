import cv2
import os
import sys
import json
import numpy as np
import joblib

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lbp_features import extract_lbp_features, FACE_SIZE

UNKNOWN_LABEL = "unknown"
BLUR_THRESHOLD = 30.0
THRESHOLD_APPROVED = 0.75
THRESHOLD_PENDING = 0.55

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
    min_size = int(min(h, w) * 0.1)

    faces = face_cascade.detectMultiScale(
        gray, 1.1, 5, minSize=(min_size, min_size)
    )
    if len(faces) == 0:
        faces = face_cascade.detectMultiScale(
            gray, 1.05, 3, minSize=(min_size, min_size)
        )
    if len(faces) == 0:
        faces = profile_cascade.detectMultiScale(
            gray, 1.1, 3, minSize=(min_size, min_size)
        )
    if len(faces) == 0:
        flipped = cv2.flip(gray, 1)
        faces = face_cascade.detectMultiScale(
            flipped, 1.05, 3, minSize=(min_size, min_size)
        )
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

    return face, blur_score


def verify_face(model_dir, user_id, image_path):
    try:
        model_file = os.path.join(model_dir, "face_model.pkl")
        scaler_file = os.path.join(model_dir, "face_scaler.pkl")
        labels_file = os.path.join(model_dir, "face_labels.json")

        if not os.path.exists(model_file):
            raise Exception("Model wajah belum tersedia. Belum ada data training.")

        if not os.path.exists(scaler_file):
            raise Exception("File scaler belum tersedia.")

        if not os.path.exists(labels_file):
            raise Exception("File label belum tersedia.")

        processed_face = None
        blur_score = 0.0

        try:
            processed_face, blur_score = preprocess_image(image_path)
        except Exception as e_proc:
            print(json.dumps({
                "status": "success",
                "match": False,
                "confidence": 0,
                "verification_status": "PREPROCESSING_FAILED",
                "message": str(e_proc),
                "blur_score": 0
            }))
            return

        features = extract_lbp_features(processed_face)

        svm = joblib.load(model_file)
        scaler = joblib.load(scaler_file)

        with open(labels_file, 'r') as f:
            labels_data = json.load(f)

        features_scaled = scaler.transform([features])
        proba = svm.predict_proba(features_scaled)[0]
        classes = list(svm.classes_)

        predicted_class = classes[np.argmax(proba)]
        max_confidence = float(np.max(proba))

        expected_user = str(user_id)

        if expected_user in classes:
            user_idx = classes.index(expected_user)
            user_confidence = float(proba[user_idx])
        else:
            user_confidence = 0.0

        unknown_confidence = 0.0
        if UNKNOWN_LABEL in classes:
            unknown_idx = classes.index(UNKNOWN_LABEL)
            unknown_confidence = float(proba[unknown_idx])

        final_confidence = user_confidence

        if final_confidence >= THRESHOLD_APPROVED and predicted_class == expected_user:
            status_verifikasi = "APPROVED"
            is_match = True
        elif final_confidence >= THRESHOLD_PENDING and predicted_class == expected_user:
            status_verifikasi = "PENDING"
            is_match = True
        else:
            status_verifikasi = "REJECTED"
            is_match = False

        result = {
            "status": "success",
            "match": is_match,
            "verification_status": status_verifikasi,
            "confidence": round(final_confidence, 4),
            "svm_confidence": round(user_confidence, 4),
            "predicted_user": str(predicted_class),
            "expected_user": expected_user,
            "unknown_confidence": round(unknown_confidence, 4),
            "blur_score": round(blur_score, 1),
            "user_id": int(user_id) if str(user_id).isdigit() else user_id
        }

        print(json.dumps(result))

    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)


if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({
            "status": "error",
            "message": "Usage: python verify_face_svm.py <model_dir> <user_id> <image_path>"
        }))
        sys.exit(1)

    m_dir = sys.argv[1]
    u_id = sys.argv[2]
    i_path = sys.argv[3]

    verify_face(m_dir, u_id, i_path)
