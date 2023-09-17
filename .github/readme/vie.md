# Mineflow

[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

[![Crowdin](https://badges.crowdin.net/mineflow/localized.svg)](https://crowdin.com/project/mineflow)
### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md), [Español](/.github/readme/spa.md)

---

### [Wiki](https://Mineflow.github.io/docs)

---

# Tiếng Việt

Bạn có thể kết hợp các hành động và tạo thứ gì đó giống như plugin mà không cần bất kỳ kiến thức viết mã nào.  
**Một số tác vụ được ẩn theo mặc định để ngăn lạm dụng. Để hiển thị tất cả, vui lòng chạy `mineflow permission add <tên của bạn> all` từ bảng điều khiển.**

## Lệnh

| lệnh                                            | mô tả                                |
| ----------------------------------------------- | ------------------------------------ |
| /mineflow language <eng &#124; jpn ind>         | Thay đổi ngôn ngữ                    |
| /mineflow recipe [add &#124; edit &#124; list]  | Quản lý các công thức chế tạo        |
| /mineflow command [add &#124; edit &#124; list] | Quản lý trình kích hoạt lệnh         |
| /mineflow form                                  | Quản lý trình kích hoạt biểu mẫu     |
| /mineflow permission <name> <level>             | Thay đổi cấp độ quyền của người chơi |
| /mineflow setting                               | Cài đặt                              |

## Quyền Hành Động

To change the permission, run `/mineflow permission <add|remove|list> <name> <permission>`. Only the player who has `permission` permission can change the permissions of the other players. You can give an all permission from the console.

## Variable

Characters enclosed by "{" and "}" are recognized as variables and will be replaced.  
examples: `{target}`, `{item}`

[more details](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial

### Create a recipe

Execute "/mineflow recipe add" and enter the recipe name and group name. (The group name can be left blank.)    
Add a variety of actions to the recipe.

### Execute a recipe

Add a trigger from "Edit trigger" of the form. Then, when the trigger occurs, the recipe will be executed.

### Change the executor

By default, the player who fired the trigger goes into the {target} variable of the recipe.  
It can be changed from "Change the target" on the form to any of the specified players, all players, random players, or none.

### Arguments and return values

You can set the value to be inherited from the original action, and the value to be returned when executing in the "Callback the other recipe" action.

## Examples

### CheckId command

Send the ID of the item in the player's hand to the chat field when execute `/id`. [Download](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### Steps

1. Execute `/mineflow command add` and add the /id command.  
   ![addCommand](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Execute `/mineflow recipe add` and add a recipe with a name of your choice.  
   ![addRecipe](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Click `Edit actions > Add action > Player` to add a `Send message to chat field` to the recipe you have created.
4. Enter `{target.hand.id}:{target.hand.damage}` in the message field of `Send message to chat field`.  
   ![addAction](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` contains information about the item in the player's hand.)
5. Click `Edit trigger > Add trigger > Command` and enter `id` in the `name of command` field. ![addTrigger](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### To send more information of item

{target.hand} is [item variable](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` is replaced by the item name and `{target.hand.count}` by the number of items.

##### To be able to use it non-OP

Set the permissions of the command to `anyone can execute` on the form to add the command or in the command menu.

## Bản quyền

Các biểu tượng được tạo bởi [Pause08](https://www.flaticon.com/authors/pause08) từ [www.flaticon.com](https://www.flaticon.com/)
