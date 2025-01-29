# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

[Server Discord](https://discord.gg/RK27uaZEt7)

---

[![Crowdin](https://badges.crowdin.net/mineflow/localized.svg)](https://crowdin.com/project/mineflow)

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md), [Español](/.github/readme/spa.md)

---

### [Wiki](https://Mineflow.github.io/docs)

---

# Indonesia

You can combine actions and create something like a plugin without any coding knowledge.\
**Some actions are hidden by default to prevent abuse. To show them all, please run `mineflow permission add <your name> all` from the console.**

## Perintah

| perintah                                                                                                                                    | deskripsi                |
| ------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------ |
| /mineflow language<eng &#124; jpn ind>                                                         | Ubah bahasa              |
| /mineflow recipe [add &#124; edit &#124; list]  | Kelola resep             |
| /mineflow command [add &#124; edit &#124; list] | Kelola pemicu perintah   |
| /mineflow form                                                                                                                              | Kelola pemicu formulir   |
| /mineflow permission <name> <level>                                                                                                         | Ubah tingkat izin pemain |
| /mineflow setting                                                                                                                           | Pengaturan               |

## Izin Tindakan

To change the permission, run `/mineflow permission <add|remove|list> <name> <permission>`. Only the player who has `permission` permission can change the permissions of the other players. Kamu dapat memberikan semua izin dari konsol.

## Variabel

Characters enclosed by "{" and "}" are recognized as variables and will be replaced.\
examples: `{target}`, `{item}`

[Detail lebih lanjut](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial

### Buat resep

Jalankan "/mineflow recipe add" dan masukkan nama resep dan nama grup.  (The group name can be left blank.)\
Add a variety of actions to the recipe.

### Jalankan resep

Tambahkan pemicu dari "Edit pemicu" dari bentuk. Kemudian, ketika pemicu terjadi, resep akan dieksekusi.

### Ubah pelaksananya

By default, the player who fired the trigger goes into the {target} variable of the recipe.\
It can be changed from "Change the target" on the form to any of the specified players, all players, random players, or none.

### Argumen dan mengembalikan nilai

Anda dapat menyetel nilai yang akan diwarisi dari tindakan asli, dan nilai yang akan dikembalikan saat menjalankan tindakan "Panggil balik resep lainnya".

## Contoh

### Perintah CheckId

Send the ID of the item in the player's hand to the chat field when execute `/id`.
[Download](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### Langkah

1. Execute `/mineflow command add` and add the /id command.\
   ![addCommand](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Execute `/mineflow recipe add` and add a recipe with a name of your choice.\
   ![addRecipe](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Klik `Edit tindakan > Tambah tindakan > Pemain` untuk menambahkan `Bidang Kirim pesan ke obrolan` ke resep yang telah Anda buat.
4. Enter `{target.hand.id}:{target.hand.damage}` in the message field of `Send message to chat field`.\
   ![addAction](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` contains information about the item in the player's hand.)
5. Click `Edit trigger > Add trigger > Command` and enter `id` in the `name of command` field.
   ![addTrigger](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Untuk mengirim informasi lebih lanjut tentang barang

{target.hand} is [item variable](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` is replaced by the item name and `{target.hand.count}` by the number of items.

##### Untuk dapat menggunakannya non-OP

Setel izin perintah ke `Siapa pun dapat mengeksekusi` pada formulir untuk menambahkan perintah atau di menu perintah.

## Hak Cipta

Ikon dibuat oleh [Pause08](https://www.flaticon.com/authors/pause08) dari [www.flaticon.com](https://www.flaticon.com/)
