import { Component } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    selector: 'app-illusionist',
    templateUrl: './illusionist.component.html',
    styleUrls: ['./illusionist.component.scss'],
    standalone: false
})
@HasSounds([ 'chaching', 'wine-pour' ])
export class IllusionistComponent {
  payWith = 'bloodWine';

  buyAjax = Subscription.EMPTY;
  askedAboutAura = false;

  dialog = 'Please, look around. I accept moneys, recycling points, and Blood Wine.';

  constructor(private api: ApiService, private sounds: SoundsService) {
  }

  doBuy(item:string, currency: string)
  {
    if(!this.buyAjax.closed)
      return;

    const data = {
      item: item,
      payWith: currency,
    };

    this.buyAjax = this.api.post<any>('/illusionist/buy', data).subscribe({
      next: _ => {
        if(data.payWith === 'bloodWine')
          this.sounds.playSound('wine-pour');
        else
          this.sounds.playSound('chaching');

        this.dialog = '"' + item + '", of course. Pleasure doing business with you.';
      },
      error: e => {
        if(e.error && e.error.errors)
          this.dialog = e.error.errors.join("\n\n");
        else if(e.errors)
          this.dialog = e.errors.join("\n\n");
      }
    });
  }

  doAskAboutAura()
  {
    this.askedAboutAura = true;
    this.dialog = "Ahh, you noticed.\n\nJust a little something I had Myles put together for me. That man has a gift - he's a wizard of his craft. It has been my pleasure to work with him all these years.";
  }

  inventory = [
    {
      name: 'Scroll of Illusions',
      image: 'scroll/illusions',
      cost: {
        moneys: 200,
        recyclingPoints: 100,
        bloodWine: 2
      }
    },
    {
      name: 'Blush of Life',
      image: 'potion/blush-of-life',
      cost: {
        moneys: 200,
        recyclingPoints: 100,
        bloodWine: 2
      }
    },
    {
      name: 'Mysterious Seed',
      image: 'plant/mysterious-seed',
      cost: {
        moneys: 150,
        recyclingPoints: 75,
        bloodWine: 1
      }
    },
    {
      name: 'Tile: Giant Bat',
      image: 'tile/giant-bat',
      cost: {
        moneys: 100,
        recyclingPoints: 50,
        bloodWine: 1
      }
    },
    {
      name: 'Tile: Bats!',
      image: 'tile/bats',
      cost: {
        moneys: 100,
        recyclingPoints: 50,
        bloodWine: 1
      }
    },
    {
      name: 'Magpie\'s Deal',
      image: 'treasure/magpie-deal',
      cost: {
        moneys: 50,
        recyclingPoints: 25,
        bloodWine: 1
      }
    },
    {
      name: 'Quinacridone Magenta Dye',
      image: 'resource/dye-quinacridone',
      cost: {
        moneys: 50,
        recyclingPoints: 25,
        bloodWine: 1
      }
    },
    {
      name: 'On Vampires',
      image: 'book/vampire',
      cost: {
        moneys: 25,
        recyclingPoints: 15,
        bloodWine: 1
      }
    },
  ]
}
