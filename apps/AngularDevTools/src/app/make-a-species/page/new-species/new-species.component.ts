import { Component, OnInit } from '@angular/core';
import {GripModel, HatModel, SpeciesModel} from "../../../models";
import {calculateHatStyles, calculateToolStyles} from "../../../functions";
import { FormsModule } from '@angular/forms';
import { NgIf, NgStyle, NgFor } from '@angular/common';

@Component({
  selector: 'app-new-species',
  templateUrl: './new-species.component.html',
  styleUrls: ['./new-species.component.scss'],
  standalone: true,
  imports: [FormsModule, NgIf, NgStyle, NgFor]
})
export class NewSpeciesComponent implements OnInit {

  public readonly ITEM_SIZE = 200;

  private readonly size = '100px';

  public readonly OUTFITS: { hat: HatModel, tool: GripModel }[] = [
    {
      hat: {
        image: 'witch',
        x: 0.546875,
        y: 0.87,
        angle: 10,
        angleFixed: false,
        scale: 0.6666,
      },
      tool: {
        image: 'fabric-mache',
        x: 0.484375,
        y: 0.503125,
        angle: 0,
        angleFixed: true,
        scale: 0.6666,
      },
    },
    {
      hat: {
        image: 'bucket-plastic',
        x: 0.490625,
        y: 0.78125,
        angle: 0,
        angleFixed: false,
        scale: 0.6,
      },
      tool: {
        image: 'crooked-painted',
        x: 0.23125,
        y: 0.725,
        angle: -20,
        angleFixed: false,
        scale: 1,
      }
    },
    {
      hat: {
        image: 'horn-unicorn',
        x: 0.409375,
        y: 0.85,
        angle: -15,
        angleFixed: false,
        scale: 0.5,
      },
      tool: {
        image: 'hunting',
        x: 0.378125,
        y: 0.7625,
        angle: -40,
        angleFixed: false,
        scale: 0.85,
      }
    },
    {
      hat: {
        image: 'crown',
        x: 0.503125,
        y: 0.915625,
        angle: 0,
        angleFixed: false,
        scale: 0.6,
      },
      tool: {
        image: 'fairy',
        x: 0.575,
        y: 0.86875,
        angle: 20,
        angleFixed: false,
        scale: 0.3333,
      }
    }
  ];

  darkMode = false;
  SQL = '';

  species: SpeciesModel;
  fileReader: FileReader;
  svgBackgroundData: string;

  mode = 'setHand';

  toolPositionStyle: any[] = [];
  toolRotationStyle: any[] = [];
  hatPositionStyle: any[] = [];
  hatRotationStyle: any[] = [];

  constructor() { }

  ngOnInit() {
    this.fileReader = new FileReader();

    this.fileReader.onloadend = () => {
      const svg = <string>this.fileReader.result;

      this.svgBackgroundData = 'url(data:image/svg+xml;base64,' + btoa(svg) + ')';

      this.species = {
        flipX: false,
        handX: 0.5, handY: 0.5, handAngle: 0, handBehind: false,
        hatX: 0.5, hatY: 0.25, hatAngle: 0
      };

      this.updateGripStyles();
    };
  }

  doSelectFile(event) {
    const file: File = event.target.files[0];

    this.fileReader.readAsText(file);
  }

  doSpeciesClick(event)
  {
    if(this.mode === 'setHand')
    {
      this.species.handX = event.offsetX / this.ITEM_SIZE;
      this.species.handY = event.offsetY / this.ITEM_SIZE;
    }
    else
    {
      this.species.hatX = event.offsetX / this.ITEM_SIZE;
      this.species.hatY = event.offsetY / this.ITEM_SIZE;
    }

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
    this.hatPositionStyle = [];
    this.hatRotationStyle = [];

    this.OUTFITS.forEach(outfit => {
      let toolStyles = calculateToolStyles(this.size, this.species, outfit.tool);
      let hatStyles = calculateHatStyles(this.size, this.species, outfit.hat);

      this.toolPositionStyle.push(toolStyles.position);
      this.toolRotationStyle.push(toolStyles.rotation);
      this.hatPositionStyle.push(hatStyles.position);
      this.hatRotationStyle.push(hatStyles.rotation);
    });

    this.SQL = `UPDATE pet_species
SET
    flip_x=${(this.species.flipX ? 1 : 0)},
    hand_x=${this.species.handX},
    hand_y=${this.species.handY},
    hand_angle=${this.species.handAngle},
    hand_behind=${(this.species.handBehind ? 1 : 0)},
    hat_x=${this.species.hatX},
    hat_y=${this.species.hatY},
    hat_angle=${this.species.hatAngle}
WHERE id=ID_GOES_HERE
LIMIT 1`;

  }
}
