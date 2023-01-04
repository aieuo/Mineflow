# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

### [Wiki](https://Mineflow.github.io/docs)

---

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md)

---

# Indonesia

Anda dapat menggabungkan tindakan dan membuat sesuatu seperti plugin tanpa pengetahuan pengkodean apa pun.  
**Beberapa tindakan disembunyikan secara default untuk mencegah penyalahgunaan. To show them all, please run `mineflow permission add <your name> all` from the console.**


## Perintah
| command                                         | description              |
| ----------------------------------------------- | ------------------------ |
| /mineflow language<eng &#124; jpn ind>          | Ubah bahasa              |
| /mineflow recipe [add &#124; edit &#124; list]  | Kelola resep             |
| /mineflow command [add &#124; edit &#124; list] | Kelola pemicu perintah   |
| /mineflow form                                  | Kelola pemicu formulir   |
| /mineflow permission <name> <level>             | Ubah tingkat izin pemain |
| /mineflow setting                               | Pengaturan               |


## AksiIzin

To change the permission, run `/mineflow permission <name> <permission>`. Only the player who has `permission` permission can change the permissions of the other players. You can give an all permission from the console.


## Variabel
Karakter yang diapit oleh "{" dan "}" dikenali sebagai variabel dan akan diganti.  
contoh: `{target}`, `{item}`

[Detail lebih lanjut](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial
### Buat resep
Jalankan "/mineflow resep add" dan masukkan nama resep dan nama grup.  (Nama grup boleh dikosongkan.)  
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
1. Jalankan perintah `/mineflow add` dan tambahkan perintah /id.  
   ![tambahkanPerintah](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Jalankan `/mineflow recipe add` dan tambahkan resep dengan nama pilihan Anda.  
   ![tambahkanResep](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Klik `Edit tindakan > Tambah tindakan > Pemain` untuk menambahkan `bidang Kirim pesan ke obrolan` ke resep yang telah Anda buat.
4. Masukkan `{target.hand.id}:{target.hand.damage}` di bidang pesan `bidang Kirim pesan ke obrolan`.  
   ![tambahkanTindakan](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` berisi informasi tentang item di tangan pemain.)
5. Klik `Edit pemicu > Tambahkan pemicu > Perintah` dan masukkan `id` di bidang `name of command`. ![tambahkanPemicu](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Untuk mengirim informasi lebih lanjut tentang barang
{target.hand} adalah [item variable](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` diganti dengan nama item dan `{target.hand.count}` dengan jumlah item.

##### Untuk dapat menggunakannya non-OP
Setel izin perintah ke `Siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.

## Hak Cipta
Setel izin perintah ke `Siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.
