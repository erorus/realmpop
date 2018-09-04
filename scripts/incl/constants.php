<?php

$MAX_LEVEL = 120;

$RACES = [
    1 => 'Human',
    2 => 'Orc',
    3 => 'Dwarf',
    4 => 'Night Elf',
    5 => 'Undead',
    6 => 'Tauren',
    7 => 'Gnome',
    8 => 'Troll',
    9 => 'Goblin',
    10 => 'Blood Elf',
    11 => 'Draenei',
    22 => 'Worgen',
    25 => 'Pandaren (A)',
    26 => 'Pandaren (H)',
    27 => 'Nightborne',
    28 => 'Highmountain Tauren',
    29 => 'Void Elf',
    30 => 'Lightforged Draenei',
    31 => 'Zandalari Troll',
    32 => 'Kul Tiran',
    33 => 'Human',
    34 => 'Dark Iron Dwarf',
    35 => 'Vulpera',
    36 => 'Mag\'har Orc',
];

$CLASSES = [
    1 => 'Warrior',
    2 => 'Paladin',
    3 => 'Hunter',
    4 => 'Rogue',
    5 => 'Priest',
    6 => 'Death Knight',
    7 => 'Shaman',
    8 => 'Mage',
    9 => 'Warlock',
    10 => 'Monk',
    11 => 'Druid',
    12 => 'Demon Hunter',
];

$GENDERS = ['Male','Female'];

$SIDES = ['Alliance','Horde'];

$RACE_TO_SIDE = [
    1 => 'Alliance',
    2 => 'Horde',
    3 => 'Alliance',
    4 => 'Alliance',
    5 => 'Horde',
    6 => 'Horde',
    7 => 'Alliance',
    8 => 'Horde',
    9 => 'Horde',
    10 => 'Horde',
    11 => 'Alliance',
    22 => 'Alliance',
    25 => 'Alliance',
    26 => 'Horde',
    27 => 'Horde',
    28 => 'Horde',
    29 => 'Alliance',
    30 => 'Alliance',
    31 => 'Horde',
    32 => 'Alliance',
    33 => 'Alliance',
    34 => 'Alliance',

    36 => 'Horde',
];

function lookup($constTable, $key) {
    return isset($constTable[$key]) ? $constTable[$key] : 'Unknown';
}
