import cv2
import os
import sys
import json
import numpy as np
import joblib
from sklearn.svm import SVC
from sklearn.preprocessing import StandardScaler
from sklearn.pipeline import Pipeline
from sklearn.model_selection import train_test_split, GridSearchCV
from skimage.feature import local_binary_pattern

sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..'))

from utils.lbp_features import (
    extract_lbp_features, extract_lbp_from_augmented,
    augmentasi_lbp, preprocess_face, FACE_SIZE, LBP_SCALES
)


def load_images(dataset_path):
    valid_ext = ('.jpg', '.jpeg', '.png')
    images = []

    if not os.path.exists(dataset_path):
        return images

    files = sorted([
        f for f in os.listdir(dataset_path)
        if f.lower().endswith(valid_ext) and (
            f.startswith('frame_') or f.startswith('selfie_')
        )
    ])

    for filename in files:
        filepath = os.path.join(dataset_path, filename)
        img = cv2.imread(filepath, cv2.IMREAD_GRAYSCALE)
        if img is not None:
            if img.shape != FACE_SIZE:
                img = cv2.resize(img, FACE_SIZE, interpolation=cv2.INTER_AREA)
            images.append(img)

    return images


def train_model(base_datasets_path, model_output_path, approved_user_ids=None):
    if not os.path.exists(base_datasets_path):
        raise Exception(f"Base datasets path tidak ditemukan: {base_datasets_path}")

    user_images_dict = {}
    label_map = {}
    user_stats = {}

    for folder_name in sorted(os.listdir(base_datasets_path)):
        folder_path = os.path.join(base_datasets_path, folder_name)
        if not os.path.isdir(folder_path):
            continue

        user_id = folder_name

        if approved_user_ids and user_id not in approved_user_ids:
            continue

        images = load_images(folder_path)
        if len(images) < 10:
            continue

        label_map[user_id] = user_id
        user_images_dict[user_id] = images

    if len(label_map) == 0:
        raise Exception("Tidak ada user dengan dataset valid (minimal 10 gambar per user).")

    if len(label_map) < 2:
        raise Exception(
            "Minimal 2 user yang sudah di-approve diperlukan untuk melatih model SVM. "
            f"Saat ini baru {len(label_map)} user."
        )

    X_ori = []
    y_ori = []
    X_aug = []
    y_aug = []

    for user_id, images in user_images_dict.items():
        ori_count = 0
        aug_count = 0

        for img in images:
            proc_img = preprocess_face(img)
            features = extract_lbp_features(proc_img)
            X_ori.append(features)
            y_ori.append(user_id)
            ori_count += 1

            pola = local_binary_pattern(proc_img, P=8, R=1, method='uniform')
            lbp_uint8 = (pola * 255.0 / 9).astype(np.uint8)
            variasi = augmentasi_lbp(lbp_uint8)
            for aug_img in variasi:
                aug_features = extract_lbp_from_augmented(aug_img)
                X_aug.append(aug_features)
                y_aug.append(user_id)
                aug_count += 1

        user_stats[user_id] = ori_count + aug_count

    X = np.array(X_ori + X_aug)
    y = np.array(y_ori + y_aug)

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )

    param_grid = {
        'svm__C': [0.1, 1, 10, 20],
        'svm__gamma': ['scale', 'auto', 0.01, 0.001, 0.1]
    }

    pipe_search = Pipeline([
        ('scaler', StandardScaler()),
        ('svm', SVC(kernel='rbf', probability=False))
    ])

    grid = GridSearchCV(
        pipe_search, param_grid,
        cv=5, scoring='accuracy',
        refit=False, return_train_score=True, verbose=0
    )
    grid.fit(X_train, y_train)

    best_C = grid.best_params_['svm__C']
    best_gamma = grid.best_params_['svm__gamma']
    cv_score = grid.best_score_

    model_final = Pipeline([
        ('scaler', StandardScaler()),
        ('svm', SVC(kernel='rbf', C=best_C, gamma=best_gamma, probability=True))
    ])
    model_final.fit(X_train, y_train)
    test_acc = model_final.score(X_test, y_test)

    if not os.path.exists(model_output_path):
        os.makedirs(model_output_path)

    model_file = os.path.join(model_output_path, "face_model.pkl")
    scaler_file = os.path.join(model_output_path, "face_scaler.pkl")
    labels_file = os.path.join(model_output_path, "face_labels.json")

    temp_model_file = os.path.join(model_output_path, "face_model_temp.pkl")
    temp_scaler_file = os.path.join(model_output_path, "face_scaler_temp.pkl")
    temp_labels_file = os.path.join(model_output_path, "face_labels_temp.json")

    joblib.dump(model_final.named_steps['svm'], temp_model_file)
    joblib.dump(model_final.named_steps['scaler'], temp_scaler_file)

    labels_data = {
        "classes": list(model_final.named_steps['svm'].classes_),
        "user_ids": list(label_map.keys()),
        "best_C": best_C,
        "best_gamma": str(best_gamma),
        "cv_score": round(cv_score, 4),
        "test_accuracy": round(test_acc, 4),
    }
    with open(temp_labels_file, 'w') as f:
        json.dump(labels_data, f, indent=2)

    os.replace(temp_model_file, model_file)
    os.replace(temp_scaler_file, scaler_file)
    os.replace(temp_labels_file, labels_file)

    return {
        "total_users": len(label_map),
        "user_stats": user_stats,
        "total_samples": len(X),
        "train_samples": len(X_train),
        "test_samples": len(X_test),
        "feature_dimension": int(X.shape[1]),
        "best_C": best_C,
        "best_gamma": str(best_gamma),
        "cv_score": round(cv_score, 4),
        "test_accuracy": round(test_acc, 4),
        "classes": list(model_final.named_steps['svm'].classes_),
        "model_path": model_file,
        "scaler_path": scaler_file,
        "labels_path": labels_file
    }
