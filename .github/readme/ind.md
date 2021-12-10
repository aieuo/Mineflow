# Mineflow
This language readme is made by keenanyafiqy


[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE)
[![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow)
[![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)  

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow)
[![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

### [Wiki](https://github.com/aieuo/Mineflow/wiki)

---

### [日本語](/.github/readme/jpn.md)

---

### [English](/README.md)

# Indonesia

Anda dapat menggabungkan tindakan dan membuat sesuatu seperti plugin tanpa pengetahuan pengkodean apa pun.

\* Beberapa tindakan disembunyikan secara default untuk mencegah penyalahgunaan. Untuk menampilkan semuanya, jalankan `mineflow permission <your name> 2` dari konsol.

## Perintah
| command | description |
| ---- | ---- |
| /mineflow language <eng &#124; jpn> | Ubah bahasa |
| /mineflow recipe [add &#124; edit &#124; list] | Kelola resep |  
| /mineflow command [add &#124; edit &#124; list] | Kelola pemicu perintah |  
| /mineflow form | Kelola pemicu formulir |  
| /mineflow permission <name> <level> | Ubah tingkat izin pemain |  
| /mineflow setting | Pengaturan |


## AksiIzin
|  level  |  jenis tindakan yang akan tersedia  |
| ---- | ---- |
|  0  |  -  | - |
|  1  |  perintah dari konsol, kelola izin, (tidak)izinkan terbang, loop  |
|  2  |  file konfigurasi  |  

Untuk mengubah izin, jalankan `/mineflow permission <name> <level>`. Level yang Anda berikan hanya dapat digunakan di bawah level Anda. Anda dapat memberikan level maksimum dari konsol.


## Variabel
Karakter yang diapit oleh "{" dan "}" dikenali sebagai variabel dan akan diganti.
contoh: `{target}`, `{item}`

[detail lebih lanjut](https://github.com/aieuo/Mineflow/wiki/Variable)        
        
## Tutorial
### Buat resep
Jalankan "/mineflow resep add" dan masukkan nama resep dan nama grup. (Nama grup boleh dikosongkan.)
Tambahkan berbagai tindakan ke resep.
### Jalankan resep
Tambahkan pemicu dari "Edit pemicu" dari bentuk. Kemudian, ketika pemicu terjadi, resep akan dieksekusi.
### Ubah pelaksananya
Secara default, pemain yang memicu pemicu masuk ke variabel {target} resep. Itu dapat diubah dari "Ubah target" pada formulir ke salah satu pemain yang ditentukan, semua pemain, secara acak 
### Argumen dan mengembalikan nilai
Anda dapat menyetel nilai yang akan diwarisi dari tindakan asli, dan nilai yang akan dikembalikan saat menjalankan tindakan "Panggil balik resep lainnya".

## Contoh
### Perintah CheckId
Kirim ID item di tangan pemain ke kolom chat saat menjalankan `/id`.
[Unduh](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)  

##### Langkah
1. Jalankan perintah `/mineflow add` dan tambahkan perintah /id.
![tambahkanPerintah](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Jalankan `/mineflow resep add` dan tambahkan resep dengan nama pilihan Anda.
![tambahkanResep](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Klik `Edit tindakan > Tambah tindakan > Pemain` untuk menambahkan `bidang Kirim pesan ke obrolan` ke resep yang telah Anda buat.
4. Masukkan `{target.hand.id}:{target.hand.damage}` di bidang pesan `bidang Kirim pesan ke obrolan`.  
![tambahkanTindakan](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true)
(`{target.hand}` berisi informasi tentang item di tangan pemain.)  
5. Klik `Edit pemicu > Tambahkan pemicu > Perintah` dan masukkan `id` di bidang `name of command`.
![tambahkanPemicu](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Untuk mengirim informasi lebih lanjut tentang barang
{target.hand} adalah [item variable](#item). `{target.hand.name}` diganti dengan nama item dan `{target.hand.count}` dengan jumlah item.

##### Untuk dapat menggunakannya non-OP
Setel izin perintah ke `siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.

## Hak Cipta
Ikon yang dibuat oleh [Pause08](https://www.flaticon.com/authors/pause08) dari [www.flaticon.com](https://www.flaticon.com/)
