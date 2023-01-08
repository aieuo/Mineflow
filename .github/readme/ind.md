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

Anda dapat menggabungkan tindakan dan membuat sesuatu seperti plugin tanpa pengetahuan pengkodean apa pun.  
**Beberapa tindakan disembunyikan secara default untuk mencegah penyalahgunaan. Untuk menampilkan semuanya, jalankan `mineflow permission add <your name> all` dari konsol.**


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

Untuk mengubah izin, jalankan `/mineflow permission <name> <permission>`. Hanya pemain yang memiliki `izin` izin dapat mengubah izin dari pemain lain. You can give an all permission from the console.


## Variabel
Karakter yang diapit oleh "{" dan "}" dikenali sebagai variabel dan akan diganti.  
contoh: `{target}`, `{item}`

[Detail lebih lanjut](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial
### Buat resep
Jalankan "/mineflow recipe add" dan masukkan nama resep dan nama grup.  (Nama grup boleh dikosongkan.)  
Tambahkan berbagai tindakan ke resep.

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
