const itemNames = [
  'Orange', 'Fluff', 'Crooked Stick', 'Tea Leaves', 'Rock', 'Talon', 'Feathers', 'Iron Key'
]

export function randomItemName()
{
  return itemNames[Math.floor(Math.random() * itemNames.length)];
}
