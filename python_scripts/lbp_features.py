"""
Modul ekstraksi fitur wajah: Multi-scale LBP (Local Binary Pattern).
Menggunakan LBP pada dua skala (radius 1 dan radius 2) dengan
spatial histogram grid 8x8 untuk diskriminasi identitas.
"""
import cv2
import numpy as np

FACE_SIZE = (128, 128)
GRID_X = 8
GRID_Y = 8


def apply_clahe(gray_img):
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray_img)


def _lbp_uniform(image, n_points=8, radius=1):
    rows, cols = image.shape
    img = image.astype(np.float64)

    r = radius
    if r >= rows // 2 or r >= cols // 2:
        return np.zeros((rows, cols), dtype=np.float64)

    center = img[r:rows - r, r:cols - r]
    out_h, out_w = center.shape
    n_bins = n_points + 2

    angles = np.linspace(0, 2 * np.pi, n_points, endpoint=False)

    bits = np.zeros((n_points, out_h, out_w), dtype=np.uint8)
    for i, angle in enumerate(angles):
        dy = -radius * np.cos(angle)
        dx = radius * np.sin(angle)

        iy, ix = int(round(dy)), int(round(dx))
        neighbor = img[r + iy:r + iy + out_h, r + ix:r + ix + out_w]

        if neighbor.shape == center.shape:
            bits[i] = (neighbor >= center).astype(np.uint8)

    pattern = np.zeros((out_h, out_w), dtype=np.int32)
    for i in range(n_points):
        pattern |= bits[i].astype(np.int32) << i

    transitions = np.zeros((out_h, out_w), dtype=np.int32)
    for i in range(n_points):
        bit_cur = (pattern >> i) & 1
        bit_next = (pattern >> ((i + 1) % n_points)) & 1
        transitions += (bit_cur != bit_next).astype(np.int32)

    bit_count = np.zeros((out_h, out_w), dtype=np.float64)
    for i in range(n_points):
        bit_count += ((pattern >> i) & 1).astype(np.float64)

    output = np.full((rows, cols), float(n_points + 1))
    uniform_mask = transitions <= 2
    result = np.where(uniform_mask, bit_count, float(n_points + 1))
    output[r:rows - r, r:cols - r] = result

    return output


def _extract_lbp_hist(lbp_map, n_bins, grid_x, grid_y):
    h, w = lbp_map.shape
    region_h = h // grid_y
    region_w = w // grid_x

    features = []
    for gy in range(grid_y):
        for gx in range(grid_x):
            region = lbp_map[
                gy * region_h:(gy + 1) * region_h,
                gx * region_w:(gx + 1) * region_w
            ]
            hist, _ = np.histogram(
                region, bins=n_bins, range=(0, n_bins), density=True
            )
            features.extend(hist)

    return features


def extract_lbp_features(image):
    if len(image.shape) == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    if image.shape != FACE_SIZE:
        image = cv2.resize(image, FACE_SIZE, interpolation=cv2.INTER_AREA)

    image = cv2.GaussianBlur(image, (3, 3), 0)
    image = apply_clahe(image)

    all_features = []

    lbp_r1 = _lbp_uniform(image, n_points=8, radius=1)
    n_bins_r1 = 8 + 2
    hist_r1 = _extract_lbp_hist(lbp_r1, n_bins_r1, GRID_X, GRID_Y)
    all_features.extend(hist_r1)

    lbp_r2 = _lbp_uniform(image, n_points=16, radius=2)
    n_bins_r2 = 16 + 2
    hist_r2 = _extract_lbp_hist(lbp_r2, n_bins_r2, GRID_X, GRID_Y)
    all_features.extend(hist_r2)

    return np.array(all_features, dtype=np.float64)
