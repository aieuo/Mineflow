# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

### [Wiki](https://Mineflow.github.io/docs)

---

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md)

---

# English

You can combine actions and create something like a plugin without any coding knowledge.  
**Some of the actions are hidden by default to prevent abuse. To show them all, please run `mineflow permission add <your name> all` from the console.**


## Perintah
| command                                         | description              |
| ----------------------------------------------- | ------------------------ |
| /mineflow language <eng &#124; jpn ind>         | Ubah bahasa              |
| /mineflow recipe [add &#124; edit &#124; list]  | Kelola resep             |
| /mineflow command [add &#124; edit &#124; list] | Kelola pemicu perintah   |
| /mineflow form                                  | Kelola pemicu formulir   |
| /mineflow permission <name> <level>             | Ubah tingkat izin pemain |
| /mineflow setting                               | Pengaturan               |


## AksiIzin

To change the permission, run `/mineflow permission <name> <permission>`. Only the player who has `permission` permission can change the permissions of the other players. You can give an all permission from the console.


## Variabel
Characters enclosed by "{" and "}" are recognized as variables and will be replaced.  
examples: `{target}`, `{item}`

[more details](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial
### Buat resep
Execute "/mineflow recipe add" and enter the recipe name and group name. (The group name can be left blank.)  
Add a variety of actions to the recipe.

### Jalankan resep
Add a trigger from "Edit trigger" of the form. Then, when the trigger occurs, the recipe will be executed.

### Ubah pelaksananya
By default, the player who fired the trigger goes into the {target} variable of the recipe. It can be changed from "Change the target" on the form to any of the specified players, all players, random players, or none.

### Arguments and return values
You can set the value to be inherited from the original action, and the value to be returned when executing in the "Callback the other recipe" action.


## Contoh
### Perintah CheckId
Send the ID of the item in the player's hand to the chat field when execute `/id`. [Download](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### Steps
1. Jalankan perintah `/mineflow add` dan tambahkan perintah /id.  
   ![tambahkanPerintah](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Jalankan `/mineflow recipe add` dan tambahkan resep dengan nama pilihan Anda.  
   ![tambahkanResep](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Klik `Edit tindakan > Tambah tindakan > Pemain` untuk menambahkan `bidang Kirim pesan ke obrolan` ke resep yang telah Anda buat.
4. Masukkan `{target.hand.id}:{target.hand.damage}` di bidang pesan `bidang Kirim pesan ke obrolan`.  
   ![tambahkanTindakan](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` berisi informasi tentang item di tangan pemain.)
5. Klik `Edit pemicu > Tambahkan pemicu > Perintah` dan masukkan `id` di bidang `name of command`. ![tambahkanPemicu](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Untuk mengirim informasi lebih lanjut tentang barang
{target.hand} is [item variable](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` is replaced by the item name and `{target.hand.count}` by the number of items.

##### Untuk dapat menggunakannya non-OP
Set the permissions of the command to `anyone can execute` on the form to add the command or in the command menu.

## Copyright
Setel izin perintah ke `Siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.
