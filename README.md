# Mineflow


[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE)
[![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow)
[![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)  

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow)
[![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

Icons made by [Pause08](https://www.flaticon.com/authors/pause08) from [www.flaticon.com](https://www.flaticon.com/)

---

### [Wiki](https://github.com/aieuo/Mineflow/wiki)

---

### [English](#English),  [日本語](#日本語)

# English

You can combine actions and create something like a plugin without any coding knowledge.


## Command
Change language: `/mineflow language <eng | jpn>`  
Manage recipes: `/mineflow recipe [add | edit | list]`  
Manage command triggers: `/mineflow command [add | edit | list]`  
Manage form triggers: `/mineflow form`  
Change player's permission level: `/mineflow permission <name> <level>`  
Setting: `/mineflow setting`  


## ActionPermission
|  level  |  description  |
| ---- | ---- |
|  0  |  Normal action.  |
|  1  |  Depending on how you use it, the server may be overloaded.  |
|  2  |  Depending on how you use it, the server machine may be overloaded.  |  

To change the permission, run `/mineflow permission <name> <level>`. The level you give can only be used below your level. You can give a maximum level from the console.


## Variable
Characters enclosed by "{" and "}" are recognized as variables and will be replaced.  
In the case of List and Map variables, you can specify the index by separating the variable names with a period like {aiueo.oo}.  
more examples:  {list.0}, {target.name}, {target.item.id}
 
### Variable types
#### string
#### number
#### list
#### map
#### item
A variable containing item data.  
Available Keys (Let the name of the variable be "item".)  
- {item.name} -> name of item (string)
- {item.id} -> id of item (number)
- {item.damage} -> damage value of item (number)
- {item.count} -> number of items (number)
- {item.lore} -> lore of item (list)
#### level
A variable containing world data.  
Available Keys (Let the name of the variable be "level".)  
- {level.name} -> name of world
- {level.folderName} -> folder name of world
- {level.id} -> world id
#### position
A variable containing position data.  
Available Keys (Let the name of the variable be "pos".)  
- {pos.x} -> x-coordinate value of the position (number)
- {pos.y} -> y-coordinate value of the position (number)
- {pos.z} -> z-coordinate value of the position (number)
- {pos.xyz} -> x, y and z coordinate value of the position (string)
- {pos.level} -> world name of the position (level)
- {pos.position} -> coordinates (position)
#### entity
A variable containing entity data.  
Available Keys (Let the name of the variable be "entity".)  
This can use all the keys of the position variable.
- {entity.id} -> entity id (number) 
- {entity.nameTag} -> The name floating above the entity (string)
- {entity.health} -> entity's current health (number)
- {entity.maxHealth} -> entity's max health (number)
- {entity.yaw} -> entity's yaw (number)
- {entity.pitch} -> entity's pitch (number)
- {entity.direction} -> entity's direction (number, 0=South, 1=West, 2=North, 3=East)
#### human
A variable containing human data.  
Available Keys (Let the name of the variable be "human".)  
This can use all the keys of the position variable and entity variable.
- {human.hand} -> player's hand item (item)
- {human.food} -> player's food level (number)
#### player
A variable containing player data.  
Available Keys (Let the name of the variable be "player".)  
This can use all the keys of the position variable, entity variable and human variable.
- {player.name} -> name of player (string)
#### block
A variable containing block data.  
Available Keys (Let the name of the variable be "block".)  
This can use all the keys of the position variable.
- {block.name} -> name of block (string)
- {block.id} -> id of block (number)
- {block.damage} -> damage value of block (number)
#### event
A variable containing event data.
Available Keys (Let the name of the variable be "event".)  
- {event.name} -> name of event (string)


List of default variables.
- What can be replaced unconditionally.
    - {server_name} -> name of the server (string)
    - {microtime} -> current microtime (number)
    - {time} -> current time (string)
    - {date} -> current date (string)
    - {default_level} -> default world name (string)
    - {onlines} ->  names of online players (array)
    - {ops} -> name of operators (array)
    - {event} -> event when the recipe executes (event)
- Variables that can be used when the player executes
    - {target} -> player who executed (player)
- Variables that can be used when the entity executes
    - {target} -> executed entity (entity)
- Replaced when command trigger
    - {cmd} -> first part of command separated by whitespace (string)
    - {args} -> The second and later of the commands separated by spaces (array) (When executing the command "/warp aieuo", {args[0]} will contain aieuo)
- Replaced when block event
    - {block} -> a block that have been touched, broken or placed (block)
- Replaced when SignChangeEvent
    - {sign_lines} -> The words on the sign (array)
- Replaced when CommandEvent or ChatEvent.
    - {message} -> command or chat (string)
- Replaced when ToggleSneak, ToggleSprint, ToggleFlight Event.
    - {state} -> Current State. true or false (string)
- Replaced when EntityDamageEvent.
    - {damage} -> The amount of damage. (number)
    - {cause} -> The cause of the damage. (number)
    - {damager} -> The entity that attacked. (entity or player)
- Replaced when EntityAttackEvent.
    - {damage} -> The amount of damage. (number)
    - {cause} -> The cause of the damage. (number)
    - {damaged} -> Damaged entity. (entity or player)
- When the player crafts
    - {inputs} -> input items. (array, item[])
    - {outputs} -> output items. (array, item[])
- When a player switches to another world
    - {origin_level} -> The name of the original world (level)
    - {target_level} -> The name of the target world (level)
        
        
## Tutorial
### Create a recipe
Execute "/mineflow recipe add" and enter the recipe name and group name. (The group name can be left blank.)  
Add a variety of actions to the recipe.
### Execute a recipe
Add a trigger from "Edit trigger" of the form. Then, when the trigger occurs, the recipe will be executed.
### Change the executor
By default, the player who fired the trigger goes into the {target} variable of the recipe. It can be changed from "Change the target" on the form to any of the specified players, all players, random players, or none.
### arguments and return values
You can set the value to be inherited from the original action, and the value to be returned when executing in the "Callback the other recipe" action.

## Examples
### CheckId command
Send the ID of the item in the player's hand to the chat field when execute `/id`.
[Download](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)  

##### steps
1. Execute `/mineflow command add` and add the /id command.  
![addCommand](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Execute `/mineflow recipe add` and add a recipe with a name of your choice.  
![addRecipe](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Click `Edit actions > Add action > Player` to add a `Send message to chat field` to the recipe you have created.
4. Enter `{target.hand.id}:{target.hand.damage}` in the message field of `Send message to chat field`.  
![addAction](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true)
(`{target.hand}` contains information about the item in the player's hand.)  
5. Click `Edit trigger > Add trigger > Command` and enter `id` in the `name of command` field.
![addTrigger](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### To send more information of item
{target.hand} is [item variable](#item). `{target.hand.name}` is replaced by the item name and `{target.hand.count}` by the number of items.   

##### To be able to use it non-OP
Set the permissions of the command to `anyone can execute` on the form to add the command or in the command menu.  





# 日本語

アクションを組み合わせてプラグインのようなものを作れます。


## コマンド
言語を変更する: `/mineflow language <eng | jpn>`  
レシピを管理する: `/mineflow recipe [add | edit | list]`  
コマンドトリガーを管理する: `/mineflow command [add | edit | list]`  
フォームトリガーを管理する: `/mineflow form`  
権限レベルを変更する: `/mineflow permission <name> <level>`  
設定: `/mineflow setting`  


## アクション権限
|  レベル  |  説明  |
| ---- | ---- |
|  0  |  普通のアクション  |
|  1  |  使い方によってはサーバーに影響を与える可能性があります  |
|  2  |  使い方によってはサーバー機に影響を与える可能性があります  |  

`/mineflow permission <name> <level>` で権限を変更できます。  
与えることができる権限レベルは自分のレベル以下だけです。  
コンソールからは最大レベルを与えることができます。


## 変数
`{` と `}`で囲った文字は変数と認識されて置き換えられます。  
List, Map 変数の場合{aiueo.oo}のように変数名の後に`.`で区切ってインデックスを指定することができます。  
もっと例:  {list.0}, {target.name}, {target.item.id}
 
### 変数のタイプ
#### string
#### number
#### list
#### map
#### item
アイテムの情報を持っている変数  
使用できるキー (変数名を「item」とします)  
- {item.name} -> アイテムの名前 (string)
- {item.id} -> アイテムのID (number)
- {item.damage} ->　アイテムのダメージ値 (number)
- {item.count} -> アイテムの個数 (number)
- {item.lore} -> アイテムの説明 (list)
#### level
ワールドの情報を持っている変数  
使用できるキー (変数名を「level」とします)  
- {level.name} -> ワールド名
- {level.folderName} -> ワールドのフォルダー名
- {level.id} -> ワールドのID
#### position
座標の情報を持っている変数  
使用できるキー (変数名を「pos」とします)  
- {pos.x} -> x座標の値 (number)
- {pos.y} -> y座標の値 (number)
- {pos.z} -> z座標の値 (number)
- {pos.xyz} -> x座標とy座標とz座標の値 (string)
- {pos.level} -> 座標のワールド (level)
- {pos.position} -> 座標 (position)
#### entity
エンティティの情報を持っている変数  
使用できるキー (変数名を「entity」とします)  
これはposition変数の全てのキーを使用することができます
- {entity.id} -> エンティティのID (number) 
- {entity.nameTag} -> エンティティの頭の上に浮いている名前 (string)
- {entity.health} -> エンティティの体力 (number)
- {entity.maxHealth} -> エンティティの最大体力 (number)
- {entity.yaw} -> エンティティの体の向き (number)
- {entity.pitch} -> エンティティの頭の向き (number)
- {entity.direction} -> エンティティの向いている方角 (number, 0=南, 1=西, 2=北, 3=東)
#### human
人間の情報を持っている変数  
使用できるキー (変数名を「human」とします)  
これはposition変数とentity変数の全てのキーを使用することができます
- {human.hand} -> プレイヤーの手にあるアイテム (item)
- {human.food} -> プレイヤーの空腹度 (number)
#### player
プレイヤーの情報を持っている変数  
使用できるキー (変数名を「player」とします)  
これはposition変数とentity変数とhuman変数の全てのキーを使用することができます
- {player.name} -> プレイヤーの名前 (string)
#### block
ブロックの情報を持っている変数  
使用できるキー (変数名を「block」とします)  
これはposition変数の全てのキーを使用することができます
- {block.name} -> ブロックの名前 (string)
- {block.id} -> ブロックのID (number)
- {block.damage} -> ブロックのダメージ値 (number)
#### event
イベントの情報を持っている変数  
使用できるキー (変数名を「event」とします)  
- {event.name} -> イベントの名前 (string)


デフォルト変数の一覧
- いつでも使える
    - {server_name} -> サーバーの名前 (string)
    - {microtime} -> 現在のマイクロ秒 (number)
    - {time} -> 現在の時刻 (string)
    - {date} -> 現在の日付 (string)
    - {default_level} -> デフォルトのワールド名 (string)
    - {onlines} -> オンラインのプレイヤー名 (array)
    - {ops} -> opの名前 (array)
    - {event} -> レシピを実行したときのイベント (event)
- プレイヤーが実行したときに使用できる変数
    - {target} -> 実行したプレイヤー (player)
- エンティティが実行したときに使用できる変数
    - {target} -> 実行したエンティティ (entity)
- コマンドトリガーとコマンドイベントで使用できる変数
    - {cmd} -> コマンドを空白で区切った最初の部分 (string)
    - {args} -> マンドを空白で区切った2つ目以降 (array) (`/warp aieuo` を実行した時、 `{args[0]}` にはaieuoが入ります)
- ブロックトリガーとブロックイベントで使用できる変数
    - {block} -> 触った、壊した、置いたブロック (block)
- 看板が変わったときのイベントで使用できる変数
    - {sign_lines} -> 看板の文字 (array)
- コマンドイベントとチャットイベントで使用できる変数
    - {message} -> コマンドかチャット (string)
- スニーク、飛行、ダッシュを切り替えた時
    - {state} -> 現在の状態 trueかfalse (string)
- エンティティがダメージを受けたときに使用できる変数
    - {damage} -> ダメージ量 (number)
    - {cause} -> ダメージを受けた原因 (number)
    - {damager} -> 攻撃したエンティティ (entity or player)
- プレイヤーが攻撃したときに使用できる変数
    - {damage} -> ダメージ量 (number)
    - {cause} -> ダメージを受けた原因 (number)
    - {damaged} -> ダメージを受けたエンティティ (entity or player)
- プレイヤーがクラフトしたとき
    - {inputs} -> 入れたアイテムたち (array, item[])
    - {outputs} -> 出てきたアイテムたち (array, item[])
- プレイヤーがワールドを移動したときに使用できる変数
    - {origin_level} -> 移動元のワールド (level)
    - {target_level} -> 移動先のワールド (level)
        
        
## チュートリアル
### レシピを作成する
`/mineflow recipe add`を実行してレシピ名とグループ名を入力します。  
レシピにいろいろなアクションを追加します
### レシピを実行する
フォームの「トリガーを編集する」からトリガーを追加するとそのトリガーが起きた時レシピが実行されます。
### 実行者を変更する
デフォルトではトリガーを発火したプレイヤーが{target}変数に入ります。それはフォームの「ターゲット変更」から指定したプレイヤー,全てのプレイヤー,ランダムなプレイヤー,なしのどれかに変更できます。
### 引数と戻り値
アクションの「レシピを呼び出す」で呼び出されたときに元のレシピから受け入れる値と元のレシピに戻す値を設定できます。

## 例
### CheckIdコマンド
/id を実行したときに手に持っているアイテムのIDを表示する
[ダウンロード](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)  

##### 手順
1. `/mineflow command add` を実行してidコマンドを追加します。  
![コマンド追加](https://github.com/aieuo/images/blob/master/mineflow/jpn/CheckId_1.png?raw=true)
2. `/mineflow recipe add` を実行して好きな名前のレシピを追加します。  
![レシピ追加](https://github.com/aieuo/images/blob/master/mineflow/jpn/CheckId_2.png?raw=true)
3. 作成したレシピのメニューから`アクションを編集する > アクションを追加する > プレイヤー`ボタンを押し、`チャット欄にメッセージを送る`を追加します。
4. `チャット欄にメッセージを送る`の送信するメッセージに`{target.hand.id}:{target.hand.damage}`と入力します。  
![アクション追加](https://github.com/aieuo/images/blob/master/mineflow/jpn/CheckId_3.png?raw=true)
(`{target.hand}`は変数にはプレイヤーの手に持っているアイテムの情報が入っています。)  
5. レシピのメニューから`トリガーを編集する > トリガーを追加する > コマンド`ボタンを押し、コマンドの名前に`id`と入力します。  
![トリガー追加](https://github.com/aieuo/images/blob/master/mineflow/jpn/CheckId_4.png?raw=true)
6. 完成!

##### 表示する情報を増やすには
{target.hand}は[item変数](#item-1)です。`{target.hand.name}`でアイテム名、`{target.hand.count}`でアイテム数と置き換えます。  

##### op以外も使用できるようにするには
コマンドを追加するときかコマンドメニューからコマンドの権限を`誰でも実行できる`にします。  


