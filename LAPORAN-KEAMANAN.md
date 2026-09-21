# Laporan Keamanan Sistem Absensi (Bahasa Awam)

Dokumen ini menjelaskan hasil pemeriksaan keamanan aplikasi absensi DPUTR
dengan bahasa sehari-hari, tanpa istilah teknis. Versi rinci untuk teknisi
ada di berkas `SECURITY.md`.

Pemeriksaan **hanya dilakukan di salinan lokal** — server yang dipakai
sehari-hari tidak pernah disentuh.

---

## Ringkasan

Ditemukan **11 celah keamanan**. **Semuanya sudah ditutup.**

Sebelumnya, sistem punya beberapa "pintu" yang bisa disalahgunakan orang untuk
mencuri data pegawai, memalsukan absensi, atau mengubah data yang bukan haknya.
Semua pintu itu sekarang sudah dikunci.

---

## Apa saja yang ditemukan & diperbaiki

Diurutkan dari yang paling berbahaya.

| Bahaya | Masalahnya (bahasa awam) | Status |
|--------|--------------------------|--------|
| 🔴 Kritis | Ada satu alamat rahasia yang, kalau diketahui, bisa mengacak-acak seluruh database — tanpa perlu login. | ✅ Ditutup |
| 🔴 Kritis | Siapa pun yang sudah login bisa bertindak seperti admin (mengubah data orang lain), padahal jabatannya bukan admin. | ✅ Ditutup |
| 🔴 Kritis | Data absensi lengkap (termasuk **lokasi GPS pegawai**) bisa diunduh siapa saja tanpa login. | ✅ Ditutup |
| 🟠 Tinggi | Trik nama berkas bisa dipakai mengintip berkas lain di server. | ✅ Ditutup |
| 🟠 Tinggi | Kalau ada error, sistem malah membocorkan detail dalaman server ke layar. | ✅ Ditutup (setelan) |
| 🟠 Tinggi | Orang bisa **memalsukan absensi atas nama pegawai lain**. | ✅ Ditutup |
| 🟠 Tinggi | Saat daftar akun baru, orang bisa diam-diam mengangkat dirinya jadi admin. | ✅ Ditutup |
| 🟡 Sedang | Pengaman anti-robot saat login belum benar-benar dicek di server. | ✅ Ditutup |
| 🟡 Sedang | Halaman login membocorkan email mana yang terdaftar — memudahkan penebak password. | ✅ Ditutup |
| 🟡 Sedang | Saat menolak akses, sistem tetap menjawab "berhasil" sehingga membingungkan pemantauan. | ✅ Beres (lihat catatan) |
| 🟡 Sedang | Seorang **Pengawas bisa melihat & mengubah data divisi lain**, bukan cuma divisinya. | ✅ Ditutup |

---

## Dua perbaikan besar terakhir

**1. Pengawas dikunci ke divisinya sendiri.**
Dulu seorang Pengawas (user_admin) bisa mengintip dan mengubah data divisi mana
pun asal tahu nomornya — termasuk proyek, jadwal, kehadiran, dan lokasi GPS.
Sekarang Pengawas **hanya bisa menyentuh divisi yang memang ditugaskan padanya**.
Admin penuh tetap bisa semua (memang haknya), pegawai biasa tetap seperti biasa.

**2. Soal jawaban "berhasil" saat sebenarnya ditolak.**
Bagian yang berbahaya sudah dibereskan: kalau seseorang belum login atau mencoba
akses yang bukan haknya, sistem **jelas menolak**. Sisanya cuma pesan error biasa
(misalnya "kolom belum diisi") yang masih dibungkus gaya lama. Itu **bukan celah
keamanan**, dan kalau dipaksa diubah malah bisa merusak tampilan aplikasi. Jadi
sengaja dibiarkan — ini keputusan, bukan pekerjaan yang belum selesai.

---

## Yang perlu dilakukan pihak DPUTR (bukan pekerjaan pemrograman)

- [ ] Pastikan mode "debug" di server **dimatikan**.
- [ ] Ganti password/kunci yang mungkin sempat bocor selagi mode debug menyala.
- [ ] (Opsional) Aktifkan pengaman anti-robot penuh setelah akunnya siap.
- [ ] **Data absensi berisi identitas pegawai jangan pernah diunggah ke internet
      atau dibagikan ke publik.**

---

## Cara memastikan (untuk teknisi)

- Seluruh uji otomatis di sistem **lolos hijau**.
- Diuji langsung: pegawai ditolak (403), admin diterima, data divisi lain
  tidak bisa diintip Pengawas.
- Tampilan aplikasi diperiksa lewat tangkapan layar otomatis.

Rincian teknis lengkap: lihat `SECURITY.md`.
