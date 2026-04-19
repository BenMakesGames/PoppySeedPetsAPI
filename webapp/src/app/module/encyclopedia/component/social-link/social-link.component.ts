/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-social-link',
    templateUrl: './social-link.component.html',
    styleUrls: ['./social-link.component.scss'],
    standalone: false
})
export class SocialLinkComponent {
  @Input() link: { website: string, nameOrId: string };
  @Input() showUrl = false;

  public static Url = (link: { website: string, nameOrId: string }) => SocialLinkComponent.Websites[link.website].url.replace('%id%', link.nameOrId);
  public static Title = (link: { website: string, nameOrId: string }) => (SocialLinkComponent.Websites[link.website].hideId ? 'On ' : (link.nameOrId + ' on ')) + SocialLinkComponent.Websites[link.website].title;

  url = SocialLinkComponent.Url;
  title = SocialLinkComponent.Title;

  public static readonly Websites: { [key:string]: { title: string,  url: string, idName: string, placeholder: string, hideId?: boolean }} = {
    ChickenSmoothie: { title: 'Chicken Smoothie', url: 'https://www.chickensmoothie.com/Forum/memberlist.php?mode=viewprofile&u=%id', idName: 'Id', placeholder: '' },
    DeviantArt: { title: 'DeviantArt', url: 'https://www.deviantart.com/%id%', idName: 'Name', placeholder: '' },
    FlightRising: { title: 'Flight Rising', url: 'https://www1.flightrising.com/clan-profile/%id%', idName: 'Id', placeholder: '' },
    FurAffinity: { title: 'Fur Affinity', url: 'https://www.furaffinity.net/user/%id%/', idName: 'Name', placeholder: '' },
    GaiaOnline: { title: 'Gaia Online', url: 'https://www.gaiaonline.com/profiles/%id%/', idName: 'Name', placeholder: '' },
    GitHub: { title: 'GitHub', url: 'https://github.com/%id%/', idName: 'Name', placeholder: '' },
    Goatlings: { title: 'Goatlings', url: 'https://www.goatlings.com/profile/u/%id', idName: 'Id', placeholder: '' },
    Instagram: { title: 'Instagram', url: 'https://www.instagram.com/%id%/', idName: 'Name', placeholder: '' },
    Lorwolf: { title: 'Lorwolf', url: 'https://www.lorwolf.com/Play/User?id=%id', idName: 'GUID', placeholder: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', hideId: true },
    Mastodon: { title: 'Mastodon', url: 'https://mastodon.social/@%id%', idName: 'Address', placeholder: 'name@server.xyz' },
    Nintendo: { title: 'Nintendo', url: '', idName: 'Friend Code', placeholder: '1234-1234-1234' },
    PixelCatsEnd: { title: 'Pixel Cat\'s End', url: 'https://www.pixelcatsend.com/profile&id=%id%', idName: 'Id', placeholder: '' },
    PSP: { title: 'Poppy Seed Pets', url: 'https://poppyseedpets.com/poppyopedia/resident/%id%', idName: 'Id', placeholder: '' },
    Steam: { title: 'Steam', url: 'https://steamcommunity.com/id/%id%/', idName: 'Name', placeholder: '' },
    Tumblr: { title: 'Tumblr', url: 'https://%id%.tumblr.com', idName: 'Name', placeholder: '' },
    Twitch: { title: 'Twitch', url: 'https://www.twitch.tv/%id%', idName: 'Name', placeholder: '' },
    YouTube: { title: 'YouTube', url: 'https://www.youtube.com/@%id%', idName: 'Name', placeholder: '' },
  };
}
