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
