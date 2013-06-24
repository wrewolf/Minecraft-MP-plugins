#DungeonGenerator
`/generate 1`

Build abadoned mineshaft

#gamemodeAll
`/gamemodea <mode>`

Set this gamemod in player connect to server. You users don't change gamemod

#getc
`/getc`

Say to player his coordinates

#prizon
`/prizone <player>`

Jail player

`/unprizone <player>`

Release player

`/prizonelist`

List of prizoners

`/setprizone`

set prizone position

**Setup needed**

Prizon/config.yml
set coordinates of Prizone

#SetHome

`/sethome` 

set home position

`/home` 

return to home position

set player home

#settime
**Setup needed**

Current set timezone to Russia Moscow, to setup edit plugin php code.

#helper

`/god <nick>`

Give 32767 health, only from console

`/heal <nick>` 

Give 20 health

`/tree <tree|brich|redwood>`

Grow treein player position, after quickly step back you inside tree and get damage

#SimpleGroup

Group commands access only for op's

`/addgroup <name>`

create group

`/rmgroup <name>`

remove group

`/lsgroup`

list of groups

`/group add|rm|ls`

`add <user> <group>`

 add user to group

`rm <user> <group>`

 remove user from group

`ls`

 user groups list

+ modivied version PrivateAreaProtector
`/protect g <groupname>`

#PeacefulSpawn

Not made from me, but i place it this :)

#PVPZone

Register PVP zone on server, only inside this area player may take damage

`setpvpzone <level> <x1> <y1> <z1> <x2> <y2> <z2>`

Set PVP zone

#Portal

Teleport player in world

Plase Sign with text

`w:`

`<world>`

`tp`

`Description`

At the touch player will be teleported


#SimpleMap
`/makemap`

time generation 1-2min, CPU load 100%

Generate YAML array with head block in all map.
After Run 

php  code to generate svg map.php, this generate static map.html, time generation 30-60sec.
Folder with Textures needed

#mobtest
`/spawnmmob <type> <player> or <x> <y> <z>`

type in [chicken, cow, pig, sheep, zombie, creeper, skeleton, spider, pigman]

after spawning mob goes to random direction

#npctest
`/spawnmmob <name> <player> `

Spawn NPS on <player> position

after spawning NPC  goes to random direction

#OnlineCount

Under construction