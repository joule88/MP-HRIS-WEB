# %% [markdown]
# # 🧠 Face Recognition Pipeline: Mandiri, Lengkap & Visual
# Ini adalah perpaduan kode yang **sangat sederhana** (mengambil 100 frame tanpa crop), 
# ditambah dengan **Gambar Pendukung (Visualisasi Matplotlib)** agar outputnya tidak sekadar
# teks melainkan bisa Anda lihat perubahan bentuk wajahnya per langkah!

# %% [markdown]
# ## Tahap 1: Persiapan Library

# %%
import cv2
import os
import numpy as np
import matplotlib.pyplot as plt
from sklearn.svm import SVC
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score

print("✅ Library ter-load dengan baik!")

# %% [markdown]
# ## Tahap 2: Ektraksi 100 Frame dari Video Asli 🎥 (Tanpa Potong Wajah)

# %%
VIDEO_DIR = r"C:\xampp\htdocs\mpg-hris\storage\app\private\face_videos"
kumpulan_gambar_mentah = {}  

print("🎬 Membaca video enrollment...\n")

for user_id in sorted(os.listdir(VIDEO_DIR)):
    video_path = os.path.join(VIDEO_DIR, user_id, "enrollment.mp4")
    if not os.path.exists(video_path): continue
        
    cap = cv2.VideoCapture(video_path)
    gambar_dikumpulkan = []
    
    while len(gambar_dikumpulkan) < 100: 
        ret, frame = cap.read()
        if not ret: break
            
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        frame_kecil = cv2.resize(gray, (128, 128)) # Dikecilkan langsung
        gambar_dikumpulkan.append(frame_kecil)
        
        # [BARU] Simpan langsung ke folder, agar Anda bisa lihat filenya!
        simpan_folder = f"hasil_1_ekstrak_asli/user_{user_id}"
        os.makedirs(simpan_folder, exist_ok=True)
        # Menulis foto ke harddisk
        cv2.imwrite(f"{simpan_folder}/frame_{len(gambar_dikumpulkan)}.jpg", frame_kecil)
            
    cap.release()
    kumpulan_gambar_mentah[user_id] = gambar_dikumpulkan
    print(f"   👤 User {user_id}: Berhasil mengambil {len(gambar_dikumpulkan)} gambar utuh.")

# %% [markdown]
# ## 📸 Mari Kita Intip Dulu Gambarnya!
# Berikut adalah bagaimana rupa 1 gambar contoh dari video (karena tanpa proses 
# crop/potong letak wajah, gambarnya sangat lebar dengan background-nya dominan)

# %%
# Tampilkan 4 user pertama
fig, axes = plt.subplots(1, 4, figsize=(15, 4))
user_terurut = list(kumpulan_gambar_mentah.keys())

for i, ax in enumerate(axes):
    if i < len(user_terurut):
        contoh_user = user_terurut[i]
        contoh_foto = kumpulan_gambar_mentah[contoh_user][10] # Ambil frame ke 10
        ax.imshow(contoh_foto, cmap='gray')
        ax.set_title(f"Frame Asli User {contoh_user}", fontsize=12, fontweight='bold')
    ax.axis('off')

plt.tight_layout()
plt.show()

# %% [markdown]
# ## Tahap 3: Fungsi Pola Jadi Angka (Algoritma LBP) 🔢

# %%
def ubah_wajah_jadi_angka_lbp(gambar):
    """
    1. Filter CLAHE: Terangkan bagian wajah yg kena bayangan/gelap.
    2. LBP (Radius 1): Sandi tekstur dengan membandingkan 8 titik berdekatan.
    3. Jadikan histogram.
    """
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    gambar_jelas = clahe.apply(gambar)
    
    tinggi, lebar = gambar_jelas.shape
    pola_lbp = np.zeros((tinggi, lebar), dtype=np.uint8)
    
    for y in range(1, tinggi-1):
        for x in range(1, lebar-1):
            pusat = gambar_jelas[y, x]
            biner = 0
            biner |= (gambar_jelas[y-1, x-1] >= pusat) << 7  
            biner |= (gambar_jelas[y-1, x]   >= pusat) << 6  
            biner |= (gambar_jelas[y-1, x+1] >= pusat) << 5  
            biner |= (gambar_jelas[y, x+1]   >= pusat) << 4  
            biner |= (gambar_jelas[y+1, x+1] >= pusat) << 3  
            biner |= (gambar_jelas[y+1, x]   >= pusat) << 2  
            biner |= (gambar_jelas[y+1, x-1] >= pusat) << 1  
            biner |= (gambar_jelas[y, x-1]   >= pusat) << 0  
            
            pola_lbp[y, x] = biner
            
    histogram,_ = np.histogram(pola_lbp, bins=256, range=(0, 256), density=True)
    
    # Supaya bisa dilihat untuk chart, saya return pola_lbp nya juga (Tapi histogram yg utama!)
    return histogram, gambar_jelas, pola_lbp

print("✅ Fungsi ekstraksi LBP sudah dibuat!")

# %% [markdown]
# ## 📸 Mari Kita Intip BAGAIMANA LBP Bekerja (Proses Sulapnya)
# Apa yang terjadi pada gambar kalau dimasukkan ke fungsi LBP di atas?

# %%
contoh_foto = kumpulan_gambar_mentah[user_terurut[0]][10]
hist, efek_clahe, efek_lbp = ubah_wajah_jadi_angka_lbp(contoh_foto)

fig, axes = plt.subplots(1, 3, figsize=(15, 5))

# Foto 1: Original
axes[0].imshow(contoh_foto, cmap='gray')
axes[0].set_title("1. Hitam Putih Original", fontsize=12)

# Foto 2: Setelah CLAHE
axes[1].imshow(efek_clahe, cmap='gray')
axes[1].set_title("2. Efek CLAHE (Pinggiran Menonjol)", fontsize=12)

# Foto 3: Hasil Akhir matriks LBP
axes[2].imshow(efek_lbp, cmap='gray')
axes[2].set_title("3. Sandi Tekstur LBP", fontsize=12)

for ax in axes:
    ax.axis('off')
    
plt.tight_layout()
plt.show()

# Kemudian ini histogram dari LBP (Bentuk Akhirnya Berupa Grafik/Angka 256 elemen)
plt.figure(figsize=(12, 3))
plt.plot(hist, color='blue', linewidth=2)
plt.title(f"4. Dan Inilah Histogram/Angka Akhirnya yang Dikirim ke Otak SVM", fontsize=12)
plt.xlabel("Kode LBP (0 - 255)")
plt.ylabel("Berapa Kali Muncul")
plt.fill_between(range(256), hist, color='blue', alpha=0.3)
plt.show()

# %% [markdown]
# ## Tahap 4: Proses Ratusan Gambar Sekaligus 🔄

# %%
X_all = [] # Simpan hasil array angkanya
y_all = [] # Simpan User ID

print("⚙️ Tolong ditunggu, mengolah seluruh gambar menjadi Array Matriks LBP...")

for user_id, daftar_foto in kumpulan_gambar_mentah.items():
    counter_foto = 1 # Penghitung nama file
    
    for foto in daftar_foto:
        fitur_angka, efek_clahe, efek_lbp = ubah_wajah_jadi_angka_lbp(foto)       
        
        # [BARU] Menyimpan foto hasil operasi komputer ke folder!
        folder_clahe = f"hasil_2_clahe/user_{user_id}"
        folder_lbp = f"hasil_3_lbp/user_{user_id}"
        os.makedirs(folder_clahe, exist_ok=True)
        os.makedirs(folder_lbp, exist_ok=True)
        
        cv2.imwrite(f"{folder_clahe}/clahe_{counter_foto}.jpg", efek_clahe)
        cv2.imwrite(f"{folder_lbp}/lbp_{counter_foto}.jpg", efek_lbp)
        counter_foto += 1
        
        X_all.append(fitur_angka)
        y_all.append(user_id)

X = np.array(X_all)
y = np.array(y_all)

print(f"✅ Otak buatan memiliki {len(X)} sampel matriks utuh!")

# %% [markdown]
# ## Tahap 5: Belajar (Training 80% Data ke SVM) 🎓
# Mari kita bedah *"Jeroan"* mesin ini! Karena selama ini selesainya terlalu instan/cepat, 
# kita akan menyalakan mode `verbose=True` agar mesin melaporkan jurnal perhitungannya ke kita.
# Serta kita gunakan trik `PCA` untuk menekan 256 angka abstrak tadi jadi grafik kordinat 2D (X dan Y) 
# yang bisa dilihat mata manusia!

# %%
from sklearn.decomposition import PCA

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test) 

print(f"🧠 AI (SVM) mulai menghafal rumus batas untuk {len(X_train)} gambar Training...")
print("=================== ISI KEPALA MESIN (LIBSVM LOG) ===================")
# verbose=True = Tampilkan seluruh hitungan angka kalibrasi matematisnya
ai_model = SVC(kernel='rbf', C=5, verbose=True)
ai_model.fit(X_train_scaled, y_train)
print("=====================================================================")
print(f"✅ Selesai Menghafal!")

# %% [markdown]
# ## 📸 Visualisasi Dimensi Belajar Komputer (PCA)
# Komputer menganalisa grafik dari 256 sudut/dimensi secara bersamaan. 
# Manusia hanya mampu melihat 2 Dimensi (kiri-kanan X, atas-bawah Y).
# Berikut adalah bagaimana komputer membuat "wilayah zona wajah" dari setiap User.

# %%
# Paksa padatkan 256 fitur LBP menjadi hanya 2 fitur untuk bisa dipajang
pca = PCA(n_components=2)
X_train_2d = pca.fit_transform(X_train_scaled)

plt.figure(figsize=(10, 6))
colors = ['crimson', 'forestgreen', 'royalblue', 'orange', 'purple', 'black']
user_unik = np.unique(y_train)

for idx, user_vid in enumerate(user_unik):
    mask = (y_train == user_vid)
    plt.scatter(X_train_2d[mask, 0], X_train_2d[mask, 1], 
                c=colors[idx % len(colors)], label=f"Zona Wajah {user_vid}", 
                alpha=0.8, edgecolors='k', s=60)

# Tambahkan Judul & Aksesoris 
plt.title("Visualisasi Peta Memori Otak SVM Setelah Training", fontsize=14, fontweight='bold')
plt.xlabel("Dimensi Pola Pipi & Mata (Disederhanakan)")
plt.ylabel("Dimensi Pola Rahang & Bibir (Disederhanakan)")
plt.legend()
plt.grid(True, linestyle='--', alpha=0.5)

# Bikin kotak merah batas agar terlihat seperti area pemisah (Decision Boundary)
plt.axvline(x=0, color='grey', linewidth=1)
plt.axhline(y=0, color='grey', linewidth=1)

plt.show()

# %% [markdown]
# ## Tahap 6: Ujian AI (Testing Ulang ke 20% Data) 📝

# %%
jawaban_ai = ai_model.predict(X_test_scaled)
akurasi = accuracy_score(y_test, jawaban_ai)

# Supaya menarik, kita tampilkan pakai Plot!
# Kita hitung dulu akurasi per User
user_unik = np.unique(y_test)
akurasi_tiap_user = {}

for u in user_unik:
    benar = sum(1 for asli, tebak in zip(y_test, jawaban_ai) if asli == u and tebak == u)
    total_ujian = sum(1 for asli in y_test if asli == u)
    akurasi_tiap_user[u] = (benar / total_ujian) * 100 if total_ujian > 0 else 0

plt.figure(figsize=(8, 4))
bars = plt.bar(akurasi_tiap_user.keys(), akurasi_tiap_user.values(), color=['red','green','blue','orange'])
plt.title(f"Akurasi Test Keseluruhan: {akurasi*100:.2f} %", fontsize=14, fontweight='bold')
plt.xlabel("ID Pegawai")
plt.ylabel("Akurasi (%)")
plt.ylim(0, 110)

# Tambah text persen di atas tiap batang grafiknya
for bar in bars:
    plt.text(bar.get_x() + bar.get_width()/2 - 0.15, bar.get_height() + 2, 
             f"{bar.get_height():.0f}%", fontweight='bold')

plt.show()

print("\nDetail Singkat (10 Tebakan Pertama):")
for i in range(min(10, len(y_test))):
    kunci = y_test[i]
    tebakan = jawaban_ai[i]
    if kunci == tebakan:
        print(f"[{i+1:02d}] Asli: User {kunci:<3} \t|Tebakan: User {tebakan:<3} ✅")
    else:
        print(f"[{i+1:02d}] Asli: User {kunci:<3} \t|Tebakan: User {tebakan:<3} ❌")

