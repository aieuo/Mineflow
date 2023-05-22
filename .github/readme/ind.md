# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

### [Wiki](https://Mineflow.github.io/docs)

---

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md), [Español](/.github/readme/spa.md)

---

# Indonesia

You can combine actions and create something like a plugin without any coding knowledge.  
**Some actions are hidden by default to prevent abuse. To show them all, please run `mineflow permission <your name> 2` from the console.**

## Perintah

| perintah                                        | deskripsi                |
| ----------------------------------------------- | ------------------------ |
| /mineflow language<eng &#124; jpn ind>          | Ubah bahasa              |
| /mineflow recipe [add &#124; edit &#124; list]  | Kelola resep             |
| /mineflow command [add &#124; edit &#124; list] | Kelola pemicu perintah   |
| /mineflow form                                  | Kelola pemicu formulir   |
| /mineflow permission <name> <level>             | Ubah tingkat izin pemain |
| /mineflow setting                               | Pengaturan               |

## Izin Tindakan

| tingkat | jenis tindakan yang akan tersedia                               |
| ------- | --------------------------------------------------------------- |
| 0       | -                                                               |
| 1       | perintah dari konsol, kelola izin, (tidak)izinkan terbang, loop |
| 2       | file konfigurasi                                                |

Untuk mengubah izin, jalankan `/mineflow permission <name> <level>`. Tingkat yang Anda berikan hanya dapat digunakan di bawah tingkat Anda. Anda dapat memberikan level maksimum dari konsol.

## Variabel

Karakter yang diapit oleh "{" dan "}" dikenali sebagai variabel dan akan diganti.  
contoh: `{target}`, `{item}`

[Detail lebih lanjut](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial

### Buat resep

Jalankan "/mineflow recipe add" dan masukkan nama resep dan nama grup.  (The group name can be left blank.)    
Add a variety of actions to the recipe.

### Jalankan resep

Tambahkan pemicu dari "Edit pemicu" dari bentuk. Kemudian, ketika pemicu terjadi, resep akan dieksekusi.

### Ubah pelaksananya

Secara default, pemain yang memicu pemicu masuk ke variabel {target} resep. Itu dapat diubah dari "Ubah target" pada formulir ke salah satu pemain yang ditentukan, semua pemain, secara acak

### Argumen dan mengembalikan nilai

Anda dapat menyetel nilai yang akan diwarisi dari tindakan asli, dan nilai yang akan dikembalikan saat menjalankan tindakan "Panggil balik resep lainnya".

## Contoh

### Perintah CheckId

Kirim ID item di tangan pemain ke kolom chat saat menjalankan `/id`. [Unduh](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### Langkah

1. Jalankan perintah `/mineflow command add` dan tambahkan perintah /id.  
   ![addCommand](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Jalankan `/mineflow recipe add` dan tambahkan resep dengan nama pilihan Anda.  
   ![addRecipe](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Klik `Edit tindakan > Tambah tindakan > Pemain` untuk menambahkan `Bidang Kirim pesan ke obrolan` ke resep yang telah Anda buat.
4. Masukkan `{target.hand.id}:{target.hand.damage}` di bidang pesan `Bidang Kirim pesan ke obrolan`.  
   ![tambahkanTindakan](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` berisi informasi tentang item di tangan pemain.)
5. Klik `Edit pemicu > Tambahkan pemicu > Perintah` dan masukkan `id` di bidang `nama perintah`. ![addTrigger](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Untuk mengirim informasi lebih lanjut tentang barang

{target.hand} adalah [variabel barang](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` diganti dengan nama barang dan `{target.hand.count}` dengan jumlah barang.

##### Untuk dapat menggunakannya non-OP

Setel izin perintah ke `Siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.

## Hak Cipta

Ikon dibuat oleh [Pause08](https://www.flaticon.com/authors/pause08) dari [www.flaticon.com](https://www.flaticon.com/)
