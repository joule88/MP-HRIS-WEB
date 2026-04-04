import cv2
import os
import sys
import numpy as np
import json

# --- KONSTANTA & FUNGSI SHARED (Harus sama dengan verify_face.py) ---
FACE_SIZE = (200, 200)
# Pastikan opencv-contrib-python terinstall untuk modul face
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
profile_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_profileface.xml')
eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')

def apply_clahe(gray_img):
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray_img)

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

def preprocess_for_training(img_path):
    """Versi simplified dari preprocess untuk training data"""
    img = cv2.imread(img_path)
    if img is None: return None
        
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # Deteksi Wajah
    faces = face_cascade.detectMultiScale(gray, 1.1, 5)
    if len(faces) == 0:
        faces = profile_cascade.detectMultiScale(gray, 1.1, 5)
        if len(faces) == 0:
            gray = cv2.flip(gray, 1)
            faces = profile_cascade.detectMultiScale(gray, 1.1, 5)
    
    if len(faces) == 0: return None
    
    # Ambil wajah terbesar
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    
    # Alignment & Enhancement
    aligned = align_face(gray, faces[0])
    resized = cv2.resize(aligned, FACE_SIZE, interpolation=cv2.INTER_AREA)
    denoised = cv2.bilateralFilter(resized, 5, 75, 75)
    final = apply_clahe(denoised)
    
    return final

def train_model(user_id, dataset_path, model_storage_path):
    try:
        if not os.path.exists(dataset_path):
            raise Exception(f"Dataset path not found: {dataset_path}")

        face_samples = []
        ids = []

        # Setup LBPH dengan parameter optimal dari simulasi Anda
        recognizer = cv2.face.LBPHFaceRecognizer_create(
            radius=1, 
            neighbors=8, 
            grid_x=8, 
            grid_y=8,
            threshold=100.0
        )

        image_paths = [os.path.join(dataset_path, f) for f in os.listdir(dataset_path) if f.endswith(('.jpg', '.jpeg', '.png'))]

        if len(image_paths) == 0:
            raise Exception("No images found in dataset path")

        success_count = 0
        for image_path in image_paths:
            processed_face = preprocess_for_training(image_path)
            
            if processed_face is not None:
                face_samples.append(processed_face)
                ids.append(int(user_id))
                success_count += 1
            else:
                # Log gambar yang gagal dideteksi wajahnya
                pass 

        if len(face_samples) == 0:
            raise Exception("Wajah tidak terdeteksi pada semua foto pendaftaran. Pastikan foto jelas dan pencahayaan cukup.")

        # Latih model
        recognizer.train(face_samples, np.array(ids))

        # Simpan model
        if not os.path.exists(model_storage_path):
            os.makedirs(model_storage_path)

        model_file = os.path.join(model_storage_path, f"user_{user_id}.yml")
        recognizer.save(model_file)

        print(json.dumps({
            "status": "success",
            "message": f"Model trained successfully with {success_count} valid samples",
            "model_path": model_file
        }))

    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))

if __name__ == "__main__":
    if len(sys.argv) < 4:  # Fixed check to ensure 3 arguments are present
        print(json.dumps({"status": "error", "message": "Invalid arguments. Usage: python train_face.py <user_id> <dataset_path> <model_path>"}))
        sys.exit(1)

    u_id = sys.argv[1]
    d_path = sys.argv[2]
    m_path = sys.argv[3]

    train_model(u_id, d_path, m_path)
