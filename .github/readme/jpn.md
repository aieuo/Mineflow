# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

[Discordサーバー](https://discord.gg/RK27uaZEt7)

---

[![Crowdin](https://badges.crowdin.net/mineflow/localized.svg)](https://crowdin.com/project/mineflow)

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md), [Español](/.github/readme/spa.md)

---

### [Wiki](https://Mineflow.github.io/docs/jpn)

---

# 日本語

アクションを組み合わせてプラグインのようなものを作れます。\
\*\*一部のアクションは、悪用を防ぐためにデフォルトで非表示になっています。 アクションを組み合わせてプラグインのようなものを作れます。\
**いくつかのアクションはデフォルトの権限では使用できなくなっています． 全て表示するにはコンソールから`mineflow permission add <your name> all`を実行してください**

## コマンド

| コマンド                                                                                                                                        | 説明            |
| ------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| /mineflow language <eng &#124; jpn &#124; ind>                             | 言語を変更する       |
| /mineflow recipe [add &#124; edit &#124; list]  | レシピを管理する      |
| /mineflow command [add &#124; edit &#124; list] | コマンドトリガーを管理する |
| /mineflow form                                                                                                                              | フォームトリガーを管理する |
| /mineflow permission <name> <level>                                                                                                         | 権限レベルを変更する    |
| /mineflow setting                                                                                                                           | 設定            |

## アクション権限

`/mineflow permission <add|remove|list><name> <permission>` で権限を変更できます。 `permission`権限を持っているプレイヤーのみ他のプレイヤーの権限を変更することができます。 コンソールからは全ての権限を与えることができます。 コンソールからは全ての権限を与えることができます。

## 変数

`{` と `}`で囲った文字は変数と認識されて置き換えられます。\
例: `{target}`, `{item}`\
例: `{target}`, `{item}`

[詳しい説明](https://mineflow.github.io/docs/jpn/#/variable/about)

## チュートリアル

### レシピを作成する

<code>/mineflow recipe add</code>を実行してレシピ名とグループ名を入力します。 (グループ名は空白のままにできます。)\
レシピに様々なアクションを追加します。

### レシピを実行する

フォームの「トリガーを編集する」からトリガーを追加すると そのトリガーが起きた時レシピが実行されます。 そのトリガーが起きた時レシピが実行されます。

### 実行者を変更する

デフォルトではトリガーを発火したプレイヤーが{target}変数に入ります。\
デフォルトではトリガーを発火したプレイヤーが{target}変数に入ります。\
それはフォームの「ターゲット変更」から指定したプレイヤー,全てのプレイヤー,ランダムなプレイヤー,なしのどれかに変更できます。

### 引数と戻り値

アクションの「レシピを呼び出す」で呼び出されたときに元のレシピから受け入れる値と元のレシピに戻す値を設定できます。

## 例

### CheckIdコマンド

`/id`を実行すると、プレイヤーの手のアイテムのIDをチャットフィールドに送信します。
[ダウンロード](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### 手順

1. `/mineflow command add` を実行してidコマンドを追加します。\
   `/mineflow command add` を実行してidコマンドを追加します。\
   ![コマンド追加](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. `/mineflow recipe add` を実行して好きな名前のレシピを追加します。\
   `/mineflow recipe add` を実行して好きな名前のレシピを追加します。\
   ![レシピ追加](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. 作成したレシピのメニューから`アクションを編集する > アクションを追加する > プレイヤー`ボタンを押し、`チャット欄にメッセージを送る`を追加します。
4. `チャット欄にメッセージを送る`の送信するメッセージに`{target.hand.id}:{target.hand.damage}`と入力します。 [ダウンロード](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)\
   ![addAction](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` にはプレイヤーの手の中にあるアイテムに関する情報が含まれています。)
5. レシピのメニューから`トリガーを編集する > トリガーを追加する > コマンド`ボタンを押し、コマンドの名前にidと入力します。
   ![addTrigger](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### 表示する情報を増やすには

{target.hand}は[item変数](https://github.com/aieuo/Mineflow/wiki/Variable#item)です。 `{target.hand.name}`でアイテム名、`{target.hand.count}`でアイテム数と置き換えます。 `{target.hand.name}`でアイテム名、`{target.hand.count}`でアイテム数と置き換えます。

##### op以外も使用できるようにするには

コマンドを追加するときかコマンドメニューからコマンドの権限を`誰でも実行できる`にします。

## Copyright

Icons made by [Pause08](https://www.flaticon.com/authors/pause08) from [www.flaticon.com](https://www.flaticon.com/)
