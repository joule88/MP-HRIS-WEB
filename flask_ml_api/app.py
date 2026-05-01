import os
import uuid
import logging
from functools import wraps
from flask import Flask, request, jsonify
from flask_cors import CORS

from config import DATASETS_DIR, MODELS_DIR, TEMP_DIR, MAX_CONTENT_LENGTH, API_KEY
from services.extract_service import extract_frames
from services.train_service import train_model
from services.verify_service import verify_face

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s'
)
logger = logging.getLogger(__name__)

app = Flask(__name__)
app.config['MAX_CONTENT_LENGTH'] = MAX_CONTENT_LENGTH
CORS(app)


def require_api_key(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        key = request.headers.get('X-API-Key', '')
        if key != API_KEY:
            return jsonify({"status": "error", "message": "API key tidak valid."}), 401
        return f(*args, **kwargs)
    return decorated


@app.route('/health', methods=['GET'])
def health():
    model_exists = os.path.exists(os.path.join(MODELS_DIR, 'face_model.pkl'))
    return jsonify({
        "status": "ok",
        "model_loaded": model_exists,
        "datasets_dir": DATASETS_DIR,
        "models_dir": MODELS_DIR,
    })


@app.route('/extract-frames', methods=['POST'])
@require_api_key
def api_extract_frames():
    if 'video' not in request.files:
        return jsonify({"status": "error", "message": "File video tidak ditemukan."}), 400

    user_id = request.form.get('user_id')
    target_frames = int(request.form.get('target_frames', 200))

    if not user_id:
        return jsonify({"status": "error", "message": "user_id wajib diisi."}), 400

    video_file = request.files['video']
    temp_video = os.path.join(TEMP_DIR, f"enroll_{user_id}_{uuid.uuid4().hex[:8]}.mp4")

    try:
        video_file.save(temp_video)

        output_dir = os.path.join(DATASETS_DIR, str(user_id))

        result = extract_frames(temp_video, output_dir, target_frames=target_frames)

        logger.info(
            f"Extract frames berhasil untuk user {user_id}. "
            f"Frames: {result['total_extracted']}"
        )

        return jsonify({
            "status": "success",
            "message": f"Berhasil mengekstrak {result['total_extracted']} frame wajah frontal",
            **result
        })

    except Exception as e:
        logger.error(f"Extract frames GAGAL untuk user {user_id}: {str(e)}")
        return jsonify({"status": "error", "message": str(e)}), 500

    finally:
        if os.path.exists(temp_video):
            os.remove(temp_video)


@app.route('/train-model', methods=['POST'])
@require_api_key
def api_train_model():
    data = request.get_json(silent=True) or {}
    approved_user_ids = data.get('approved_user_ids')

    if not approved_user_ids or not isinstance(approved_user_ids, list):
        return jsonify({
            "status": "error",
            "message": "approved_user_ids wajib diisi sebagai array."
        }), 400

    approved_str = [str(uid) for uid in approved_user_ids]

    try:
        logger.info(
            f"Memulai training model SVM. "
            f"Approved users: {approved_str}"
        )

        result = train_model(DATASETS_DIR, MODELS_DIR, approved_str)

        logger.info(
            f"Training selesai. Users: {result['total_users']}, "
            f"CV: {result['cv_score']*100:.2f}%, "
            f"Test: {result['test_accuracy']*100:.2f}%"
        )

        return jsonify({
            "status": "success",
            "message": (
                f"Model SVM berhasil dilatih. "
                f"{result['total_users']} user, "
                f"CV={result['cv_score']*100:.2f}%, "
                f"Test={result['test_accuracy']*100:.2f}%"
            ),
            **result
        })

    except Exception as e:
        logger.error(f"Training model GAGAL: {str(e)}")
        return jsonify({"status": "error", "message": str(e)}), 500


@app.route('/verify-face', methods=['POST'])
@require_api_key
def api_verify_face():
    if 'file' not in request.files:
        return jsonify({"status": "error", "message": "File tidak ditemukan."}), 400

    user_id = request.form.get('user_id')
    is_video = request.form.get('is_video', 'false').lower() == 'true'

    if not user_id:
        return jsonify({"status": "error", "message": "user_id wajib diisi."}), 400

    uploaded_file = request.files['file']
    ext = 'mp4' if is_video else 'jpg'
    temp_file = os.path.join(TEMP_DIR, f"verify_{user_id}_{uuid.uuid4().hex[:8]}.{ext}")

    try:
        uploaded_file.save(temp_file)

        result = verify_face(MODELS_DIR, user_id, temp_file, is_video=is_video)

        logger.info(
            f"Verifikasi user {user_id}: "
            f"status={result.get('verification_status')}, "
            f"match={result.get('match')}, "
            f"svm_df={result.get('svm_df')}, "
            f"confidence={result.get('confidence')}, "
            f"predicted={result.get('predicted_user')}, "
            f"blur={result.get('blur_score')}, "
            f"frames_approved={result.get('frames_approved')}/{result.get('frames_total')}"
        )

        return jsonify(result)

    except Exception as e:
        logger.error(f"Verifikasi GAGAL untuk user {user_id}: {str(e)}")
        return jsonify({"status": "error", "message": str(e)}), 500

    finally:
        if os.path.exists(temp_file):
            os.remove(temp_file)


if __name__ == '__main__':
    logger.info("=" * 50)
    logger.info("MPG HRIS - Flask ML API Server")
    logger.info(f"Datasets: {DATASETS_DIR}")
    logger.info(f"Models:   {MODELS_DIR}")
    logger.info("=" * 50)

    app.run(host='0.0.0.0', port=5000, debug=True)
