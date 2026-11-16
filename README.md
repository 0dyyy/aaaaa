# 🥗 ASVÍS
A web that serves humanity

ASVÍS adalah platform asisten kesehatan cerdas berbasis AI yang membantu pengguna melacak asupan nutrisi, aktivitas fisik, serta memberikan rekomendasi gaya hidup sehat yang terintegrasi secara *real-time* dengan data kualitas udara lingkungan.

---

## 👥 Anggota Kelompok

| NPM          | Nama Lengkap        | Peran / Tugas                  |
| :----------- | :------------------ | :----------------------------- |
| 140810250033 | Eshan               | Fullstack Developer            |
| 140810250042 | Radit               | Fullstack Developer            |
| 140810250084 | Farhan              | Fullstack Developer            |

---

## ⚙️ Fungsi Utama Website

1. **Tracker Makanan**: Melacak kalori dan konsumsi gizi resep makanan dari pengguna menggunakan bantuan AI untuk mempermudah pencatatan dan kalkulasi nutrisi.
2. **Tracker Olahraga & Polusi Udara**: Melacak kegiatan olahraga dari pengguna dan memberikan rekomendasi waktu & lokasi olahraga yang optimal dengan mempertimbangkan kondisi cuaca dan indeks kualitas udara (AQI) setempat.
3. **Tracker Overall Health**: Memberi skor kesehatan menyeluruh (*overall health score*) menggunakan data akumulasi dari Tracker Makanan & Olahraga, serta memberikan saran/insight berbasis AI untuk meningkatkan kesehatan pengguna.

---

## 🎯 Tujuan & Implementasi SDG

**Tujuan Proyek:**  
Proyek ini bertujuan menjalankan SDG poin 2, 3, 13 dengan cara menyediakan solusi berupa project ASVÍS. Kami berharap dengan adanya project ASVÍS ini dapat memberikan dampak yang nyata dan **terukur bagi peningkatan kualitas kesehatan, pemenuhan gizi seimbang, serta kesadaran lingkungan di masyarakat.**

**Target Sustainable Development Goals (SDG):**  
Website ini dirancang untuk mendukung pencapaian:
- **SDG 2: Mengakhiri Kelaparan (Zero Hunger)**  
  *Cara memenuhi:* Aplikasi ini membantu memantau asupan gizi yang seimbang dan memberikan informasi nutrisi resep makanan, mendukung perbaikan gizi masyarakat melalui pola makan yang lebih terkontrol.
- **SDG 3: Kehidupan Sehat dan Sejahtera (Good Health and Well-being)**  
  *Cara memenuhi:* Fitur pelacakan kesehatan menyeluruh (makanan dan olahraga) secara langsung mempromosikan gaya hidup sehat, pencegahan penyakit, dan peningkatan kebugaran fisik pengguna.
- **SDG 13: Penanganan Perubahan Iklim (Climate Action)**  
  *Cara memenuhi:* Dengan mengintegrasikan peringatan polusi udara, aplikasi ini meningkatkan kesadaran publik terhadap dampak kualitas udara lingkungan dan perubahan iklim terhadap aktivitas dan kesehatan pribadi sehari-hari.

---

## 🧑‍🤝‍🧑 Target Pengguna

- **Demografi:** Pria dan wanita usia 18-40 tahun, terutama yang tinggal di daerah urban/perkotaan di Indonesia di mana isu polusi udara dan gaya hidup *sedentary* (kurang gerak) cukup tinggi.
- **Karakteristik/Kebutuhan:** Individu yang peduli dengan kesehatan, pekerja kantoran, atau siapa saja yang sedang menjalani program kebugaran namun membutuhkan asisten digital yang praktis (AI) untuk melacak kalori sekaligus memantau keamanan udara untuk berolahraga *outdoor*.
- **Perangkat Utama:** Mayoritas mengakses melalui *smartphone* (Mobile-first design) karena pencatatan makanan dan pelacakan olahraga dilakukan secara dinamis kapan saja dan di mana saja.

---

## 🖼️ Mockup Kasar Sederhana

🎨 **Desain lengkap (Figma):** [ASVÍS — Mockup UI](https://www.figma.com/design/O07Gbq8DkDDQG1Uj1usYD6/Untitled?node-id=0-1&t=uW6SWvOeARYTkWFN-1)

Mockup dirancang dengan pendekatan *mobile-first* karena mayoritas pengguna mengakses ASVÍS melalui *smartphone*.

| Beranda | Tracker Makanan | Tracker Olahraga |
| :-----: | :-------------: | :--------------: |
| <img src="assets/mockup/beranda.png" alt="Mockup Halaman Beranda" width="260"> | <img src="assets/mockup/tracker-makanan.png" alt="Mockup Halaman Tracker Makanan" width="260"> | <img src="assets/mockup/tracker-olahraga.png" alt="Mockup Halaman Tracker Olahraga" width="260"> |

**1. Halaman Beranda**

Menampilkan ringkasan harian pengguna: **Skor Kesehatan Harian** (0–100) beserta perbandingan dengan hari sebelumnya, kartu **Kualitas Udara (AQI)** sesuai lokasi pengguna lengkap dengan saran waktu teraman untuk beraktivitas di luar ruangan, serta ringkasan **Kalori** harian dengan rincian makronutrien (protein, karbohidrat, lemak).

**2. Halaman Tracker Makanan**

Pengguna cukup menuliskan deskripsi makanan (atau mengunggah foto), lalu menekan **Analisis AI**. Sistem mengembalikan estimasi **total kalori** beserta rincian makronutrien dan komentar gizi (misalnya peringatan natrium tinggi). Hasil analisis dapat disimpan ke **Log Hari Ini** yang menampilkan riwayat makan beserta waktu dan kalorinya.

**3. Halaman Tracker Olahraga**

Menampilkan grafik **AQI per jam** untuk lokasi pengguna sehingga terlihat rentang waktu paling aman untuk berolahraga *outdoor*, daftar **lokasi rekomendasi** beserta nilai AQI-nya, dan ringkasan **sesi terakhir** (durasi, kalori terbakar, serta AQI saat sesi berlangsung). Tombol *Mulai Sesi Baru* digunakan untuk mencatat aktivitas olahraga.

---

## 🗄️ Skema Database

*Skema ERD ini telah disesuaikan dengan fitur ASVÍS, meliputi data pengguna, log makanan, log olahraga, dan pencatatan skor kesehatan.*

```mermaid
erDiagram
    %% Relasi antar entitas
    USERS ||--o{ FOOD_LOGS : "mencatat"
    USERS ||--o{ EXERCISE_LOGS : "melakukan"
    USERS ||--o{ HEALTH_SCORES : "memiliki"
    FOOD_LOGS ||--o{ FOOD_PHOTOS : "menyertakan"
    LOCATIONS ||--o{ EXERCISE_LOGS : "menjadi tempat"

    %% Detail Atribut & Tabel
    USERS {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        float weight_kg
        float height_cm
        string role
        string remember_token
        timestamp created_at
        timestamp updated_at
    }

    FOOD_LOGS {
        bigint id PK
        bigint user_id FK
        string food_name
        integer calories
        text nutrition_details
        string location_name
        decimal latitude
        decimal longitude
        timestamp consumed_at
        timestamp created_at
        timestamp updated_at
    }

    FOOD_PHOTOS {
        bigint id PK
        bigint food_log_id FK
        string photo_path
        timestamp created_at
        timestamp updated_at
    }

    EXERCISE_LOGS {
        bigint id PK
        bigint user_id FK
        bigint location_id FK
        string exercise_type
        integer duration_minutes
        integer calories_burned
        string air_quality_during_exercise
        timestamp exercised_at
        timestamp created_at
        timestamp updated_at
    }

    LOCATIONS {
        bigint id PK
        string name
        string address
        decimal latitude
        decimal longitude
        timestamp created_at
        timestamp updated_at
    }

    HEALTH_SCORES {
        bigint id PK
        bigint user_id FK
        date score_date 
        integer total_score 
        integer nutrition_score
        integer activity_score
        integer calories_in
        integer calories_burned
        text ai_insight
        timestamp created_at
        timestamp updated_at
    }