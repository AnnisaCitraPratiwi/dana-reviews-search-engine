# Pengembangan Sistem Pencarian Ulasan Aplikasi Dana di Google Play Store  
**Berbasis Kata Kunci Menggunakan TF-IDF dan Cosine Similarity**

---

## Deskripsi Proyek

Proyek ini bertujuan mengembangkan sebuah sistem pencarian ulasan aplikasi Dana yang tersedia di Google Play Store dengan metode berbasis teks. Sistem menggunakan algoritma **TF-IDF (Term Frequency-Inverse Document Frequency)** untuk mengekstrak kata kunci penting dalam setiap ulasan dan **Cosine Similarity** untuk mengukur tingkat kemiripan antara kata kunci pencarian pengguna dengan ulasan yang ada.

Dengan adanya sistem ini, pengguna dapat dengan cepat menemukan ulasan yang paling relevan dan sesuai dengan kebutuhan atau masalah yang ingin mereka ketahui, sehingga mempercepat proses pengambilan keputusan terkait aplikasi Dana.

---

## Fitur Utama

- **Pencarian berbasis kata kunci** yang akurat dan efisien  
- **Pengolahan teks** menggunakan TF-IDF untuk menilai relevansi tiap ulasan  
- **Perhitungan kemiripan** menggunakan Cosine Similarity untuk hasil pencarian yang tepat  
- Antarmuka sederhana yang mudah digunakan oleh semua kalangan

---

## Anggota Tim

| Nama                      | NPM         
|---------------------------|-------------
| Annisa Citra Pratiwi      | 2217051008  
| Andria Laras Ramadhania   | 2217051016  
| Nazwa Sophia Nadine Effendi | 2217051049 
| Fathiyya Jasmine          | 2217051026  

---

## Teknologi yang Digunakan

- **Python** untuk pengolahan data dan backend  
- **Flask** sebagai web framework backend  
- **TF-IDF & Cosine Similarity** sebagai metode utama pencarian teks  
- **HTML, CSS, JavaScript** untuk frontend  
- **Git & GitHub** untuk version control dan kolaborasi

---

## Cara Menjalankan Proyek

1. **Clone repository**  
   ```bash
   git clone https://github.com/AnnisaCitraPratiwi/dana-reviews-search-engine.git
   cd dana-reviews-search-engine
````

2. **Instalasi dependencies backend**
   Pastikan kamu sudah punya Python dan pip.

   ```bash
   pip install -r backend/requirements.txt
   ```

3. **Jalankan server backend**

   ```bash
   python backend/app.py
   ```

4. **Buka aplikasi di browser**
   Kunjungi `http://localhost:5000` untuk mengakses antarmuka pencarian ulasan.

---
