import {Component, OnInit} from '@angular/core';
import {GripModel, SpeciesModel} from "../../../models";
import {calculateToolStyles} from "../../../functions";
import {SPECIES} from "../../../functions";
import { FormsModule } from '@angular/forms';
import { NgIf, NgStyle, NgFor } from '@angular/common';

@Component({
  selector: 'app-new-item',
  templateUrl: './new-item.component.html',
  styleUrls: ['./new-item.component.scss'],
  standalone: true,
  imports: [FormsModule, NgIf, NgStyle, NgFor]
})
export class NewItemComponent implements OnInit {

  public readonly ITEM_SIZE = 200;

  private readonly size = '100px';

  public readonly SPECIES: SpeciesModel[] = SPECIES;

  darkMode = false;
  SQL = '';

  grip: GripModel;
  fileReader: FileReader;
  svgBackgroundData: string;

  toolRotationStyle: any[] = [];
  toolPositionStyle: any[] = [];

  constructor() { }

  ngOnInit() {
    this.fileReader = new FileReader();

    this.fileReader.onloadend = () => {
      const svg = <string>this.fileReader.result;

      this.svgBackgroundData = 'url(data:image/svg+xml;base64,' + btoa(svg) + ')';

      this.grip = {
        x: 0.5, y: 0.5, angle: 0, angleFixed: false, scale: 1
      };

      this.updateGripStyles();
    };
  }

  doSelectFile(event) {
    const file: File = event.target.files[0];

    this.fileReader.readAsText(file);
  }

  doSetGrip(event)
  {
    this.grip.x = event.offsetX / this.ITEM_SIZE;
    this.grip.y = event.offsetY / this.ITEM_SIZE;

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
    this.toolPositionStyle = [];
    this.toolRotationStyle = [];

    this.SPECIES.forEach(species => {
      let toolStyles = calculateToolStyles(this.size, species, this.grip);

      toolStyles.rotation.backgroundImage = this.svgBackgroundData;

      this.toolPositionStyle.push(toolStyles.position);
      this.toolRotationStyle.push(toolStyles.rotation);
    });

    this.SQL = `UPDATE item_tool
SET
  grip_x=${this.grip.x},
  grip_y=${this.grip.y},
  grip_angle=${this.grip.angle},
  grip_angle_fixed=${(this.grip.angleFixed ? 1 : 0)},
  grip_scale=${this.grip.scale}
WHERE id=ID_GOES_HERE
LIMIT 1`;
  }
}
