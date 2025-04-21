import { Component, OnInit } from '@angular/core';
import {HatModel, SpeciesModel} from "../../../models";
import {calculateHatStyles, SPECIES} from "../../../functions";
import { FormsModule } from '@angular/forms';
import { NgIf, NgStyle, NgFor } from '@angular/common';

@Component({
  selector: 'app-new-hat',
  templateUrl: './new-hat.component.html',
  styleUrls: ['./new-hat.component.scss'],
  standalone: true,
  imports: [FormsModule, NgIf, NgStyle, NgFor]
})
export class NewHatComponent implements OnInit {

  public readonly ITEM_SIZE = 200;

  private readonly size = '100px';

  public readonly SPECIES: SpeciesModel[] = SPECIES;

  darkMode = false;
  SQL = '';

  hat: HatModel;
  fileReader: FileReader;
  svgBackgroundData: string;

  hatRotationStyle: any[] = [];
  hatPositionStyle: any[] = [];

  constructor() { }

  ngOnInit() {
    this.fileReader = new FileReader();

    this.fileReader.onloadend = () => {
      const svg = <string>this.fileReader.result;

      this.svgBackgroundData = 'url(data:image/svg+xml;base64,' + btoa(svg) + ')';

      this.hat = {
        x: 0.5, y: 0.5, angle: 0, angleFixed: false, scale: 1
      };

      this.updateGripStyles();
    };
  }

  doSelectFile(event) {
    const file: File = event.target.files[0];

    this.fileReader.readAsText(file);
  }

  doSetHead(event)
  {
    this.hat.x = event.offsetX / this.ITEM_SIZE;
    this.hat.y = event.offsetY / this.ITEM_SIZE;

    this.updateGripStyles();
  }

  doChangeGripProperty()
  {
    this.updateGripStyles();
  }

  doToggleDarkMode()
  {
    this.darkMode = !this.darkMode;
  }

  private updateGripStyles()
  {
    this.hatPositionStyle = [];
    this.hatRotationStyle = [];

    this.SPECIES.forEach(species => {
      let hatStyles = calculateHatStyles(this.size, species, this.hat);

      hatStyles.rotation.backgroundImage = this.svgBackgroundData;

      this.hatPositionStyle.push(hatStyles.position);
      this.hatRotationStyle.push(hatStyles.rotation);
    });

    this.SQL = `UPDATE item_hat
SET
  head_x=${this.hat.x},
  head_y=${this.hat.y},
  head_angle=${this.hat.angle},
  head_angle_fixed=${(this.hat.angleFixed ? 1 : 0)},
  head_scale=${this.hat.scale}
WHERE id=ID_GOES_HERE
LIMIT 1`;
  }
}
