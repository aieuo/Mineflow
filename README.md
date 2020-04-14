# Mineflow


[![GitHub license](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE)

[![PoggitCI Badge](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

https://github.com/aieuo/if_plugin

You can combine actions and create something like a plugin without any coding knowledge.


# Command
Change language: `/mineflow language <eng | jpn>`  
Manage recipes: `/mineflow recipe [add | edit | list]`  
Manage command triggers: `/mineflow command [add | edit | list]`  
Manage form triggers: `/mineflow form`  
Change player's permission level: `/mineflow permission <name> <level>`  
Setting: `/mineflow setting`  


# ActionPermission
|  level  |  description  |
| ---- | ---- |
|  0  |  Normal action.  |
|  1  |  Depending on how you use it, the server may be overloaded.  |
|  2  |  Depending on how you use it, the server machine may be overloaded.  |
To change the permission, run /mineflow permission <name> <level>. The level you give can only be used below your level. You can give a maximum level from the console.


# Variable
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
#### player
A variable containing player data.  
Available Keys (Let the name of the variable be "player".)  
This can use all the keys of the position and entity variable.
- {player.name} -> name of player (string)
- {player.hand} -> player's hand item (item)
#### block
A variable containing block data.  
Available Keys (Let the name of the variable be "block".)  
This can use all the keys of the position variable.
- {block.name} -> name of block
- {block.id} -> id of block
- {block.damage} -> damage value of block


List of default variables.
- What can be replaced unconditionally.
    - {server_name} -> name of the server (string)
    - {microtime} -> current microtime (number)
    - {time} -> current time (string)
    - {date} -> current date (string)
    - {default_level} -> default world name (string)
    - {onlines} ->  names of online players (array)
    - {ops} -> name of operators (array)
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
        
        
# Tutorial
## Create a recipe
Execute "/mineflow recipe add" and enter the recipe name and group name. (The group name can be left blank.)  
Add a variety of actions to the recipe.
## Execute a recipe
Add a trigger from "Edit trigger" of the form. Then, when the trigger occurs, the recipe will be executed.
## Change the executor
By default, the player who fired the trigger goes into the {target} variable of the recipe. It can be changed from "Change the target" on the form to any of the specified players, all players, random players, or none.
## arguments and return values
You can set the value to be inherited from the original action and the value to be returned when executing in the "Callback the other recipe" action.
