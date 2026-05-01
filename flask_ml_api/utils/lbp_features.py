import cv2
import numpy as np
from skimage.feature import local_binary_pattern

FACE_SIZE = (128, 128)
LBP_SCALES = [(8, 1), (16, 2), (24, 3)]
LBP_TOTAL_BINS = sum(P + 2 for P, R in LBP_SCALES)


def apply_clahe(gray_img):
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    return clahe.apply(gray_img)


def preprocess_face(image):
    if len(image.shape) == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    if image.shape != FACE_SIZE:
        image = cv2.resize(image, FACE_SIZE, interpolation=cv2.INTER_AREA)

    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    image = clahe.apply(image)
    return image


def extract_lbp_features(image):
    if len(image.shape) == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

    if image.shape != FACE_SIZE:
        image = cv2.resize(image, FACE_SIZE, interpolation=cv2.INTER_AREA)

    hists = []
    for P, R in LBP_SCALES:
        bins = P + 2
        pola = local_binary_pattern(image, P=P, R=R, method='uniform')
        hist, _ = np.histogram(pola, bins=bins, range=(0, bins), density=False)
        hists.append(hist)

    return np.concatenate(hists).astype(np.float64)


def extract_lbp_from_augmented(lbp_uint8):
    hists = []
    for P, R in LBP_SCALES:
        bins = P + 2
        hist, _ = np.histogram(lbp_uint8, bins=bins, range=(0, 256), density=False)
        hists.append(hist)
    return np.concatenate(hists).astype(np.float64)


def augmentasi_lbp(lbp_uint8):
    hasil = []
    hasil.append(cv2.flip(lbp_uint8, 1))
    hasil.append(cv2.add(lbp_uint8, 30))
    hasil.append(cv2.subtract(lbp_uint8, 30))
    hasil.append(cv2.GaussianBlur(lbp_uint8, (5, 5), 0))
    noise = np.random.normal(0, 10, lbp_uint8.shape).astype(np.int16)
    noisy = np.clip(lbp_uint8.astype(np.int16) + noise, 0, 255).astype(np.uint8)
    hasil.append(noisy)
    M = cv2.getRotationMatrix2D((64, 64), 5, 1.0)
    hasil.append(cv2.warpAffine(lbp_uint8, M, (128, 128)))
    M2 = cv2.getRotationMatrix2D((64, 64), -5, 1.0)
    hasil.append(cv2.warpAffine(lbp_uint8, M2, (128, 128)))
    return hasil
