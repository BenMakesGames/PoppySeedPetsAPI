/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, OnChanges} from '@angular/core';
import { AppComponent } from "../../../../app.component";
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-loading-throbber',
    templateUrl: './loading-throbber.component.html',
    styleUrls: ['./loading-throbber.component.scss']
})
export class LoadingThrobberComponent implements OnChanges {

  @Input() randomText = false;
  @Input('text') basicText = 'Loading';

  text = 'Loading';

  private static readonly HIDING_MESSAGES = 'Hiding messages in the dev console';

  private static readonly LOADING_TEXTS = [
    'Reticulating splines',
    'Checking on Noetala',
    'Adding a brick to the Sphinx',
    'Waging ant-bee war',
    'Stabilizing the Portal',
    'Looking for Tig',
    'Repairing the Library of Fire',
    'Mobilizing raccoons',
    'Restocking the Grocer',
    'Rolling the 1s out of the d20s',
    LoadingThrobberComponent.HIDING_MESSAGES,
    'Fixing up the scarecrow',
    'Negotiating with the Jelly Prince',
  ];

  ngOnChanges()
  {
    if(this.randomText && Math.random() < 0.02)
    {
      this.text = LoadingThrobberComponent.LOADING_TEXTS[Math.floor(Math.random() * LoadingThrobberComponent.LOADING_TEXTS.length)];

      if(this.text == LoadingThrobberComponent.HIDING_MESSAGES)
        AppComponent.consoleLogRandomSecret();
    }
    else
      this.text = this.basicText ?? 'Loading';
  }
}
