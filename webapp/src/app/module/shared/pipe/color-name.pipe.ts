import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'colorName'
})
export class ColorNamePipe implements PipeTransform {

  static readonly colors = {
    // from XKCD's color survey; the top 48 colors:
    'purple': '7e1e9c',
    'green': '15b01a',
    'blue': '0343df',
    'pink': 'ff81c0',
    'brown': '653700',
    'red': 'e50000',
    'light blue': '95d0fc',
    'teal': '029386',
    'orange': 'f97306',
    'light green': '96f97b',
    'magenta': 'c20078',
    'yellow': 'ffff14',
    'sky blue': '75bbfd',
    'grey': '929591',
    'lime green': '89fe05',
    'light purple': 'bf77f6',
    'violet': '9a0eea',
    'dark green': '033500',
    'turquoise': '06c2ac',
    'lavender': 'c79fef',
    'dark blue': '00035b',
    'tan': 'd1b26f',
    'cyan': '00ffff',
    'aqua': '13eac9',
    'forest green': '06470c',
    'mauve': 'ae7181',
    'dark purple': '35063e',
    'bright green': '01ff07',
    'maroon': '650021',
    'olive': '6e750e',
    'salmon': 'ff796c',
    'beige': 'e6daa6',
    'royal blue': '0504aa',
    'navy blue': '001146',
    'lilac': 'cea2fd',
    'black': '000000',
    'hot pink': 'ff028d',
    'light brown': 'ad8150',
    'pale green': 'c7fdb5',
    'peach': 'ffb07c',
    'olive green': '677a04',
    //'dark pink': 'cb416b', // Terracotta and Magenta were filling this space better
    'periwinkle': '8e82fe',
    'sea green': '53fca1',
    'lime': 'aaff32',
    'indigo': '380282',
    'mustard': 'ceb301',
    'light pink': 'ffd1df',

    // these are not in the top 48, but I added them to cover gaps in the 48 (for example, many
    // greens were being reported as grey), and just to add some more evocative names (poop! :P)
    'white': 'ffffff',
    'light grey': 'd8dcd6',
    'dark grey': '363737',
    'greyish blue': '5e819d',
    'ocean blue': '03719c',
    'moss green': '658b38',
    'grey green': '789b73',
    'blue green': '137e6d',
    'sand': 'e2ca76',
    'deep purple': '36013f',
    'steel blue': '5a7d9a',
    'reddish brown': '7f2b0a',
    'wine': '80013f',
    'apple green': '76cd26',
    'blood red': '980002',
    'terracotta': 'ca6641',
    'pumpkin': 'e17701',
    'dusty pink': 'd58a94',
    'denim': '3b638c',
    'poop': '7f5e00',
    'eggshell': 'ffffd4',
    'khaki': 'aaa662',
    'brick red': '8f1402',
    'brownish red': '9e3623',
    'dark turquoise': '045c5a'
  };

  transform(color: string): string {
    // get the R G and B component from the input color; remove leading '#', if any:
    color = color.replace(/^#/, '');
    const r = parseInt(color.substring(0, 2), 16);
    const g = parseInt(color.substring(2, 4), 16);
    const b = parseInt(color.substring(4, 6), 16);

    // find the closest color from the list of colors:
    let minDistance = Number.MAX_SAFE_INTEGER;

    let closestColor = '';

    for(const colorName in ColorNamePipe.colors)
    {
      const colorValue = ColorNamePipe.colors[colorName];
      const colorValueR = parseInt(colorValue.substring(0, 2), 16);
      const colorValueG = parseInt(colorValue.substring(2, 4), 16);
      const colorValueB = parseInt(colorValue.substring(4, 6), 16);

      const distance = ColorNamePipe.getRedmean(r, g, b, colorValueR, colorValueG, colorValueB);

      if(distance < minDistance)
      {
        minDistance = distance;
        closestColor = colorName;
      }
    }

    return closestColor;
  }

  public static getRedmean(r1: number, g1: number, b1: number, r2: number, g2: number, b2: number): number {
    const rMean = (r1 + r2) / 2;

    const r = r1 - r2;
    const g = g1 - g2;
    const b = b1 - b2;

    const redPart = (2 + rMean / 256) * r * r;
    const greenPart = 4 * g * g;
    const bluePart = (2 + (255 - rMean) / 256) * b * b;

    return Math.sqrt(redPart + greenPart + bluePart);
  }

}
