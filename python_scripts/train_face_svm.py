import cv2
import os
import sys
import json
import numpy as np
import joblib
from sklearn.svm import SVC
from sklearn.preprocessing import StandardScaler

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lbp_features import extract_lbp_features, FACE_SIZE

UNKNOWN_LABEL = "unknown"


def augment_image(image):
    augmented = []

    flipped = cv2.flip(image, 1)
    augmented.append(flipped)

    bright_factor = np.random.uniform(0.7, 1.3)
    bright = np.clip(
        image.astype(np.float32) * bright_factor, 0, 255
    ).astype(np.uint8)
    augmented.append(bright)

    return augmented


def load_images(dataset_path):
    valid_ext = ('.jpg', '.jpeg', '.png')
    images = []

    if not os.path.exists(dataset_path):
        return images

    files = sorted([
        f for f in os.listdir(dataset_path)
        if f.lower().endswith(valid_ext)
    ])

    for filename in files:
        filepath = os.path.join(dataset_path, filename)
        img = cv2.imread(filepath, cv2.IMREAD_GRAYSCALE)
        if img is not None:
            img = cv2.resize(img, FACE_SIZE, interpolation=cv2.INTER_AREA)
            images.append(img)

    return images


def generate_unknown_negatives(all_images, num_samples):
    negatives = []
    n_total = len(all_images)
    if n_total == 0:
        return negatives

    for i in range(num_samples):
        method = i % 5
        base_img = all_images[i % n_total].copy()
        h, w = base_img.shape

        if method == 0:
            grid = 4
            bh, bw = h // grid, w // grid
            patches = []
            for gy in range(grid):
                for gx in range(grid):
                    patch = base_img[gy*bh:(gy+1)*bh, gx*bw:(gx+1)*bw].copy()
                    patches.append(patch)
            np.random.shuffle(patches)
            result = np.zeros_like(base_img)
            idx = 0
            for gy in range(grid):
                for gx in range(grid):
                    result[gy*bh:(gy+1)*bh, gx*bw:(gx+1)*bw] = patches[idx]
                    idx += 1
            negatives.append(result)

        elif method == 1:
            img1 = all_images[i % n_total]
            img2 = all_images[(i + n_total // 2) % n_total]
            alpha = np.random.uniform(0.3, 0.7)
            blended = cv2.addWeighted(img1, alpha, img2, 1 - alpha, 0)
            mid = h // 2
            result = blended.copy()
            result[:mid, :] = cv2.flip(result[:mid, :], 1)
            result[mid:, :] = cv2.flip(result[mid:, :], 0)
            negatives.append(result)

        elif method == 2:
            result = base_img.copy()
            cx, cy = w // 2, h // 2
            angle = np.random.choice([90, 180, 270])
            M = cv2.getRotationMatrix2D((cx, cy), angle, 0.8)
            result = cv2.warpAffine(result, M, (w, h),
                                    borderMode=cv2.BORDER_REFLECT)
            noise = np.random.normal(0, 25, result.shape).astype(np.float32)
            result = np.clip(result.astype(np.float32) + noise, 0, 255).astype(np.uint8)
            negatives.append(result)

        elif method == 3:
            result = base_img.copy()
            strip_h = h // 6
            indices = list(range(6))
            np.random.shuffle(indices)
            shuffled = np.zeros_like(result)
            for j, src_idx in enumerate(indices):
                src_start = src_idx * strip_h
                dst_start = j * strip_h
                src_end = min(src_start + strip_h, h)
                dst_end = min(dst_start + strip_h, h)
                copy_h = min(src_end - src_start, dst_end - dst_start)
                shuffled[dst_start:dst_start+copy_h, :] = result[src_start:src_start+copy_h, :]
            if np.random.random() > 0.5:
                shuffled = cv2.flip(shuffled, -1)
            negatives.append(shuffled)

        elif method == 4:
            noise = np.random.randint(0, 256, FACE_SIZE, dtype=np.uint8)
            noise = cv2.GaussianBlur(noise, (7, 7), 0)
            negatives.append(noise)

    return negatives


def train_model(base_datasets_path, model_output_path, approved_user_ids=None):
    try:
        if not os.path.exists(base_datasets_path):
            raise Exception(f"Base datasets path tidak ditemukan: {base_datasets_path}")

        X_all = []
        y_all = []
        label_map = {}
        all_images = []
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
            all_images.extend(images)

            count = 0
            for img in images:
                features = extract_lbp_features(img)
                X_all.append(features)
                y_all.append(user_id)
                count += 1

                for aug_img in augment_image(img):
                    aug_features = extract_lbp_features(aug_img)
                    X_all.append(aug_features)
                    y_all.append(user_id)
                    count += 1

            user_stats[user_id] = count

        if len(label_map) == 0:
            raise Exception("Tidak ada user dengan dataset valid (minimal 10 gambar per user).")

        n_positive = len(X_all)
        unknown_count = max(n_positive // 2, 50)
        unknown_images = generate_unknown_negatives(all_images, unknown_count)

        unknown_sample_count = 0
        for neg_img in unknown_images:
            features = extract_lbp_features(neg_img)
            X_all.append(features)
            y_all.append(UNKNOWN_LABEL)
            unknown_sample_count += 1

        X = np.array(X_all)
        y = np.array(y_all)

        scaler = StandardScaler()
        X_scaled = scaler.fit_transform(X)

        svm = SVC(
            kernel='rbf',
            C=10,
            gamma='scale',
            probability=True,
            class_weight='balanced',
            random_state=42,
            decision_function_shape='ovr'
        )
        svm.fit(X_scaled, y)

        if not os.path.exists(model_output_path):
            os.makedirs(model_output_path)

        model_file = os.path.join(model_output_path, "face_model.pkl")
        scaler_file = os.path.join(model_output_path, "face_scaler.pkl")
        labels_file = os.path.join(model_output_path, "face_labels.json")

        joblib.dump(svm, model_file)
        joblib.dump(scaler, scaler_file)

        labels_data = {
            "classes": list(svm.classes_),
            "user_ids": list(label_map.keys()),
            "unknown_label": UNKNOWN_LABEL,
        }
        with open(labels_file, 'w') as f:
            json.dump(labels_data, f, indent=2)

        print(json.dumps({
            "status": "success",
            "message": (
                f"Model SVM multi-class berhasil dilatih dengan "
                f"{len(label_map)} user dan {n_positive} sampel positif + "
                f"{unknown_sample_count} sampel unknown"
            ),
            "total_users": len(label_map),
            "user_stats": user_stats,
            "samples_positive": n_positive,
            "samples_unknown": unknown_sample_count,
            "total_samples": len(X),
            "feature_dimension": X.shape[1],
            "classes": list(svm.classes_),
            "model_path": model_file,
            "scaler_path": scaler_file,
            "labels_path": labels_file
        }))

    except Exception as e:
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({
            "status": "error",
            "message": (
                "Usage: python train_face_svm.py <base_datasets_path> "
                "<model_output_path> [user_id1,user_id2,...]"
            )
        }))
        sys.exit(1)

    b_path = sys.argv[1]
    m_path = sys.argv[2]
    approved_ids = None
    if len(sys.argv) > 3:
        approved_ids = sys.argv[3].split(',')

    train_model(b_path, m_path, approved_ids)
