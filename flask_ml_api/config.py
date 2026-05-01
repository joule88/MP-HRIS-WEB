import os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

STORAGE_DIR = os.path.join(BASE_DIR, 'storage')
DATASETS_DIR = os.path.join(STORAGE_DIR, 'face_datasets')
MODELS_DIR = os.path.join(STORAGE_DIR, 'face_models')
TEMP_DIR = os.path.join(STORAGE_DIR, 'temp')

MAX_CONTENT_LENGTH = 50 * 1024 * 1024  # 50MB

API_KEY = os.environ.get('FLASK_ML_API_KEY', 'dev-api-key-mpg-hris')

for d in [STORAGE_DIR, DATASETS_DIR, MODELS_DIR, TEMP_DIR]:
    os.makedirs(d, exist_ok=True)
