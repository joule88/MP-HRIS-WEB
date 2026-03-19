import cv2
import sys
import os
import json
import numpy as np

# --- KONFIGURASI & KONSTANTA ---
FACE_SIZE = (200, 200)
BLUR_THRESHOLD = 30.0   # Dilonggarkan untuk kamera depan HP
THRESHOLD_MATCH = 80.0  # < 80: Approved (langsung cocok)
THRESHOLD_REVIEW = 100.0 # 80-100: Pending (Review HRD)

# Inisialisasi Cascade
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
profile_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_profileface.xml')
eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')

def apply_clahe(gray_img):
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray_img)

def get_blur_score(img):
    return cv2.Laplacian(img, cv2.CV_64F).var()

def fix_exif_rotation(img_path):
    """Memperbaiki orientasi gambar berdasarkan EXIF metadata dari kamera HP"""
    try:
        from PIL import Image
        pil_img = Image.open(img_path)
        exif = pil_img.getexif()
        orientation = exif.get(274, 1)  # Tag 274 = Orientation

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

def detect_faces_multi(gray, min_face_size):
    """Coba deteksi wajah di 4 orientasi (0, 90, 180, 270) + flip"""
    rotations = [
        (0, None),
        (90, cv2.ROTATE_90_CLOCKWISE),
        (180, cv2.ROTATE_180),
        (270, cv2.ROTATE_90_COUNTERCLOCKWISE),
    ]
    for angle, rot_code in rotations:
        test_gray = gray if rot_code is None else cv2.rotate(gray, rot_code)

        # Frontal face - strict
        faces = face_cascade.detectMultiScale(test_gray, 1.1, 5, minSize=(min_face_size, min_face_size))
        if len(faces) > 0:
            return faces, test_gray

        # Frontal face - longgar
        faces = face_cascade.detectMultiScale(test_gray, 1.05, 3, minSize=(min_face_size, min_face_size))
        if len(faces) > 0:
            return faces, test_gray

        # Flip horizontal
        flipped = cv2.flip(test_gray, 1)
        faces = face_cascade.detectMultiScale(flipped, 1.05, 3, minSize=(min_face_size, min_face_size))
        if len(faces) > 0:
            return faces, flipped

    return [], gray

def align_face(gray_img, face_rect):
    (x, y, w, h) = face_rect
    roi_gray = gray_img[y:y+h, x:x+w]
    
    eyes = eye_cascade.detectMultiScale(roi_gray, 1.1, 15, minSize=(w//6, h//6))
    
    if len(eyes) >= 2:
        eyes = sorted(eyes, key=lambda e: e[0])
        l_center = (int(eyes[0][0] + eyes[0][2]/2), int(eyes[0][1] + eyes[0][3]/2))
        r_center = (int(eyes[1][0] + eyes[1][2]/2), int(eyes[1][1] + eyes[1][3]/2))
        
        dy = r_center[1] - l_center[1]
        dx = r_center[0] - l_center[0]
        angle = np.degrees(np.arctan2(dy, dx))
        
        if abs(angle) < 30:
            center = (float(w / 2), float(h / 2))
            M = cv2.getRotationMatrix2D(center, angle, 1.0)
            rotated = cv2.warpAffine(roi_gray, M, (w, h), flags=cv2.INTER_CUBIC)
            return rotated
            
    return roi_gray

def preprocess_image(img_path):
    """Pipeline pemrosesan gambar dengan EXIF rotation handling"""
    if not os.path.exists(img_path):
        raise Exception("File gambar input tidak ditemukan.")

    # Coba baca dengan EXIF rotation fix dulu (kamera HP)
    img = fix_exif_rotation(img_path)
    if img is None:
        img = cv2.imread(img_path)
    
    if img is None:
        raise Exception("Gagal membaca file gambar (Corrupted/Not Valid).")
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # 1. Cek Kualitas: Blur
    blur_score = get_blur_score(gray)
    if blur_score < BLUR_THRESHOLD:
        raise Exception(f"Foto terlalu buram (Score: {round(blur_score, 1)}). Harap foto ulang.")

    h_img, w_img = gray.shape
    min_face_size = int(min(h_img, w_img) * 0.1)
    
    # 2. Deteksi Wajah (Multi-orientasi + flip)
    faces, gray = detect_faces_multi(gray, min_face_size)

    if len(faces) == 0:
        # Terakhir coba profil samping
        faces = profile_cascade.detectMultiScale(gray, 1.1, 3, minSize=(min_face_size, min_face_size))

    if len(faces) == 0:
        raise Exception("Wajah tidak terdeteksi. Pastikan pencahayaan cukup dan wajah terlihat jelas.")
    
    # Ambil wajah terbesar
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    (x, y, w, h) = faces[0]
    
    # 3. Cek Jarak
    face_ratio = max(w, h) / max(h_img, w_img)
    if face_ratio < 0.1:
        raise Exception("Wajah terlalu jauh. Mohon dekatkan kamera.")
    
    # 4. Alignment & Preprocessing
    aligned_face = align_face(gray, faces[0])
    resized_face = cv2.resize(aligned_face, FACE_SIZE, interpolation=cv2.INTER_AREA)
    denoised = cv2.bilateralFilter(resized_face, 5, 75, 75)
    final_img = apply_clahe(denoised)
    
    return final_img, blur_score

def verify_face(model_path, image_path):
    try:
        if not os.path.exists(model_path):
            raise Exception("Model biometrik user belum tersedia (Belum training).")

        # Parameter HARUS sama dengan train_face.py
        recognizer = cv2.face.LBPHFaceRecognizer_create(
            radius=1,
            neighbors=8,
            grid_x=8,
            grid_y=8,
            threshold=100.0
        )
        recognizer.read(model_path)

        processed_face = None
        blur_score = 0.0

        # Preprocess Image
        try:
            processed_face, blur_score = preprocess_image(image_path)
        except Exception as e_proc:
            print(json.dumps({
                "status": "success",
                "match": False,
                "confidence": 999,
                "verification_status": "PREPROCESSING_FAILED",
                "message": str(e_proc)
            }))
            return

        # Prediksi
        id_user, confidence = recognizer.predict(processed_face)

        # Logika Keputusan: LBPH 0 = Identik, >100 = Sangat Berbeda
        status_verifikasi = "REJECTED"
        is_match = False
        
        if confidence < THRESHOLD_MATCH:
            status_verifikasi = "APPROVED"
            is_match = True
        elif confidence < THRESHOLD_REVIEW:
            status_verifikasi = "PENDING"
            is_match = True
        
        print(json.dumps({
            "status": "success",
            "match": is_match,
            "verification_status": status_verifikasi,
            "confidence": round(confidence, 2),
            "blur_score": round(blur_score, 1),
            "user_id": id_user
        }))

    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Invalid arguments"}))
        sys.exit(1)

    m_path = sys.argv[1]
    i_path = sys.argv[2]

    verify_face(m_path, i_path)
