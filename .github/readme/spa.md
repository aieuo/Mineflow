# Mineflow

[![Licencia GitHub](https://img.shields.io/badge/license-UIUC/NCSA-blue.svg)](https://github.com/aieuo/Mineflow/blob/master/LICENSE) [![](https://poggit.pmmp.io/shield.state/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.api/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![](https://poggit.pmmp.io/shield.dl/Mineflow)](https://poggit.pmmp.io/p/Mineflow) [![](https://poggit.pmmp.io/shield.dl.total/Mineflow)](https://poggit.pmmp.io/p/Mineflow)

[![Insignia PoggitCI](https://poggit.pmmp.io/ci.badge/aieuo/Mineflow/Mineflow)](https://poggit.pmmp.io/ci/aieuo/Mineflow/Mineflow)

---

### [Wiki](https://Mineflow.github.io/docs)

---

### [English](/README.md), [日本語](/.github/readme/jpn.md), [Indonesia](/.github/readme/ind.md), [Español](/.github/readme/spa.md)

---

# Español

Puedes combinar acciones y crear algo como un plugin sin ningún conocimiento de programación.  
**Algunas de las acciones se ocultan por defecto para evitar abusos. Para mostrarlas todas, por favor ejecuta `mineflow permission add <your name> all` desde la consola.**

## Comando

| comando                                         | descripción                           |
| ----------------------------------------------- | ------------------------------------- |
| /mineflow language <eng &#124; jpn ind>         | Cambiar idioma                        |
| /mineflow recipe [add &#124; edit &#124; list]  | Administrar recetas                   |
| /mineflow command [add &#124; edit &#124; list] | Administrar accionadores de comandos  |
| /mineflow form                                  | Administrar accionadores de interfaz  |
| /mineflow permission <name> <level>             | Cambiar nivel de permisos del jugador |
| /mineflow setting                               | Ajustes                               |

## Permiso de acción

Para cambiar el permiso, ejecute `/mineflow permission <name> <level>`. El nivel que usted da sólo puede ser utilizado por debajo de su nivel. Puedes dar un nivel máximo desde la consola.

## Variable

Los caracteres encerrados por "{" y "}" son reconocidos como variables y serán reemplazados.  
ejemplos: `{target}`, `{item}`

[Más detalles](https://mineflow.github.io/docs/eng/#/variable/about)

## Tutorial

### Crear una receta

Ejecuta "/mineflow recipe add" e introduce el nombre de la receta y el nombre del grupo. (El nombre del grupo puede dejarse en blanco.)  
Añade una variedad de acciones a la receta.

### Ejecutar una receta

Añadir un accionador de "Editar accionador" del formulario. Luego, cuando el accionador ocurre, la receta será ejecutada.

### Cambiar el ejecutor

De forma predeterminada, el jugador que disparó el gatillo entra en la variable {target} de la receta. Se puede cambiar de "Cambiar el objetivo" en el formulario a cualquiera de los jugadores especificados, todos los jugadores, jugadores aleatorios o ninguno.

### Argumentos y devolver valores

Puede establecer el valor a heredar de la acción original, y el valor a devolver cuando se ejecuta en la acción "Callback the other recet".

## Ejemplos

### Comando CheckId

Envía el ID del elemento en la mano del jugador al campo de chat cuando ejecute `/id`. [Descargar](https://github.com/aieuo/MineflowExamples/blob/master/checkId.json)

##### Pasos

1. Ejecuta `/mineflow comando add` y añade el comando /id.  
   ![AñadirComando](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_1.png?raw=true)
2. Ejecuta `/mineflow recipe add` y añade una receta con un nombre de tu elección.  
   ![AñadirReceta](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_2.png?raw=true)
3. Haga clic en `Editar acciones > Añadir acción > Jugador` para añadir un `Enviar mensaje al campo de chat` a la receta que ha creado.
4. Introduzca `{target.hand.id}:{target.hand.damage}` en el campo de mensaje de `Enviar mensaje al campo de chat`.  
   ![addAction](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_3.png?raw=true) (`{target.hand}` contiene información sobre el objeto en la mano del jugador.)
5. Haga clic en `Editar accionador > Añadir accionador > Comando` e introduzca `id` en el campo `nombre del comando`. ![AñadirAccionador](https://github.com/aieuo/images/blob/master/mineflow/eng/CheckId_4.png?raw=true)

##### Para enviar más información del artículo

{target.hand} es [variable de elemento](https://github.com/aieuo/Mineflow/wiki/Variable#item). `{target.hand.name}` es reemplazado por el nombre del artículo y `{target.hand.count}` por el número de elementos.

##### Para poder usarlo no es necesario OP

Establezca los permisos del comando a `cualquiera puede ejecutar` en el formulario para agregar el comando o en el menú de comandos.

## Copyright

Iconos hechos por [Pause08](https://www.flaticon.com/authors/pause08) de [www.flaticon.com](https://www.flaticon.com/)
